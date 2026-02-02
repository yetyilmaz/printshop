<?php

namespace App\Repositories;

use App\Models\PortfolioCategory;
use App\Models\PortfolioItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PortfolioRepository
{
    /**
     * Get all categories with item count (cached for 1 hour)
     */
    public function getCategoriesWithCount(): Collection
    {
        return Cache::remember('portfolio.categories.with_count', 3600, function () {
            return PortfolioCategory::withCount('items')->ordered()->get();
        });
    }

    /**
     * Get podium category (cached for 1 hour)
     */
    public function getPodiumCategory(): ?PortfolioCategory
    {
        return Cache::remember('portfolio.podium_category', 3600, function () {
            return PortfolioCategory::where('slug', 'podium')->first();
        });
    }

    /**
     * Get featured items for homepage
     */
    public function getFeaturedItems(int $categoryId, int $limit = 6): Collection
    {
        return PortfolioItem::where('category_id', $categoryId)
            ->with(['images' => function ($q) {
                $q->orderBy('sort_order');
            }, 'category'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get all items with relationships
     */
    public function getAllWithRelations(): Collection
    {
        return PortfolioItem::with(['images' => function ($q) {
            $q->orderBy('sort_order');
        }, 'category'])->orderByDesc('created_at')->get();
    }

    /**
     * Clear portfolio cache
     */
    public function clearCache(): void
    {
        Cache::forget('portfolio.categories.with_count');
        Cache::forget('portfolio.podium_category');
    }
}
