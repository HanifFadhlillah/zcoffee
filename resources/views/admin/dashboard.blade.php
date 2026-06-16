<x-cashier-layout>
    <x-slot:title>Admin Dashboard — ZCoffee</x-slot:title>

    @push('styles')
    <style>
        .kpi-card { transition: transform 0.15s, box-shadow 0.15s; }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    </style>
    @endpush

    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="font-serif text-2xl text-stone-900">Dashboard Statistik</h1>
                <p class="text-stone-400 text-sm">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
            </div>
            <div class="flex gap-2 bg-stone-100 p-1 rounded-xl">
                <a href="?period=today" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $period === 'today' ? 'bg-white shadow-sm text-stone-900' : 'text-stone-500 hover:text-stone-700' }}">Hari ini</a>
                <a href="?period=week" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $period === 'week' ? 'bg-white shadow-sm text-stone-900' : 'text-stone-500 hover:text-stone-700' }}">7 Hari</a>
                <a href="?period=month" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $period === 'month' ? 'bg-white shadow-sm text-stone-900' : 'text-stone-500 hover:text-stone-700' }}">Bulan ini</a>
            </div>
        </div>

        {{-- ── KPI CARDS ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="kpi-card bg-white rounded-2xl p-5 border border-stone-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-xl">💰</div>
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+12%</span>
                </div>
                <p class="text-2xl font-bold text-stone-900">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</p>
                <p class="text-xs text-stone-400 mt-1">Revenue {{ strtolower($stats['label']) }}</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-stone-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-xl">📦</div>
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">+3</span>
                </div>
                <p class="text-2xl font-bold text-stone-900">{{ $stats['orders'] }}</p>
                <p class="text-xs text-stone-400 mt-1">Total order {{ strtolower($stats['label']) }}</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-stone-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center text-xl">📊</div>
                </div>
                <p class="text-2xl font-bold text-stone-900">Rp {{ number_format($stats['avg_transaction'], 0, ',', '.') }}</p>
                <p class="text-xs text-stone-400 mt-1">Rata-rata transaksi</p>
            </div>
            <div class="kpi-card bg-white rounded-2xl p-5 border border-stone-100">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center text-xl">⏳</div>
                    @if($stats['pending_orders'] > 0)
                    <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">Perlu aksi</span>
                    @endif
                </div>
                <p class="text-2xl font-bold text-stone-900">{{ $stats['pending_orders'] }}</p>
                <p class="text-xs text-stone-400 mt-1">Order pending</p>
            </div>
        </div>

        {{-- ── CHARTS ROW ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            {{-- Revenue Chart (takes 2/3 width) --}}
            <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-stone-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-stone-900">Grafik Penjualan</h3>
                    <p class="text-xs text-stone-400">{{ $stats['label'] }}</p>
                </div>
                <div class="h-48">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            {{-- Top Menu --}}
            <div class="bg-white rounded-2xl p-5 border border-stone-100">
                <h3 class="font-semibold text-stone-900 mb-4">🏆 Menu Terlaris</h3>
                @forelse($topMenus as $i => $menu)
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0
                        {{ $i === 0 ? 'bg-amber-100 text-amber-700' : ($i === 1 ? 'bg-stone-100 text-stone-600' : 'bg-stone-50 text-stone-400') }}">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-stone-700 truncate">{{ $menu['menu_name'] }}</p>
                        <div class="mt-1 h-1.5 bg-stone-100 rounded-full overflow-hidden">
                            <div class="h-full bg-stone-900 rounded-full"
                                 style="width: {{ $topMenus[0]['total_sold'] > 0 ? round($menu['total_sold'] / $topMenus[0]['total_sold'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs text-stone-400 w-8 text-right">{{ $menu['total_sold'] }}x</span>
                </div>
                @empty
                <p class="text-sm text-stone-400 text-center py-4">Belum ada data penjualan</p>
                @endforelse
            </div>
        </div>

        {{-- ── RECENT ORDERS ── --}}
        <div class="bg-white rounded-2xl border border-stone-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-50 flex items-center justify-between">
                <h3 class="font-semibold text-stone-900">Order Terbaru Hari Ini</h3>
                <a href="{{ route('cashier.dashboard') }}" class="text-xs text-amber-600 font-semibold hover:text-amber-700">Lihat semua →</a>
            </div>
            @php
                $recentOrders = \App\Models\Order::with('items')
                    ->today()->orderByDesc('created_at')->limit(8)->get();
            @endphp
            <div class="divide-y divide-stone-50">
                @forelse($recentOrders as $order)
                <div class="px-5 py-3 flex items-center gap-4">
                    <div class="w-8 h-8 bg-stone-100 rounded-lg flex items-center justify-center text-stone-700 text-xs font-bold flex-shrink-0">
                        M{{ $order->table_number }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-stone-800">{{ $order->order_number }}</p>
                        <p class="text-xs text-stone-400">{{ $order->items->count() }} item · {{ $order->created_at->format('H:i') }}</p>
                    </div>
                    <p class="text-sm font-bold text-stone-900">{{ $order->formatted_total }}</p>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : ($order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                        {{ $order->status_label }}
                    </span>
                    <span class="text-xs font-semibold uppercase tracking-wider px-2 py-1 rounded border
                        {{ $order->payment_method === 'qris' ? 'border-blue-200 text-blue-600' : 'border-green-200 text-green-700' }}">
                        {{ $order->payment_method }}
                    </span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-stone-300 text-sm">Belum ada order hari ini</div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
    const chartData = @json($chartData);

    // Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(d => d.day),
            datasets: [{
                label: 'Revenue (Rp)',
                data: chartData.map(d => d.revenue),
                backgroundColor: chartData.map((d, i) =>
                    i === chartData.length - 1 ? '#F59E0B' : '#1C1917'
                ),
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + ctx.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: {
                    grid: { color: '#F5F5F4' },
                    ticks: {
                        font: { size: 10 },
                        callback: v => 'Rp ' + (v/1000) + 'k'
                    }
                }
            }
        }
    });
    </script>
    @endpush
</x-cashier-layout>
