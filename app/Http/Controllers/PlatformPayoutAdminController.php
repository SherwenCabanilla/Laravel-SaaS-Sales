<?php

namespace App\Http\Controllers;

use App\Models\TenantPayoutAccount;
use App\Models\User;
use App\Services\FinanceAuditService;
use App\Services\N8nEmailOrchestrator;
use Illuminate\Http\Request;

class PlatformPayoutAdminController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = trim((string) $request->query('status', 'pending'));
        $statusMap = [
            'pending' => TenantPayoutAccount::STATUS_PENDING_PLATFORM_REVIEW,
            'approved' => TenantPayoutAccount::STATUS_APPROVED,
            'rejected' => TenantPayoutAccount::STATUS_REJECTED,
            'all' => 'all',
        ];
        $resolvedStatus = $statusMap[$statusFilter] ?? TenantPayoutAccount::STATUS_PENDING_PLATFORM_REVIEW;

        $baseQuery = TenantPayoutAccount::query()
            ->with([
                'tenant:id,company_name,logo_path',
                'reviewer:id,name,email',
            ])
            ->where('is_default', true);

        $statusCounts = [
            'pending' => (clone $baseQuery)->where('verification_status', TenantPayoutAccount::STATUS_PENDING_PLATFORM_REVIEW)->count(),
            'approved' => (clone $baseQuery)->where('verification_status', TenantPayoutAccount::STATUS_APPROVED)->count(),
            'rejected' => (clone $baseQuery)->where('verification_status', TenantPayoutAccount::STATUS_REJECTED)->count(),
        ];

        $payoutAccounts = (clone $baseQuery)
            ->when($resolvedStatus !== 'all', function ($query) use ($resolvedStatus) {
                $query->where('verification_status', $resolvedStatus);
            })
            ->latest('updated_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $ownerMap = User::query()
            ->whereIn('tenant_id', $payoutAccounts->pluck('tenant_id')->filter()->unique()->values())
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'account-owner');
            })
            ->orderBy('id')
            ->get(['id', 'tenant_id', 'name', 'email'])
            ->keyBy('tenant_id');

        return view('platform.payout-admin-dashboard', [
            'payoutAccounts' => $payoutAccounts,
            'ownerMap' => $ownerMap,
            'statusCounts' => $statusCounts,
            'statusFilter' => array_key_exists($statusFilter, $statusMap) ? $statusFilter : 'pending',
        ]);
    }

    public function review(Request $request, TenantPayoutAccount $payoutAccount)
    {
        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'review_notes' => 'nullable|string|max:1000|required_if:decision,reject',
        ], [
            'review_notes.required_if' => 'Review notes are required when rejecting a payout account.',
        ]);

        $user = auth()->user();
        $tenant = $payoutAccount->tenant;
        $owner = User::query()
            ->where('tenant_id', $payoutAccount->tenant_id)
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'account-owner');
            })
            ->orderBy('id')
            ->first(['id', 'tenant_id', 'name', 'email']);

        if ($validated['decision'] === 'approve') {
            $payoutAccount->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => $user->id,
                'verification_status' => TenantPayoutAccount::STATUS_APPROVED,
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
                'review_notes' => trim((string) ($validated['review_notes'] ?? '')) ?: null,
            ]);
        } else {
            $payoutAccount->update([
                'is_verified' => false,
                'verified_at' => null,
                'verified_by' => null,
                'verification_status' => TenantPayoutAccount::STATUS_REJECTED,
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
                'review_notes' => trim((string) ($validated['review_notes'] ?? '')) ?: null,
            ]);
        }

        $decision = $validated['decision'];
        app(FinanceAuditService::class)->record(
            'tenant_payout_reviewed',
            $decision === 'approve'
                ? 'Platform payout admin approved tenant payout destination.'
                : 'Platform payout admin rejected tenant payout destination.',
            $user,
            $tenant,
            null,
            null,
            [
                'payout_account_id' => $payoutAccount->id,
                'decision' => $decision,
                'verification_status' => $payoutAccount->fresh()->reviewStatus(),
                'masked_destination' => $payoutAccount->masked_destination,
                'destination_type' => $payoutAccount->destination_type,
                'review_notes' => $payoutAccount->review_notes,
            ]
        );

        $this->dispatchPayoutAutomationEvent(
            $decision === 'approve' ? 'payout_account_approved' : 'payout_account_rejected',
            [
                'tenant_id' => $tenant?->id,
                'tenant_name' => $tenant?->company_name,
                'account_owner_id' => $owner?->id,
                'account_owner_name' => $owner?->name,
                'account_owner_email' => $owner?->email,
                'payout_account_id' => $payoutAccount->id,
                'destination_type' => $payoutAccount->destination_type,
                'masked_destination' => $payoutAccount->masked_destination,
                'verification_status' => $payoutAccount->fresh()->reviewStatus(),
                'review_notes' => $payoutAccount->review_notes,
                'reviewed_by' => $user->email,
            ]
        );

        return redirect()->route('platform.payouts.index')->with('success', 'Edited Successfully');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function dispatchPayoutAutomationEvent(string $eventName, array $payload): void
    {
        try {
            app(N8nEmailOrchestrator::class)->dispatch($eventName, $payload);
        } catch (\Throwable) {
            // Best-effort dispatch only.
        }
    }
}
