<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;
use App\Models\PortfolioCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = PortfolioCategory::withCount('items')->ordered()->get();
        $selectedCategory = $request->get('category');
        
        $items = PortfolioItem::with(['images', 'category'])
            ->when($selectedCategory, function($query) use ($selectedCategory) {
                return $query->where('category_id', $selectedCategory);
            })
            ->latest()
            ->get();
        
        return view('admin.portfolio.index', compact('items', 'categories', 'selectedCategory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = PortfolioCategory::ordered()->get();
        return view('admin.portfolio.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:portfolio_items',
            'category_id' => 'required|exists:portfolio_categories,id',
            'material' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'glb_model' => 'nullable|file|mimetypes:model/gltf-binary,application/octet-stream|max:10240',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'delete_images' => 'array',
            'delete_images.*' => 'integer'
        ]);

        // Handle GLB model upload
        if ($request->hasFile('glb_model')) {
            $validated['glb_model'] = $request->file('glb_model')->store('models', 'public');
        }

        // Convert featured checkbox
        $validated['featured'] = $request->has('featured');

        $item = PortfolioItem::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('portfolio', 'public');
                $item->images()->create([
                    'path' => $path,
                    'sort_order' => $index
                ]);
            }
        }

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio item created.');
    }

    /**
     * Display the specified resource.
     */
    public function edit(string $id)
    {
        $portfolio = PortfolioItem::with('images')->findOrFail($id);
        $categories = PortfolioCategory::ordered()->get();
        return view('admin.portfolio.edit', compact('portfolio', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $portfolio = PortfolioItem::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:portfolio_items,slug,' . $id,
            'category_id' => 'required|exists:portfolio_categories,id',
            'material' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'glb_model' => 'nullable|file|mimetypes:model/gltf-binary,application/octet-stream|max:10240',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'delete_images' => 'array',
            'delete_images.*' => 'integer'
        ]);

        // Handle GLB model upload
        if ($request->hasFile('glb_model')) {
            // Delete old model file if it exists
            if ($portfolio->glb_model) {
                Storage::disk('public')->delete($portfolio->glb_model);
            }
            $validated['glb_model'] = $request->file('glb_model')->store('models', 'public');
        }

        // Handle GLB model deletion
        if ($request->has('delete_glb_model') && $portfolio->glb_model) {
            Storage::disk('public')->delete($portfolio->glb_model);
            $validated['glb_model'] = null;
        }

        // Convert featured checkbox
        $validated['featured'] = $request->has('featured');

        $portfolio->update($validated);

        if ($request->hasFile('images')) {
            $startOrder = $portfolio->images()->max('sort_order') + 1;
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('portfolio', 'public');
                $portfolio->images()->create([
                    'path' => $path,
                    'sort_order' => $startOrder + $index
                ]);
            }
        }

        // Handle deletion of images if needed (could be array of IDs to delete)
        if ($request->has('delete_images')) {
            $imagesToDelete = $portfolio->images()
                ->whereIn('id', $request->delete_images)
                ->get();
            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio item updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $portfolio = \App\Models\PortfolioItem::findOrFail($id);
        if ($portfolio->glb_model) {
            Storage::disk('public')->delete($portfolio->glb_model);
        }

        foreach ($portfolio->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        // Note: DB migration has cascadeOnDelete for PortfolioImage table.
        $portfolio->delete();

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio item deleted.');
    }
}
