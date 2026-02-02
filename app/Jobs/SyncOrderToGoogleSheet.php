<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncOrderToGoogleSheet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $url = config('services.google.sheets_webhook_url');

        if (!$url) {
            Log::warning('Google Sheet Sync Skipped: No URL configured.');
            return;
        }

        try {
            Log::info("Syncing Order #{$this->order->id} to Google Sheets...");

            // Ensure items are loaded
            $this->order->loadMissing('items');
            
            $payload = [
                'id' => $this->order->id,
                'date' => $this->order->created_at->format('Y-m-d H:i:s'),
                'customer_name' => $this->order->contact_info['name'] ?? 'N/A',
                'customer_email' => $this->order->contact_info['email'] ?? 'N/A',
                'customer_phone' => $this->order->contact_info['phone'] ?? 'N/A',
                'status' => $this->order->status,
                'total_price' => $this->order->total_price ?? 0,
                'items_count' => $this->order->items->count(),
                'updated_at' => $this->order->updated_at->format('Y-m-d H:i:s'),
            ];

            $response = Http::post($url, $payload);

            if ($response->successful()) {
                Log::info("Google Sheet Sync Response: " . $response->body());
            } else {
                Log::error('Google Sheet Sync Failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Google Sheet Sync Error: ' . $e->getMessage());
        }
    }
}
