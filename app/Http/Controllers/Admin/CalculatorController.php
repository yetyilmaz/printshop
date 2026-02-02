<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalculatorSetting;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index()
    {
        $qualitySettings = CalculatorSetting::where('category', 'quality')
            ->orderBy('sort_order')
            ->get();
        
        $infillSettings = CalculatorSetting::where('category', 'infill')
            ->orderBy('sort_order')
            ->get();

        return view('admin.calculator.index', compact('qualitySettings', 'infillSettings'));
    }

    public function bulkUpdate(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.id' => 'required|exists:calculator_settings,id',
            'settings.*.label' => 'required|string|max:255',
            'settings.*.multiplier' => 'required|numeric|min:0',
            'settings.*.sort_order' => 'required|integer',
            'settings.*.is_active' => 'boolean',
        ]);

        foreach ($data['settings'] as $item) {
            $setting = CalculatorSetting::find($item['id']);
            $setting->update([
                'label' => $item['label'],
                'multiplier' => $item['multiplier'],
                'sort_order' => $item['sort_order'],
                'is_active' => isset($item['is_active']), // Checkbox specific handling
            ]);
        }

        return redirect()->route('admin.calculator.index')->with('success', 'Calculator settings updated successfully.');
    }
    
    public function store(Request $request)
    {
        // For adding new presets (like new Infill %)
        $data = $request->validate([
            'category' => 'required|in:quality,infill',
            'slug' => 'required|string|max:50|unique:calculator_settings,slug',
            'label' => 'required|string|max:255',
            'multiplier' => 'required|numeric|min:0',
            'sort_order' => 'required|integer',
        ]);
        
        $data['is_active'] = true;
        
        CalculatorSetting::create($data);
        
        return redirect()->route('admin.calculator.index')->with('success', 'New setting added successfully.');
    }

    public function destroy(CalculatorSetting $calculatorSetting)
    {
        $calculatorSetting->delete();
        return redirect()->route('admin.calculator.index')->with('success', 'Setting deleted successfully.');
    }
}
