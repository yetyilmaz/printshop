<?php

namespace App\Http\Controllers;

use App\Models\PortfolioItem;
use App\Models\PortfolioCategory;

class PortfolioController extends Controller
{
    public function index()
    {
        $categories = PortfolioCategory::ordered()->get();
        $items = PortfolioItem::with(['images' => function ($q) {
            $q->orderBy('sort_order');
        }, 'category'])->orderByDesc('created_at')->get();

        // Transform items for JavaScript
        $projectsData = $items->map(function($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'material' => $item->material,
                'category_id' => $item->category_id,
                'category' => $item->category->name,
                'featured' => $item->featured,
                'image' => $item->images->first() ? \Storage::url($item->images->first()->path) : null,
                'images' => $item->images->map(fn($img) => \Storage::url($img->path))->toArray(),
            ];
        });

        return view('portfolio.index', compact('items', 'categories', 'projectsData'));
    }
}
