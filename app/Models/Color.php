<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $fillable = ['name', 'hex_code', 'material_id'];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
