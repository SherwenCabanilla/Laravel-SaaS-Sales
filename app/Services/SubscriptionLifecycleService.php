<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class SubscriptionLifecycleService
{
    public const BILLING_CURRENT = 'current';
    public const BILLING_OVERDUE = 'overdue';
    public const BILLING_INACTIVE = 'inactive';
    public const BILLING_TRIAL = 'trial';
    public const GRACE_DAYS = 3;

    public function activateTenantSubscriptionFromPayment(Payment $payment, array $plan, ?string $paymentMethod = null): Tenant
    {
        return DB::transaction(function () use ($payment, $plan, $paymentMethod) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $tenant = Tenant::query()->lockForUpdate()->findOrFail($payment->tenant_id);

            if (! $payment->isPlatformSubscription()) {
                throw new \RuntimeException('Only platform subscription payments can activate tenant subscriptions.');
            }

            if ($payment->status !== 'paid') {
                $payment->update([
                    'status' => 'paid',
                    'payment_method' => $paymentMethod ?? $payment->payment_method,
                    'payment_date' => now()->toDateString(),
                ]);
            }

            $tenant->update([
                'subscription_plan' => $plan['name'],
                'status' => 'active',
                'billing_status' => self::BILLING_CURRENT,
                'billing_grace_ends_at' => null,
                'last_payment_failed_at' => null,
                'subscription_activated_at' => now(),
                'subscription_renews_at' => now()->addMonthNoOverflow(),
                'trial_ends_at' => null,
            ]);

            $tenant = $tenant->fresh();
            $this->dispatchAutomationEvent('subscription_paid', $this->subscriptionPayload($tenant, $payment, [
                'plan_code' => (string) ($plan['code'] ?? ''),
                'plan_name' => (string) ($plan['name'] ?? $tenant->subscription_plan),
                'payment_method' => $paymentMethod ?? $payment->payment_method,
            ]));
            app(FinanceAuditService::class)->record(
                'subscription_paid',
                'Platform subscription payment activated the tenant workspace.',
                null,
                $tenant,
                $payment,
                null,
                [
                    'plan_code' => (string) ($plan['code'] ?? ''),
                    'plan_name' => (string) ($plan['name'] ?? $tenant->subscription_plan),
                    'payment_method' => $paymentMethod ?? $payment->payment_method,
                ]
            );

            return $tenant;
        });
    }

    public function markPaymentFailed(Payment $payment): ?Tenant
    {
        return DB::transaction(function () use ($payment) {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $tenant = Tenant::query()->lockForUpdate()->find($payment->tenant_id);

            if ($payment->status !== 'failed') {
                $payment->update([
                    'status' => 'failed',
                    'payment_date' => $payment->payment_date ?: now()->toDateString(),
                ]);
            }

            if (! $tenant) {
                return null;
            }

            if (! $payment->isPlatformSubscription()) {
                return $tenant->fresh();
            }

            if ($tenant->status === 'active') {
                $tenant->update([
                    'billing_status' => self::BILLING_OVERDUE,
                    'billing_grace_ends_at' => now()->addDays(self::GRACE_DAYS),
                    'last_payment_failed_at' => now(),
                ]);

                $tenant = $tenant->fresh();
                $this->dispatchAutomationEvent('payment_failed', [
                    'tenant_id' => $tenant->id,
                    'invoice_id' => (string) ($payment->provider_reference ?: $payment->id),
                    'payment_id' => $payment->id,
                ]);
                $this->dispatchAutomationEvent('subscription_overdue', $this->subscriptionPayload($tenant, $payment, [
                    'invoice_id' => (string) ($payment->provider_reference ?: $payment->id),
                ]));
                app(FinanceAuditService::class)->record(
                    'subscription_overdue',
                    'Platform subscription entered overdue state after a failed payment.',
                    null,
                    $tenant,
                    $payment,
                    null,
                    [
                        'invoice_id' => (string) ($payment->provider_reference ?: $payment->id),
                    ]
                );
            }

            return $tenant->fresh();
        });
    }

    public function expireGracePeriodIfNeeded(Tenant $tenant): Tenant
    {
        if (
            $tenant->status === 'active'
            && $tenant->billing_status === self::BILLING_OVERDUE
            && $tenant->billing_grace_ends_at
            && now()->greaterThan($tenant->billing_grace_ends_at)
        ) {
            $tenant->update([
                'status' => 'inactive',
                'billing_status' => self::BILLING_INACTIVE,
            ]);
        }

        return $tenant->fresh();
    }

    public function restoreTenantBilling(Tenant $tenant, ?Payment $payment = null): Tenant
    {
        $tenant->update([
            'status' => 'active',
            'billing_status' => self::BILLING_CURRENT,
            'billing_grace_ends_at' => null,
            'last_payment_failed_at' => null,
            'subscription_activated_at' => now(),
            'subscription_renews_at' => now()->addMonthNoOverflow(),
        ]);

        $tenant = $tenant->fresh();
        $payload = $this->subscriptionPayload($tenant, null, []);
        if ($payment) {
            $payload = $this->subscriptionPayload($tenant, $payment, []);
            $this->dispatchAutomationEvent('subscription_paid', $payload);
        }
        $this->dispatchAutomationEvent('payment_recovered', [
            'tenant_id' => $tenant->id,
            'billing_status' => $tenant->billing_status,
            'subscription_plan' => $tenant->subscription_plan,
        ]);
        $this->dispatchAutomationEvent('subscription_recovered', $payload);
        app(FinanceAuditService::class)->record(
            $payment ? 'subscription_paid' : 'subscription_recovered',
            $payment
                ? 'Platform subscription payment restored tenant billing.'
                : 'Tenant billing was restored.',
            null,
            $tenant,
            $payment,
            null,
            [
                'billing_status' => $tenant->billing_status,
                'subscription_plan' => $tenant->subscription_plan,
            ]
        );

        return $tenant;
    }

    public function billingStateLabel(Tenant $tenant): string
    {
        return match ($tenant->billing_status) {
            self::BILLING_OVERDUE => 'Overdue',
            self::BILLING_INACTIVE => 'Inactive',
            self::BILLING_TRIAL => 'Trial',
            default => 'Current',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchAutomationEvent(string $eventName, array $payload): void
    {
        try {
            app(N8nEmailOrchestrator::class)->dispatch($eventName, $payload);
        } catch (\Throwable) {
            // Best-effort dispatch only.
        }
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function subscriptionPayload(Tenant $tenant, ?Payment $payment, array $extra): array
    {
        $owner = $tenant->users()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'account-owner'))
            ->orderBy('id')
            ->first(['users.id', 'users.name', 'users.email']);

        return array_merge([
            'tenant_id' => $tenant->id,
            'company_name' => $tenant->company_name,
            'account_owner_id' => $owner?->id,
            'account_owner_name' => $owner?->name,
            'account_owner_email' => $owner?->email,
            'payment_id' => $payment?->id,
            'amount' => $payment?->amount,
            'provider' => $payment?->provider,
            'provider_reference' => $payment?->provider_reference,
            'payment_method' => $payment?->payment_method,
            'subscription_plan' => $tenant->subscription_plan,
            'status' => $tenant->status,
            'billing_status' => $tenant->billing_status,
            'trial_ends_at' => optional($tenant->trial_ends_at)->toIso8601String(),
            'billing_grace_ends_at' => optional($tenant->billing_grace_ends_at)->toIso8601String(),
            'subscription_activated_at' => optional($tenant->subscription_activated_at)->toIso8601String(),
            'subscription_renews_at' => optional($tenant->subscription_renews_at)->toIso8601String(),
        ], $extra);
    }
}
