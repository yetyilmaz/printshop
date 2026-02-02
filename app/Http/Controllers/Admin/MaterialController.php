<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::all(); // with('colors') is default load or we add it
        return view('admin.materials.index', compact('materials'));
    }

    public function create()
    {
        return view('admin.materials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_per_cm3' => 'required|numeric|min:0',
            'time_multiplier' => 'required|numeric|min:0.1',
            'type' => 'nullable|string|max:255',
            // 'colors' is now an array of objects
            'colors' => 'array',
            'colors.*.name' => 'required|string|max:255',
            'colors.*.hex' => 'required|string|max:20',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $material = Material::create(\Illuminate\Support\Arr::except($validated, ['colors']));
        
        if (isset($validated['colors'])) {
            foreach ($validated['colors'] as $colorData) {
                $material->colors()->create([
                    'name' => $colorData['name'],
                    'hex_code' => $colorData['hex'],
                ]);
            }
        }

        return redirect()->route('admin.materials.index')->with('success', 'Material created successfully.');
    }

    public function edit(Material $material)
    {
        // $material->load('colors'); implicitly available if relationship set
        return view('admin.materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_per_cm3' => 'required|numeric|min:0',
            'time_multiplier' => 'required|numeric|min:0.1',
            'type' => 'nullable|string|max:255',
            'colors' => 'array',
            'colors.*.id' => 'nullable|integer',
            'colors.*.name' => 'required|string|max:255',
            'colors.*.hex' => 'required|string|max:20',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $material->update(\Illuminate\Support\Arr::except($validated, ['colors']));

        if (isset($validated['colors'])) {
            // Get IDs present in the form
            $submittedIds = array_filter(array_column($validated['colors'], 'id'));
            
            // Delete colors not in the form
            $material->colors()->whereNotIn('id', $submittedIds)->delete();
            
            // Update or Create
            foreach ($validated['colors'] as $colorData) {
                if (!empty($colorData['id'])) {
                    $material->colors()->where('id', $colorData['id'])->update([
                        'name' => $colorData['name'],
                        'hex_code' => $colorData['hex']
                    ]);
                } else {
                    $material->colors()->create([
                        'name' => $colorData['name'],
                        'hex_code' => $colorData['hex']
                    ]);
                }
            }
        } else {
            // If colors array empty, delete all? or maybe they removed all rows
            $material->colors()->delete();
        }

        return redirect()->route('admin.materials.index')->with('success', 'Material updated successfully.');
    }

    public function destroy(Material $material)
    {
        $material->delete();
        return redirect()->route('admin.materials.index')->with('success', 'Material deleted successfully.');
    }
}
