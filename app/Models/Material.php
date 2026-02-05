<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = ['name', 'slug', 'price_per_cm3', 'price_per_kg', 'material_density', 'type'];

    public function colors()
    {
        return $this->hasMany(Color::class);
    }
}
