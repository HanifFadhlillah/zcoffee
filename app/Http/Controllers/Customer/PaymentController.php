<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Buat transaksi Midtrans QRIS dan kembalikan token + snap URL.
     * POST /payment/create/{order}
     */
    public function create(Order $order)
    {
        // Hanya order dengan metode QRIS & status pending yang bisa diproses
        if ($order->payment_method !== 'qris' || $order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak valid untuk pembayaran QRIS.',
            ], 422);
        }

        // Jika sudah ada snap_token, kembalikan yang lama (tidak perlu request ulang)
        if ($order->snap_token) {
            return response()->json([
                'success'    => true,
                'snap_token' => $order->snap_token,
                'snap_url'   => $order->snap_url,
            ]);
        }

        try {
            $snapToken = $this->createSnapTransaction($order);

            // Simpan token ke database
            $order->update([
                'snap_token' => $snapToken['token'],
                'snap_url'   => $snapToken['redirect_url'],
            ]);

            return response()->json([
                'success'    => true,
                'snap_token' => $snapToken['token'],
                'snap_url'   => $snapToken['redirect_url'],
            ]);

        } catch (\Throwable $e) {
            Log::error('Midtrans create snap error', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi pembayaran. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Webhook / Notification handler dari Midtrans.
     * POST /payment/notification
     */
    public function notification(Request $request)
    {
        try {
            $payload = $request->all();

            // Verifikasi signature key dari Midtrans
            $orderId           = $payload['order_id'] ?? '';
            $statusCode        = $payload['status_code'] ?? '';
            $grossAmount       = $payload['gross_amount'] ?? '';
            $serverKey         = config('midtrans.server_key');
            $signatureKey      = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($signatureKey !== ($payload['signature_key'] ?? '')) {
                Log::warning('Midtrans: signature key tidak valid', ['order_id' => $orderId]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }

            $transactionStatus = $payload['transaction_status'] ?? '';
            $fraudStatus       = $payload['fraud_status'] ?? 'accept';

            // Cari order berdasarkan order_number (yang kita kirim sebagai order_id ke Midtrans)
            $order = Order::where('order_number', $orderId)->first();

            if (! $order) {
                Log::warning('Midtrans notification: order tidak ditemukan', ['order_id' => $orderId]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Proses status dari Midtrans
            // settlement = pembayaran berhasil dikonfirmasi
            // capture = untuk kartu kredit, fraud_status harus 'accept'
            if (
                $transactionStatus === 'settlement' ||
                ($transactionStatus === 'capture' && $fraudStatus === 'accept')
            ) {
                if ($order->status === 'pending') {
                    // Menyimpan transaction_id dari Midtrans
                    $order->update(['midtrans_transaction_id' => $payload['transaction_id'] ?? 'paid']);

                    // Broadcast ke kasir agar muncul di dashboard sebagai order baru yang sudah lunas
                    broadcast(new \App\Events\NewOrderReceived($order))->toOthers();

                    Log::info('Midtrans: pembayaran berhasil, pesanan masuk ke antrean', ['order_number' => $order->order_number]);
                }
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                if ($order->status === 'pending') {
                    $order->update(['status' => 'cancelled']);
                    broadcast(new \App\Events\OrderStatusUpdated($order))->toOthers();
                    Log::info('Midtrans: pembayaran dibatalkan/expired', [
                        'order_number' => $order->order_number,
                        'status'       => $transactionStatus,
                    ]);
                }
            }

            return response()->json(['message' => 'OK']);

        } catch (\Throwable $e) {
            Log::error('Midtrans notification error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    /**
     * Buat transaksi Snap di Midtrans API.
     * Mengembalikan array ['token' => ..., 'redirect_url' => ...]
     */
    private function createSnapTransaction(Order $order): array
    {
        $serverKey = config('midtrans.server_key');
        $isProduction = config('midtrans.is_production', false);

        $snapUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        // Siapkan item detail
        $itemDetails = $order->items->map(fn ($item) => [
            'id'       => (string) $item->id, // Menggunakan ID item agar unik
            'price'    => (int) $item->menu_price,
            'quantity' => (int) $item->quantity,
            'name'     => mb_substr($item->menu_name, 0, 50), // Midtrans max 50 char
        ])->toArray();

        $payload = [
            'transaction_details' => [
                'order_id'     => $order->order_number, // order_number sebagai ID unik
                'gross_amount' => (int) $order->total_price,
            ],
            'item_details'  => $itemDetails,
            'customer_details' => [
                'first_name' => 'Pelanggan',
                'last_name'  => 'Meja ' . $order->table_number,
                'email'      => 'pelanggan@zcoffee.id', // placeholder
            ],
            // Sesuaikan waktu expired Midtrans dengan countdown frontend (10 menit)
            'custom_expiry' => [
                'order_time'      => now()->format('Y-m-d H:i:s O'),
                'expiry_duration' => 10,
                'unit'            => 'minute'
            ],
            // Hanya tampilkan metode pembayaran QRIS
            'enabled_payments' => ['other_qris'],
            'callbacks' => [
                'finish' => url('/order/' . $order->table_number . '?paid=1'),
            ],
            'expiry' => [
                'unit'     => 'minutes',
                'duration' => 10,
            ],
        ];

        $response = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')
            ->post($snapUrl, $payload);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Midtrans API error: ' . $response->status() . ' — ' . $response->body()
            );
        }

        return $response->json();
    }
}
