<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PortfolioCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = PortfolioCategory::withCount('items')->ordered()->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:portfolio_categories,slug',
            'description' => 'nullable|string',
            'emoji' => 'nullable|string|max:10',
            'is_featured' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = PortfolioCategory::create($validated);

        // Clear portfolio cache
        app(\App\Repositories\PortfolioRepository::class)->clearCache();

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PortfolioCategory $category)
    {
        $category->load('items');
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PortfolioCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PortfolioCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:portfolio_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'emoji' => 'nullable|string|max:10',
            'is_featured' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        // Clear portfolio cache
        app(\App\Repositories\PortfolioRepository::class)->clearCache();

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PortfolioCategory $category)
    {
        // Check if category has items
        if ($category->items()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing portfolio items. Please remove or reassign items first.');
        }

        $category->delete();

        // Clear portfolio cache
        app(\App\Repositories\PortfolioRepository::class)->clearCache();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
