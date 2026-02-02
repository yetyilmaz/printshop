<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['order_id', 'original_name', 'file_path', 'disk', 'volume_cm3', 'dimensions_json'];

    protected $casts = [
        'dimensions_json' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
