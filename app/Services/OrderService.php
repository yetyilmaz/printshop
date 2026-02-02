<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\File;
use App\Models\Material;
use App\Models\CalculatorSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewOrderTelegramNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new order with associated files and items.
     */
    public function createOrder(array $data, ?int $userId, ?UploadedFile $file = null, ?array $blueprints = null): Order
    {
        $order = DB::transaction(function () use ($data, $userId, $file, $blueprints) {
            $isStl = $data['order_type'] === 'stl';
            
            // 1. Calculate Price based on Server Data
            $calculatedTotalPrice = 0;
            $materialId = null;
            $volumeCm3 = 0;

            if ($isStl) {
                $materialId = $data['material_id'];
                $volumeCm3 = $data['volume_cm3'];

                $material = Material::findOrFail($materialId);
                
                // Fetch dynamic multipliers from DB
                $qualitySetting = CalculatorSetting::where('category', 'quality')
                    ->where('slug', $data['quality'])
                    ->first();
                $qualityMultiplier = $qualitySetting ? $qualitySetting->multiplier : 1.0;

                $infillSetting = CalculatorSetting::where('category', 'infill')
                    ->where('slug', (string)($data['infill'] ?? '20'))
                    ->first();
                $infillMultiplier = $infillSetting ? $infillSetting->multiplier : 1.0;

                $itemPrice = ($volumeCm3 * $material->price_per_cm3 * $qualityMultiplier * $infillMultiplier * $material->time_multiplier);
                $calculatedTotalPrice = $itemPrice * $data['quantity'];
            }

            // 2. Create Order
            $order = Order::create([
                'user_id' => $userId,
                'contact_info' => [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'product_type' => $data['product_type'] ?? null,
                    'approx_size' => $data['approx_size'] ?? null,
                    'description' => $data['description'] ?? null,
                ],
                // For assist, price is 0 (quote needed). For STL, we calculated it.
                'total_price' => $isStl ? $calculatedTotalPrice : 0, 
                'status' => $isStl ? 'quoted' : 'needs_review',
            ]);

            // 3. Handle STL File & Item
            if ($isStl && $file) {
                $path = $file->store('models'); 
                
                $fileRecord = File::create([
                    'original_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'disk' => 'local',
                    'volume_cm3' => $volumeCm3,
                    'order_id' => $order->id,
                ]);

                // Create Order Item
                OrderItem::create([
                    'order_id' => $order->id,
                    'file_id' => $fileRecord->id,
                    'material_id' => $materialId,
                    'color_id' => $data['color_id'] ?? null,
                    'quantity' => $data['quantity'],
                    'quality_preset' => $data['quality'],
                    'infill_percentage' => $data['infill'] ?? 20,
                    'item_price' => $calculatedTotalPrice / $data['quantity']
                ]);
            }

            // 4. Handle Blueprints
            if ($blueprints) {
                foreach ($blueprints as $blueprintFile) {
                    if ($blueprintFile instanceof UploadedFile) {
                        $path = $blueprintFile->store('blueprints');
                        File::create([
                            'order_id' => $order->id,
                            'original_name' => $blueprintFile->getClientOriginalName(),
                            'file_path' => $path,
                            'disk' => 'local',
                        ]);
                    }
                }
            }

            return $order;
        });

        // Send Telegram Notification (Async)
        try {
            \Illuminate\Support\Facades\Log::info("Attempting to send Telegram notification for Order ID: {$order->id}");
            $notifiable = new AnonymousNotifiable;
            $notifiable->notify(new NewOrderTelegramNotification($order));
        } catch (\Exception $e) {
            // Log silent failure to avoid breaking the user experience
            \Illuminate\Support\Facades\Log::error('Failed to send Telegram notification: ' . $e->getMessage());
        }

        return $order;
    }
}
