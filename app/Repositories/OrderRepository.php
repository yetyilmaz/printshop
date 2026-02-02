<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Collection;

class OrderRepository
{
    /**
     * Get all orders with user relationship
     */
    public function getAllWithUser(): Collection
    {
        return Order::with('user')->latest()->get();
    }

    /**
     * Get order with all relationships for detail view
     */
    public function getWithRelations(int $id): Order
    {
        return Order::with([
            'items.file',
            'items.material',
            'items.color',
            'user',
            'additionalFiles'
        ])->findOrFail($id);
    }

    /**
     * Update order with validation for auto-status change
     */
    public function updateWithAutoStatus(Order $order, array $data): bool
    {
        // Auto-update status to 'quoted' if price is set for 'needs_review' order
        if ($order->status === 'needs_review' && isset($data['total_price']) && $data['total_price'] > 0) {
            $data['status'] = 'quoted';
        }

        return $order->update($data);
    }

    /**
     * Delete order and associated files
     */
    public function deleteWithFiles(Order $order): void
    {
        // Delete additional files from storage
        if ($order->additionalFiles) {
            foreach ($order->additionalFiles as $file) {
                if ($file->file_path && \Storage::exists($file->file_path)) {
                    \Storage::delete($file->file_path);
                }
            }
        }
        
        // Delete order item files
        $order->load('items.file');
        foreach ($order->items as $item) {
            if ($item->file && $item->file->file_path && \Storage::exists($item->file->file_path)) {
                \Storage::delete($item->file->file_path);
            }
        }

        $order->delete();
    }
}
