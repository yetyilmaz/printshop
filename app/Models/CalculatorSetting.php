<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalculatorSetting extends Model
{
    protected $fillable = [
        'category',
        'slug',
        'label',
        'multiplier',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'multiplier' => 'float',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
