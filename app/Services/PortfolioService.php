<?php

namespace App\Services;

use App\Models\PortfolioItem;
use Illuminate\Support\Collection;

class PortfolioService
{
    /**
     * Transform portfolio items for frontend display
     */
    public function transformItems(Collection $items): Collection
    {
        return $items->map(function($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'material' => $item->material ?? null,
                'category_id' => $item->category_id,
                'category' => $item->category->name ?? null,
                'featured' => $item->featured ?? false,
                'glb_model' => $item->glb_model ? \Storage::url($item->glb_model) : null,
                'image' => $item->images->first() ? \Storage::url($item->images->first()->path) : null,
                'images' => $item->images->map(fn($img) => \Storage::url($img->path))->toArray(),
            ];
        });
    }
}
