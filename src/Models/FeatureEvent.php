<?php

namespace Flagship\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureEvent extends Model
{
    protected $fillable = [
        'feature_name',
        'user_id',
        'event_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
