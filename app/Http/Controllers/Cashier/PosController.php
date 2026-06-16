<?php

namespace App\Http\Controllers\Cashier;

use App\Events\NewOrderReceived;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    /**
     * Tampilkan halaman POS kasir.
     */
    public function index()
    {
        $menus = Menu::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('cashier.pos', [
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
     * Simpan order POS (Take Away).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        // Validasi menu aktif
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
            // Buat order takeaway
            // Kasir yang buat, otomatis "processing" (sedang disiapkan) karena pembayarannya sudah beres di kasir.
            $order = Order::create([
                'order_type'     => 'take_away',
                'table_number'   => null,
                'total_price'    => $totalPrice,
                'payment_method' => $request->payment_method,
                'status'         => 'processing', // Langsung diproses karena sudah dilayani kasir
                'customer_note'  => $request->customer_note,
                'processed_at'   => now(),
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

            // Broadcast real-time ke kasir lain (jika ada) atau dashboard yang sama
            broadcast(new NewOrderReceived($order))->toOthers();

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan order. Error: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success'        => true,
            'order_number'   => $order->order_number,
            'total_price'    => $order->total_price,
            'payment_method' => $order->payment_method,
            'order_id'       => $order->id,
            'receipt_url'    => route('cashier.orders.receipt', $order->id), // Untuk redirect kasir ke cetak struk
        ]);
    }
}
