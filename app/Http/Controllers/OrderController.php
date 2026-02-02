<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

use App\Models\CalculatorSetting;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function create()
    {
        $materials = \App\Models\Material::with('colors')->get();
        $colors = \App\Models\Color::all(); // Keep this if used for "Sketch" mode, or remove if not needed.
        
        $qualities = CalculatorSetting::where('category', 'quality')->where('is_active', true)->orderBy('sort_order')->get();
        $infills = CalculatorSetting::where('category', 'infill')->where('is_active', true)->orderBy('sort_order')->get();
        
        return view('order.create', compact('materials', 'colors', 'qualities', 'infills'));
    }

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();
        
        $order = $this->orderService->createOrder(
            $data, 
            auth()->id(), 
            $request->file('file'),
            $request->file('blueprints')
        );

        return redirect()->route('order.success')->with('order_id', $order->id);
    }

    public function success()
    {
        $orderId = session('order_id');
        return view('order.success', compact('orderId'));
    }
}
