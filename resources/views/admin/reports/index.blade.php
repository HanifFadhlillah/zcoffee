<x-cashier-layout>
    <x-slot:title>Rekap Penjualan — ZCoffee</x-slot:title>

    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="font-serif text-2xl text-stone-900">Rekap Penjualan</h1>
                <p class="text-stone-400 text-sm">Laporan transaksi terselesaikan</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.reports.index') }}" method="GET" class="flex gap-2 bg-stone-100 p-1 rounded-xl">
                    <button type="submit" name="period" value="today" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $period === 'today' ? 'bg-white shadow-sm text-stone-900' : 'text-stone-500 hover:text-stone-700' }}">Hari ini</button>
                    <button type="submit" name="period" value="week" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $period === 'week' ? 'bg-white shadow-sm text-stone-900' : 'text-stone-500 hover:text-stone-700' }}">7 Hari Terakhir</button>
                    <button type="submit" name="period" value="month" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $period === 'month' ? 'bg-white shadow-sm text-stone-900' : 'text-stone-500 hover:text-stone-700' }}">30 Hari Terakhir</button>
                </form>

                <a href="{{ route('admin.reports.export', ['period' => $period]) }}" class="inline-flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Excel
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-stone-100 flex items-center justify-between">
                <div>
                    <p class="text-xs text-stone-400 font-medium mb-1">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-stone-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-green-50 rounded-full flex items-center justify-center text-green-600 text-xl">
                    💰
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-stone-100 flex items-center justify-between">
                <div>
                    <p class="text-xs text-stone-400 font-medium mb-1">Total Transaksi</p>
                    <p class="text-2xl font-bold text-stone-900">{{ $totalOrders }} Order</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 text-xl">
                    🧾
                </div>
            </div>
        </div>

        <div class="bg-white border border-stone-100 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-stone-600">
                    <thead class="bg-stone-50 border-b border-stone-100 text-xs font-semibold text-stone-500">
                        <tr>
                            <th class="px-5 py-4">Waktu</th>
                            <th class="px-5 py-4">Order ID</th>
                            <th class="px-5 py-4">Meja</th>
                            <th class="px-5 py-4">Metode</th>
                            <th class="px-5 py-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-50">
                        @forelse($orders as $order)
                        <tr class="hover:bg-stone-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-medium text-stone-900">{{ $order->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-stone-400">{{ $order->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-stone-900">{{ $order->order_number }}</span>
                            </td>
                            <td class="px-5 py-4">
                                Meja {{ $order->table_number }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-semibold uppercase tracking-wider px-2 py-1 rounded border {{ $order->payment_method === 'qris' ? 'border-blue-200 text-blue-600 bg-blue-50' : 'border-green-200 text-green-700 bg-green-50' }}">
                                    {{ $order->payment_method }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right font-bold text-stone-900">
                                {{ $order->formatted_total }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-stone-400">
                                Tidak ada transaksi pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-cashier-layout>
