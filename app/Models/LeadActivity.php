<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $fillable = ['lead_id', 'activity_type', 'notes'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
