<?php

namespace App\Services;

use App\Models\PaymentReceipt;
use App\Models\User;

class ReceiptVerificationService
{
    public function evaluate(PaymentReceipt $receipt): PaymentReceipt
    {
        $receipt->loadMissing('payment');
        $payment = $receipt->payment;
        if (! $payment) {
            return $receipt;
        }

        $amountMatches = $receipt->receipt_amount !== null
            && abs((float) $receipt->receipt_amount - (float) $payment->amount) < 0.01;
        $providerMatches = $this->normalized($receipt->provider) !== ''
            && $this->normalized($receipt->provider) === $this->normalized($payment->provider);
        $referenceMatches = $this->normalized($receipt->reference_number) !== ''
            && $this->normalized($receipt->reference_number) === $this->normalized($payment->provider_reference);
        $tenantMatches = (int) $receipt->tenant_id === (int) $payment->tenant_id;
        $paymentTypeMatches = $this->normalized($payment->payment_type) !== '';

        $matchNotes = [
            'amount_matches' => $amountMatches,
            'provider_matches' => $providerMatches,
            'reference_matches' => $referenceMatches,
            'tenant_matches' => $tenantMatches,
            'payment_type_matches' => $paymentTypeMatches,
        ];

        if ($amountMatches && $providerMatches && $referenceMatches && $tenantMatches && $paymentTypeMatches) {
            $receipt->update([
                'status' => PaymentReceipt::STATUS_AUTO_APPROVED,
                'automation_status' => 'matched',
                'automation_reason' => 'Auto-approved because amount, provider, reference, tenant, and payment type all matched the linked payment.',
                'verified_at' => now(),
                'meta' => array_merge((array) $receipt->meta, ['automation_checks' => $matchNotes]),
            ]);

            $this->dispatchAutomationEvent('receipt_auto_approved', [
                'tenant_id' => $receipt->tenant_id,
                'payment_id' => $receipt->payment_id,
                'receipt_id' => $receipt->id,
            ]);

            return $receipt->fresh();
        }

        $receipt->update([
            'status' => PaymentReceipt::STATUS_PENDING,
            'automation_status' => 'review_required',
            'automation_reason' => 'Manual review required because one or more auto-match checks failed.',
            'meta' => array_merge((array) $receipt->meta, ['automation_checks' => $matchNotes]),
        ]);

        return $receipt->fresh();
    }

    public function review(PaymentReceipt $receipt, string $decision, User $reviewer, ?string $note = null): PaymentReceipt
    {
        $status = $decision === 'approve'
            ? PaymentReceipt::STATUS_APPROVED
            : PaymentReceipt::STATUS_REJECTED;

        $receipt->update([
            'status' => $status,
            'reviewed_by' => $reviewer->id,
            'verified_at' => $status === PaymentReceipt::STATUS_REJECTED ? null : now(),
            'notes' => $note ?: $receipt->notes,
        ]);

        $this->dispatchAutomationEvent($status === PaymentReceipt::STATUS_APPROVED ? 'receipt_approved' : 'receipt_rejected', [
            'tenant_id' => $receipt->tenant_id,
            'payment_id' => $receipt->payment_id,
            'receipt_id' => $receipt->id,
            'reviewed_by' => $reviewer->id,
        ]);

        return $receipt->fresh();
    }

    private function normalized(?string $value): string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = preg_replace('/[^a-z0-9]/', '', $value) ?? $value;

        return $value;
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
}
