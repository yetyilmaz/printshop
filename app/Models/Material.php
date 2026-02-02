<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = ['name', 'slug', 'price_per_cm3', 'time_multiplier', 'type'];

    public function colors()
    {
        return $this->hasMany(Color::class);
    }
}
