<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionEntry extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_HELD = 'held';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAYABLE = 'payable';
    public const STATUS_PAID = 'paid';
    public const STATUS_REVERSED = 'reversed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'commission_plan_id',
        'payment_id',
        'lead_id',
        'user_id',
        'approved_by',
        'commission_role',
        'commission_type',
        'gross_amount',
        'basis_amount',
        'rate_percentage',
        'commission_amount',
        'status',
        'hold_until',
        'approved_at',
        'paid_at',
        'reversed_at',
        'notes',
        'meta',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'basis_amount' => 'decimal:2',
        'rate_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'hold_until' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'reversed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CommissionPlan::class, 'commission_plan_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
