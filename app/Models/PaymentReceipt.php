<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_AUTO_APPROVED = 'auto_approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'tenant_id',
        'payment_id',
        'uploaded_by',
        'reviewed_by',
        'receipt_amount',
        'receipt_date',
        'provider',
        'reference_number',
        'receipt_path',
        'status',
        'automation_status',
        'automation_reason',
        'notes',
        'verified_at',
        'meta',
    ];

    protected $casts = [
        'receipt_amount' => 'decimal:2',
        'receipt_date' => 'date',
        'verified_at' => 'datetime',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
