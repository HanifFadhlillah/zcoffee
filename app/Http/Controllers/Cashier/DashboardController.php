<?php

namespace App\Http\Controllers\Cashier;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard kasir — menampilkan order aktif secara real-time.
     */
    public function index()
    {
        $activeOrders = Order::activeOrders()
            ->with('items.menu')
            ->orderByRaw("FIELD(status, 'pending', 'processing')")
            ->orderBy('created_at')
            ->get();

        $completedToday = Order::completed()->today()->count();
        $revenueToday   = Order::todayRevenue();

        return view('cashier.dashboard', compact(
            'activeOrders',
            'completedToday',
            'revenueToday',
        ));
    }

    /**
     * Update status order (pending → processing → completed).
     * PATCH /cashier/orders/{order}/status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:processing,completed,cancelled',
        ]);

        $allowedTransitions = [
            'pending'    => ['processing', 'cancelled'],
            'processing' => ['completed', 'cancelled'],
        ];

        $current = $order->status;
        $new     = $request->status;

        if (!in_array($new, $allowedTransitions[$current] ?? [])) {
            return response()->json([
                'success' => false,
                'message' => "Tidak bisa mengubah status dari {$current} ke {$new}.",
            ], 422);
        }

        match ($new) {
            'processing' => $order->markAsProcessing(),
            'completed'  => $order->markAsCompleted(),
            default      => $order->update(['status' => $new]),
        };

        // 🔔 Broadcast perubahan status
        broadcast(new OrderStatusUpdated($order))->toOthers();

        return response()->json([
            'success'      => true,
            'order_number' => $order->order_number,
            'new_status'   => $order->status,
            'status_label' => $order->status_label,
        ]);
    }

    /**
     * Ambil order history (completed/cancelled) — untuk tab riwayat.
     * GET /cashier/orders/history
     */
    public function history()
    {
        $orders = Order::whereIn('status', ['completed', 'cancelled'])
            ->with('items.menu')
            ->today()
            ->orderByDesc('updated_at')
            ->get();

        return response()->json($orders);
    }

    /**
     * Tampilkan struk untuk diprint (58mm/80mm thermal).
     * GET /cashier/orders/{order}/receipt
     */
    public function receipt(Order $order)
    {
        $order->load('items.menu');
        return view('cashier.receipt', compact('order'));
    }
}
