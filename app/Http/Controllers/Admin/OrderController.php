<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = $this->orderRepository->getAllWithUser();
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = $this->orderRepository->getWithRelations($id);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, \App\Models\Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:needs_review,quoted,printing,paid,done,delivered,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json(['status' => 'success']);
    }

    public function download($fileId)
    {
        $file = \App\Models\File::findOrFail($fileId);
        
        if (\Illuminate\Support\Facades\Storage::exists($file->file_path)) {
            return \Illuminate\Support\Facades\Storage::download($file->file_path, $file->original_name);
        }
        
        // Fallback check
        $path = storage_path('app/' . $file->file_path);
        if (file_exists($path)) {
            return response()->download($path, $file->original_name);
        }

        abort(404, 'File not found');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:needs_review,quoted,printing,paid,done,delivered,cancelled',
            'admin_notes' => 'nullable|string',
            'total_price' => 'nullable|numeric|min:0'
        ]);

        $this->orderRepository->updateWithAutoStatus($order, $validated);

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        try {
            $this->orderRepository->deleteWithFiles($order);
            return response()->json(['message' => 'Order deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete order: ' . $e->getMessage()], 500);
        }
    }
}
