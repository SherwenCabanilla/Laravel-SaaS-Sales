<?php

namespace App\Support;

use App\Models\Funnel;
use App\Models\Tenant;
use App\Models\TenantPayoutAccount;

class TenantPayoutReadiness
{
    public const STATE_MISSING_SETUP = 'missing_setup';
    public const STATE_PENDING_PLATFORM_REVIEW = 'pending_platform_review';
    public const STATE_REJECTED = 'rejected';
    public const STATE_APPROVED = 'approved';

    private const MONETIZED_STEP_TYPES = [
        'checkout',
        'upsell',
        'downsell',
    ];

    public function summaryForTenant(?Tenant $tenant): array
    {
        $state = $this->stateForTenant($tenant);

        return [
            'state' => $state,
            'label' => $this->labelForState($state),
            'workspace_notice' => $this->workspaceNoticeForState($state),
            'can_process_monetization' => $state === self::STATE_APPROVED,
        ];
    }

    public function publishDecision(Funnel $funnel): array
    {
        $state = $this->stateForTenant($funnel->tenant);
        $isMonetized = $this->funnelUsesMonetizedSteps($funnel);

        return match ($state) {
            self::STATE_MISSING_SETUP => [
                'allowed' => false,
                'state' => $state,
                'is_monetized' => $isMonetized,
                'message' => 'Complete the payout account setup before publishing funnels for this workspace.',
            ],
            self::STATE_PENDING_PLATFORM_REVIEW => [
                'allowed' => ! $isMonetized,
                'state' => $state,
                'is_monetized' => $isMonetized,
                'message' => $isMonetized
                    ? 'This funnel has checkout or offer steps. Wait for platform payout approval before publishing monetized funnels.'
                    : 'This funnel can still be published because it does not process live payments yet.',
            ],
            self::STATE_REJECTED => [
                'allowed' => ! $isMonetized,
                'state' => $state,
                'is_monetized' => $isMonetized,
                'message' => $isMonetized
                    ? 'Update and resubmit the payout account before publishing monetized funnels.'
                    : 'This funnel can still be published because it does not process live payments yet.',
            ],
            default => [
                'allowed' => true,
                'state' => $state,
                'is_monetized' => $isMonetized,
                'message' => 'Payout account approved.',
            ],
        };
    }

    public function monetizationDecisionForTenant(?Tenant $tenant): array
    {
        $state = $this->stateForTenant($tenant);

        return match ($state) {
            self::STATE_MISSING_SETUP => [
                'allowed' => false,
                'state' => $state,
                'message' => 'Live checkout is unavailable until this workspace completes payout account setup.',
            ],
            self::STATE_PENDING_PLATFORM_REVIEW => [
                'allowed' => false,
                'state' => $state,
                'message' => 'Live checkout is temporarily unavailable while this workspace payout account is under platform review.',
            ],
            self::STATE_REJECTED => [
                'allowed' => false,
                'state' => $state,
                'message' => 'Live checkout is unavailable until this workspace updates and resubmits its payout account.',
            ],
            default => [
                'allowed' => true,
                'state' => $state,
                'message' => 'Payout account approved.',
            ],
        };
    }

    public function funnelUsesMonetizedSteps(Funnel $funnel): bool
    {
        $funnel->loadMissing('steps');

        return $funnel->steps
            ->where('is_active', true)
            ->contains(fn ($step) => in_array(strtolower(trim((string) $step->type)), self::MONETIZED_STEP_TYPES, true));
    }

    public function stateForTenant(?Tenant $tenant): string
    {
        if (! $tenant) {
            return self::STATE_APPROVED;
        }

        $tenant->loadMissing('defaultPayoutAccount');
        $payoutAccount = $tenant->defaultPayoutAccount;

        if (! $payoutAccount || ! $payoutAccount->hasDestinationDetails()) {
            return self::STATE_MISSING_SETUP;
        }

        return match ($payoutAccount->reviewStatus()) {
            TenantPayoutAccount::STATUS_APPROVED => self::STATE_APPROVED,
            TenantPayoutAccount::STATUS_REJECTED => self::STATE_REJECTED,
            default => self::STATE_PENDING_PLATFORM_REVIEW,
        };
    }

    public function labelForState(string $state): string
    {
        return match ($state) {
            self::STATE_MISSING_SETUP => 'Payout setup required',
            self::STATE_PENDING_PLATFORM_REVIEW => 'Pending platform review',
            self::STATE_REJECTED => 'Payout update required',
            default => 'Approved',
        };
    }

    private function workspaceNoticeForState(string $state): ?string
    {
        return match ($state) {
            self::STATE_MISSING_SETUP => 'Payout setup is still missing. Teams can keep building drafts, but publishing is restricted until a payout account is submitted.',
            self::STATE_PENDING_PLATFORM_REVIEW => 'Payout review is still pending. Draft work can continue, but monetized funnels and live checkout stay paused until approval.',
            self::STATE_REJECTED => 'The payout account needs corrections. Draft work can continue, but monetized funnels and live checkout stay paused until the payout account is updated and approved.',
            default => null,
        };
    }
}
