<?php

namespace App\Observers;

use App\Jobs\SyncOrderToGoogleSheet;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "saved" event.
     * Use saved to catch both created and updated events.
     */
    public function saved(Order $order): void
    {
        // Only dispatch if the webhook URL is configured
        if (config('services.google.sheets_webhook_url')) {
            SyncOrderToGoogleSheet::dispatch($order);
        }
    }
}
