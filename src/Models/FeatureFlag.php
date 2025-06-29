<?php

namespace Flagship\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $table = 'feature_flags';

    protected $fillable = [
        'name',
        'is_active',
        'description',
        'targeting_rules',
        'targeting_strategy',
        'custom_evaluator',
        'rollout_percentage',
        'variants',
        'scheduled_start',
        'scheduled_end',
        'environments',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'targeting_rules' => 'array',
        'variants' => 'array',
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'environments' => 'array',
    ];
}
