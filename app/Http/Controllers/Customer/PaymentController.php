<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    /**
     * Buat Midtrans Snap Token dan kembalikan ke frontend.
     * POST /order/payment/snap/{order}
     */
    public function snap(Order $order)
    {
        if ($order->payment_method !== 'qris' || $order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak valid untuk pembayaran QRIS.',
            ], 422);
        }

        // Jika sudah ada snap_token, kembalikan yang lama (hindari double charge)
        if ($order->snap_token) {
            return response()->json([
                'success'    => true,
                'snap_token' => $order->snap_token,
                'snap_url'   => $order->snap_url,
            ]);
        }

        try {
            $serverKey   = config('midtrans.server_key');
            $isProduction = config('midtrans.is_production');

            $baseUrl = $isProduction
                ? 'https://app.midtrans.com/snap/v1/transactions'
                : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

            $payload = [
                'transaction_details' => [
                    'order_id'     => $order->order_number,
                    'gross_amount' => (int) $order->total_price,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_note ?: 'Pelanggan',
                ],
                'expiry' => [
                    'duration' => 10,
                    'unit'     => 'minute',
                ],
            ];

            $response = Http::withBasicAuth($serverKey, '')
                ->post($baseUrl, $payload);

            if (! $response->successful()) {
                Log::error('Midtrans Snap Error Response', ['body' => $response->json()]);
                throw new \Exception('Failed to get Snap token from Midtrans API');
            }

            $data      = $response->json();
            $snapToken = $data['token'] ?? null;
            $snapUrl   = $data['redirect_url'] ?? null;

            if (! $snapToken) {
                throw new \Exception('Snap token tidak tersedia dari Midtrans');
            }

            // Simpan token ke database
            $order->update([
                'snap_token' => $snapToken,
                'snap_url'   => $snapUrl,
            ]);

            return response()->json([
                'success'    => true,
                'snap_token' => $snapToken,
                'snap_url'   => $snapUrl,
            ]);

        } catch (\Throwable $e) {
            Log::error('Midtrans snap token error', [
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

            Log::info('Midtrans webhook received', $payload);

            $serverKey         = config('midtrans.server_key');
            $orderId           = $payload['order_id']           ?? '';
            $statusCode        = $payload['status_code']        ?? '';
            $grossAmount       = $payload['gross_amount']        ?? '';
            $incomingSignature = $payload['signature_key']      ?? '';

            // Jika order_id kosong (seperti saat tombol "Tes URL notifikasi" di-klik di Midtrans Dashboard)
            if (empty($orderId)) {
                Log::info('Midtrans webhook: test ping received', $payload);
                return response()->json(['message' => 'OK']);
            }

            // Verifikasi signature key dari Midtrans
            // Format: SHA512(order_id + status_code + gross_amount + server_key)
            if ($serverKey && $incomingSignature) {
                $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
                if ($incomingSignature !== $expectedSignature) {
                    Log::warning('Midtrans webhook: signature tidak valid', [
                        'incoming' => $incomingSignature,
                        'expected' => $expectedSignature,
                    ]);
                    return response()->json(['message' => 'Unauthorized'], 401);
                }
            }

            $transactionStatus = $payload['transaction_status'] ?? '';
            $fraudStatus       = $payload['fraud_status']       ?? 'accept';
            $midtransId        = $payload['transaction_id']     ?? '';

            // Cari order berdasarkan order_number
            $order = Order::where('order_number', $orderId)->first();

            if (! $order) {
                Log::warning('Midtrans webhook: order tidak ditemukan', ['order_id' => $orderId]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Simpan midtrans_transaction_id
            if ($midtransId) {
                $order->update(['midtrans_transaction_id' => $midtransId]);
            }

            // Proses berdasarkan status transaksi Midtrans
            $isPaid = ($transactionStatus === 'settlement')
                || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

            if ($isPaid && $order->status === 'pending') {
                $order->markAsProcessing();
                broadcast(new \App\Events\NewOrderReceived($order))->toOthers();

                Log::info('Midtrans: pembayaran berhasil, pesanan masuk antrean', [
                    'order_number' => $order->order_number,
                    'status'       => $transactionStatus,
                ]);
            }

            return response()->json(['message' => 'OK']);

        } catch (\Throwable $e) {
            Log::error('Midtrans webhook error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}
