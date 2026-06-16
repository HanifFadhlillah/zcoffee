<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'today');

        $query = Order::query();
        
        if ($period === 'week') {
            $query->where('created_at', '>=', now()->subDays(6)->startOfDay());
            $label = '7 Hari Terakhir';
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->subDays(29)->startOfDay());
            $label = '30 Hari Terakhir';
        } else {
            $query->today();
            $label = 'Hari Ini';
        }

        $revenue = (int) (clone $query)->completed()->sum('total_price');
        $orderCount = (clone $query)->count();
        $completedCount = (clone $query)->completed()->count();
        $avg = $completedCount > 0 ? (int) ($revenue / $completedCount) : 0;
        $pending = (clone $query)->pending()->count();

        $stats = [
            'revenue'         => $revenue,
            'orders'          => $orderCount,
            'avg_transaction' => $avg,
            'pending_orders'  => $pending,
            'label'           => $label
        ];

        $chartData = $this->getChartData($period);
        $topMenus = OrderItem::topSelling(5, $period);

        return view('admin.dashboard', compact('stats', 'chartData', 'topMenus', 'period'));
    }

    private function getChartData(string $period): array
    {
        $result = [];

        if ($period === 'month') {
            // Last 30 days grouped by 3 days to avoid too many bars
            for ($i = 29; $i >= 0; $i -= 3) {
                $dateStart = now()->subDays($i)->startOfDay();
                $dateEnd = now()->subDays($i - 2)->endOfDay();
                $revenue = (int) Order::completed()->whereBetween('created_at', [$dateStart, $dateEnd])->sum('total_price');
                $result[] = [
                    'day'     => $dateStart->format('d/m'),
                    'revenue' => $revenue,
                ];
            }
        } elseif ($period === 'today') {
             $orders = Order::completed()->today()->get();
             for ($i = 8; $i <= 22; $i += 2) {
                $revenue = $orders->filter(function($o) use ($i) {
                    $hour = (int) $o->created_at->format('H');
                    return $hour >= $i && $hour < $i+2;
                })->sum('total_price');
                $result[] = [
                    'day'     => sprintf('%02d:00', $i),
                    'revenue' => $revenue,
                ];
            }
        } else {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $result[] = [
                    'day'     => $date->isoFormat('ddd'),
                    'revenue' => (int) Order::completed()->whereDate('created_at', $date)->sum('total_price'),
                ];
            }
        }

        return $result;
    }
}
