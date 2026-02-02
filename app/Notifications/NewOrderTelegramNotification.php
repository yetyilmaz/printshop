<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewOrderTelegramNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    protected function escapeHtml($text)
    {
        if (is_null($text)) {
            return '';
        }
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function toTelegram(object $notifiable)
    {
        $order = $this->order;
        
        // Ensure relations are loaded to prevent lazy loading violations
        $order->loadMissing('items');

        $itemsCount = $order->items->count();
        $totalPrice = number_format($order->total_price, 0, ',', ' ');
        
        // Check contact info array or user relation
        $name = $this->escapeHtml($order->contact_info['name'] ?? 'N/A');
        $email = $this->escapeHtml($order->contact_info['email'] ?? 'N/A');
        $phone = $this->escapeHtml($order->contact_info['phone'] ?? 'N/A');
        $desc = $this->escapeHtml($order->contact_info['description'] ?? 'Нет описания');
        $type = $this->escapeHtml($order->contact_info['product_type'] ?? 'N/A');
        $status = $this->escapeHtml(ucfirst($order->status));
        
        $emoji = match($order->status) {
            'needs_review' => '🟡',
            'quoted' => '🟢',
            default => '📦'
        };

        return "{$emoji} <b>Новый заказ #{$order->id}</b>\n\n" .
               "User: <b>{$name}</b>\n" .
               "Phone: <code>{$phone}</code>\n" .
               "Email: <code>{$email}</code>\n\n" .
               "Type: {$type}\n" .
               "Status: {$status}\n" .
               "Items: {$itemsCount}\n" .
               "Est. Price: <b>{$totalPrice} ₸</b>\n\n" .
               "Comment:\n<i>{$desc}</i>";
    }
}
