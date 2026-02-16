<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    // ✅ Mass-assignable fields
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'status',
        'score'
    ];

    // 🔹 Optional: Global Scope for Tenant Isolation
    protected static function booted()
    {
        static::addGlobalScope('tenant', function ($query) {
            // Skip tenant filtering when running artisan commands like migrate or seed
            if (app()->runningInConsole()) {
                return;
            }

            // Only filter if a user is logged in
            if (auth()->check() && auth()->user()) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    // 🔹 Relationship: each lead belongs to a tenant
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // 🔹 Relationship: a lead can have multiple activities
    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }
}
