<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioImage extends Model
{
    protected $fillable = ['portfolio_item_id', 'path', 'sort_order'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PortfolioItem::class);
    }
}
