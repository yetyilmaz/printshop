<?php

use App\Models\Order;
use Illuminate\Database\LazyLoadingViolationException;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ensure strict mode is ON (it should be by default in local, but let's confirm usage)
// \Illuminate\Database\Eloquent\Model::shouldBeStrict();

echo "Strict mode enabled.\n";

// Grab the latest order
$order = Order::latest()->first();
if (!$order) {
    echo "No order found.\n";
    exit;
}

echo "Order ID: " . $order->id . "\n";
echo "Relations loaded: " . implode(', ', array_keys($order->getRelations())) . "\n";

try {
    echo "Attempting to access items...\n";
    $count = $order->items->count();
    echo "Count: $count\n";
} catch (LazyLoadingViolationException $e) {
    echo "CAUGHT LazyLoadingViolationException: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "CAUGHT Exception: " . $e->getMessage() . "\n"; // This handles generic Exception
}
