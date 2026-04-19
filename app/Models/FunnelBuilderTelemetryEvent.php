<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FunnelBuilderTelemetryEvent extends Model
{
    public const SOURCE_FUNNEL = 'funnel';
    public const SOURCE_TEMPLATE = 'template';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'source',
        'event',
        'latency_ms',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}

