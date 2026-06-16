<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'today'); // today, week, month

        $query = Order::query()->where('status', 'completed');

        if ($period === 'today') {
            $query->today();
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->subDays(6)->startOfDay());
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->subDays(29)->startOfDay());
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $totalRevenue = $orders->sum('total_price');
        $totalOrders = $orders->count();

        return view('admin.reports.index', compact('orders', 'period', 'totalRevenue', 'totalOrders'));
    }

    public function export(Request $request)
    {
        $period = $request->query('period', 'today');

        $query = Order::with('items')->where('status', 'completed');

        if ($period === 'today') {
            $query->today();
            $filename = 'Rekap_Penjualan_Hari_Ini_' . now()->format('Y-m-d');
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->subDays(6)->startOfDay());
            $filename = 'Rekap_Penjualan_7_Hari_Terakhir_' . now()->format('Y-m-d');
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->subDays(29)->startOfDay());
            $filename = 'Rekap_Penjualan_30_Hari_Terakhir_' . now()->format('Y-m-d');
        }

        $orders = $query->orderBy('created_at', 'asc')->get();

        $csvFileName = $filename . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Tanggal', 'Waktu', 'Nomor Order', 'Meja', 'Item Dibeli', 'Metode Pembayaran', 'Total Harga (Rp)'];

        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 to make Excel happy
            fputs($file, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

            fputcsv($file, $columns, ';'); // Use semicolon for Excel compatibility in some locales, or comma. Let's stick to semicolon which is safer for Indonesian locale Excel. Wait, standard is comma. Let's use comma, but sometimes Excel expects semicolon in Indonesia. I'll use comma as it's standard CSV.

            foreach ($orders as $order) {
                $items = $order->items->map(function($item) {
                    return $item->quantity . 'x ' . $item->menu_name;
                })->implode(', ');

                $row = [
                    $order->created_at->format('Y-m-d'),
                    $order->created_at->format('H:i'),
                    $order->order_number,
                    $order->table_number,
                    $items,
                    strtoupper($order->payment_method),
                    $order->total_price
                ];

                fputcsv($file, $row, ',');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
