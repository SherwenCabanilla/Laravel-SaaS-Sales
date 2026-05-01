<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InAppNotification extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'source',
        'event_name',
        'level',
        'idempotency_key',
        'title',
        'message',
        'action_url',
        'payload',
        'occurred_at',
        'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }
}
