<?php

namespace App\Services;

use App\Models\FinanceAuditLog;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FinanceAuditService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(
        string $eventType,
        ?string $message = null,
        ?User $actor = null,
        ?Tenant $tenant = null,
        ?Payment $payment = null,
        ?PaymentReceipt $receipt = null,
        array $context = [],
    ): void {
        try {
            FinanceAuditLog::query()->create([
                'tenant_id' => $tenant?->id ?? $payment?->tenant_id ?? $receipt?->tenant_id,
                'actor_user_id' => $actor?->id,
                'payment_id' => $payment?->id ?? $receipt?->payment_id,
                'payment_receipt_id' => $receipt?->id,
                'event_type' => $eventType,
                'message' => $message,
                'context' => $context,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to persist finance audit log.', [
                'event_type' => $eventType,
                'message' => $message,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
