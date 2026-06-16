<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Order $order) {}

    /**
     * Broadcast ke channel "orders" — semua kasir yang terkoneksi akan menerima.
     */
    public function broadcastOn(): array
    {
        return [new Channel('orders')];
    }

    public function broadcastAs(): string
    {
        return 'new-order';
    }

    /**
     * Data yang dikirim ke frontend via WebSocket.
     */
    public function broadcastWith(): array
    {
        return [
            'id'             => $this->order->id,
            'order_number'   => $this->order->order_number,
            'table_number'   => $this->order->table_number,
            'status'         => $this->order->status,
            'payment_method' => $this->order->payment_method,
            'total_price'    => $this->order->total_price,
            'formatted_total'=> $this->order->formatted_total,
            'created_at'     => $this->order->created_at->format('H:i'),
            'items'          => $this->order->items->map(fn ($item) => [
                'name'        => $item->menu_name,
                'quantity'    => $item->quantity,
                'sugar_label' => $item->sugar_label,
                'serve_label' => $item->serve_label,
                'has_sugar'   => $item->has_sugar,
                'has_serve'   => $item->has_serve,
                'subtotal'    => $item->subtotal,
                'notes'       => $item->notes,
            ])->toArray(),
        ];
    }
}
