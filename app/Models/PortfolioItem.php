<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PortfolioItem extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'material', 'glb_model', 'category_id', 'featured'];

    protected $casts = [
        'featured' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($item) {
            if (empty($item->slug)) {
                $item->slug = Str::slug($item->title);
            }
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('sort_order');
    }

    public function category()
    {
        return $this->belongsTo(PortfolioCategory::class, 'category_id');
    }
}