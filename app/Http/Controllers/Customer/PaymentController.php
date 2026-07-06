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
     * Buat transaksi Xendit QRIS Dinamis dan kembalikan QR string.
     * POST /payment/create/{order}
     */
    public function create(Order $order)
    {
        if ($order->payment_method !== 'qris' || $order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak valid untuk pembayaran QRIS.',
            ], 422);
        }

        // Jika sudah ada QR ID Xendit, kembalikan yang lama
        if ($order->xendit_qr_id && $order->xendit_qr_string) {
            return response()->json([
                'success'   => true,
                'qr_string' => $order->xendit_qr_string,
            ]);
        }

        try {
            $secretKey = config('xendit.secret_key');
            
            $response = Http::withBasicAuth($secretKey, '')
                ->withHeaders([
                    'api-version' => '2022-07-31'
                ])
                ->post('https://api.xendit.co/qr_codes', [
                    'reference_id' => $order->order_number,
                    'type'         => 'DYNAMIC',
                    'currency'     => 'IDR',
                    'amount'       => (int) $order->total_price,
                ]);

            if (!$response->successful()) {
                Log::error('Xendit QR Error Response', ['body' => $response->json()]);
                throw new \Exception('Failed to generate QR string from Xendit API');
            }

            $data = $response->json();
            $qrId = $data['id'] ?? '';
            $qrString = $data['qr_string'] ?? '';

            if (!$qrString) {
                throw new \Exception('Failed to generate QR string from Xendit');
            }

            // Simpan QR ID & string ke database
            $order->update([
                'xendit_qr_id'     => $qrId,
                'xendit_qr_string' => $qrString,
            ]);

            return response()->json([
                'success'   => true,
                'qr_string' => $qrString,
            ]);

        } catch (\Throwable $e) {
            Log::error('Xendit create QRIS error', [
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
     * Webhook / Notification handler dari Xendit.
     * POST /payment/notification
     */
    public function notification(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Xendit webhook received', $payload);

            // Verifikasi webhook token dari Xendit
            $callbackToken = $request->header('x-callback-token');
            $expectedToken = config('xendit.webhook_token');
            
            if (!empty($expectedToken) && $callbackToken !== $expectedToken) {
                Log::warning('Xendit webhook: Unauthorized callback token', ['token' => $callbackToken]);
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Xendit mengirim payload: { event, data: { reference_id, status } }
            // Untuk event QR_CODE.SUCCEEDED
            $event = $payload['event'] ?? '';
            $status = $payload['data']['status'] ?? '';
            $orderNumber = $payload['data']['reference_id'] ?? '';

            if (empty($orderNumber)) {
                Log::warning('Xendit webhook: payload tidak valid (tanpa reference_id)', $payload);
                return response()->json(['message' => 'Invalid payload'], 400);
            }

            // Cari order berdasarkan order_number
            $order = Order::where('order_number', $orderNumber)->first();

            if (!$order) {
                Log::warning('Xendit webhook: order tidak ditemukan', ['order_number' => $orderNumber]);
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Proses jika status COMPLETED
            if ($event === 'qr.payment' && (strtoupper($status) === 'COMPLETED' || strtoupper($status) === 'PAID')) {
                if ($order->status === 'pending') {
                    // Tandai order sebagai processing dan broadcast ke kasir
                    $order->markAsProcessing();
                    broadcast(new \App\Events\NewOrderReceived($order))->toOthers();

                    Log::info('Xendit: pembayaran QRIS berhasil, pesanan masuk antrean', [
                        'order_number' => $order->order_number,
                    ]);
                }
            }

            return response()->json(['message' => 'OK']);

        } catch (\Throwable $e) {
            Log::error('Xendit webhook error', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}
