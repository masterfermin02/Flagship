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
        'rules'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rules' => 'array'
    ];
}