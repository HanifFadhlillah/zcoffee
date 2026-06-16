<?php

namespace App\Http\Controllers\Customer;

use App\Events\NewOrderReceived;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Tampilkan halaman menu untuk pelanggan.
     * URL: /order/{table}
     */
    public function index(int $table)
    {
        abort_if($table < 1 || $table > 50, 404, 'Nomor meja tidak valid.');

        $menus = Menu::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('customer.menu', [
            'table' => $table,
            'menus' => $menus,
            'categories' => [
                'espresso'   => 'Espresso Based',
                'manual'     => 'Manual Brewed',
                'noncoffee'  => 'Non Coffee',
                'maincourse' => 'Main Course',
                'snack'      => 'Snack',
            ],
        ]);
    }

    /**
     * Simpan order baru & broadcast ke kasir.
     * POST /order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'table_number'              => 'required|integer|min:1|max:50',
            'payment_method'            => 'required|in:qris,cash',
            'customer_note'             => 'nullable|string|max:255',
            'items'                     => 'required|array|min:1',
            'items.*.menu_id'           => 'required|exists:menus,id',
            'items.*.quantity'          => 'required|integer|min:1|max:10',
            'items.*.sugar_level'       => 'required|in:less,normal,extra',
            'items.*.ice_level'         => 'required|in:no_ice,less,normal',
            'items.*.serve_type'        => 'nullable|in:hot,ice',
            'items.*.notes'             => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Validasi semua menu masih aktif
        $menuIds  = collect($request->items)->pluck('menu_id');
        $menus    = Menu::active()->whereIn('id', $menuIds)->get()->keyBy('id');

        if ($menus->count() !== $menuIds->unique()->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa menu tidak tersedia.',
            ], 422);
        }

        // Hitung total
        $totalPrice = 0;
        foreach ($request->items as $item) {
            $menu = $menus[$item['menu_id']];
            $totalPrice += $menu->price * $item['quantity'];
        }

        DB::beginTransaction();
        try {
            // Buat order
            $order = Order::create([
                'table_number'   => $request->table_number,
                'total_price'    => $totalPrice,
                'payment_method' => $request->payment_method,
                'status'         => 'pending',
                'customer_note'  => $request->customer_note,
            ]);

            // Buat order items
            foreach ($request->items as $itemData) {
                $menu = $menus[$itemData['menu_id']];
                OrderItem::create([
                    'order_id'    => $order->id,
                    'menu_id'     => $menu->id,
                    'menu_name'   => $menu->name,
                    'menu_price'  => $menu->price,
                    'quantity'    => $itemData['quantity'],
                    'sugar_level' => $itemData['sugar_level'],
                    'ice_level'   => $itemData['ice_level'],
                    'serve_type'  => $itemData['serve_type'] ?? null,
                    'subtotal'    => $menu->price * $itemData['quantity'],
                    'notes'       => $itemData['notes'] ?? null,
                ]);
            }

            $order->load('items.menu');
            DB::commit();

            // 🔔 Broadcast real-time ke kasir
            broadcast(new NewOrderReceived($order))->toOthers();

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan order. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success'        => true,
            'order_number'   => $order->order_number,
            'total_price'    => $order->total_price,
            'payment_method' => $order->payment_method,
            'order_id'       => $order->id,
        ]);
    }

    /**
     * Cek status pembayaran order (digunakan oleh polling frontend pelanggan).
     * GET /order/check/{orderNumber}
     */
    public function checkPayment(string $orderNumber)
    {
        $order = Order::with('items.menu')
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order tidak ditemukan.'], 404);
        }

        $items = $order->items->map(fn ($item) => [
            'name'        => $item->menu_name,
            'qty'         => $item->quantity,
            'price'       => $item->menu_price,
            'subtotal'    => $item->subtotal,
            'sugar_label' => $item->sugar_label,
            'serve_label' => $item->serve_label,
            'has_sugar'   => $item->has_sugar,
            'has_serve'   => $item->has_serve,
            'notes'       => $item->notes,
        ]);

        $isPaid = in_array($order->status, ['processing', 'completed']);

        return response()->json([
            'success'      => true,
            'order_number' => $order->order_number,
            'status'       => $order->status,
            'paid'         => $isPaid,
            'total_price'  => $order->total_price,
            'items'        => $items,
        ]);
    }
}
