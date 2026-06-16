<x-cashier-layout>
    <x-slot:title>Dashboard Kasir — ZCoffee</x-slot:title>

    @push('styles')
    <style>
        .order-card-enter { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .pulse-new { animation: pulseGlow 1.2s ease-in-out 3; }
        @keyframes pulseGlow {
            0%,100% { box-shadow: 0 0 0 0 rgba(245,158,11,0); }
            50%      { box-shadow: 0 0 0 6px rgba(245,158,11,0.2); }
        }
    </style>
    @endpush

    @php
        $ordersData = $activeOrders->map(fn($o) => [
            'id'             => $o->id,
            'order_number'   => $o->order_number,
            'table_number'   => $o->table_number,
            'status'         => $o->status,
            'payment_method' => $o->payment_method,
            'total_price'    => $o->total_price,
            'created_at'     => $o->created_at->format('H:i'),
            'items'          => $o->items->map(fn($i) => [
                'name'        => $i->menu_name,
                'quantity'    => $i->quantity,
                'sugar_label' => $i->sugar_label,
                'serve_label' => $i->serve_label,
                'has_sugar'   => (bool) $i->has_sugar,
                'has_serve'   => (bool) $i->has_serve,
                'subtotal'    => $i->subtotal,
                'notes'       => $i->notes,
            ])->values()->toArray(),
        ])->values()->toArray();
    @endphp

    <div x-data="cashierApp()" x-init="init()" class="flex flex-col h-full">

        {{-- TOP BAR --}}
        <div class="bg-white border-b border-stone-100 px-6 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <h1 class="font-semibold text-stone-900">Order Masuk</h1>
                <span x-show="pendingCount > 0"
                    class="bg-amber-500 text-stone-900 text-xs font-bold px-2 py-0.5 rounded-full"
                    x-text="pendingCount + ' pending'"></span>
                <span class="flex items-center gap-1.5 text-xs" :class="wsConnected ? 'text-green-600' : 'text-red-400'">
                    <span class="w-1.5 h-1.5 rounded-full" :class="wsConnected ? 'bg-green-500 animate-pulse' : 'bg-red-400'"></span>
                    <span x-text="wsConnected ? 'Real-time aktif' : 'Menghubungkan...'"></span>
                </span>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right text-xs text-stone-400">
                    <div class="font-semibold text-stone-700" x-text="'Rp ' + formatNum(todayRevenue)"></div>
                    <div>Revenue hari ini</div>
                </div>
                <div class="w-px h-8 bg-stone-100"></div>
                <div class="flex gap-1 bg-stone-100 p-1 rounded-xl">
                    <button @click="tab = 'active'" :class="tab === 'active' ? 'bg-white shadow-sm text-stone-900' : 'text-stone-500'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">Aktif</button>
                    <button @click="tab = 'history'; loadHistory()" :class="tab === 'history' ? 'bg-white shadow-sm text-stone-900' : 'text-stone-500'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">Riwayat</button>
                </div>
            </div>
        </div>

        {{-- ORDERS --}}
        <div class="flex-1 overflow-y-auto p-6">

            {{-- Active orders tab --}}
            <div x-show="tab === 'active'">
                <div x-show="activeOrders.length === 0" class="flex flex-col items-center justify-center py-20 text-stone-300">
                    <svg class="w-16 h-16 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-sm font-medium">Belum ada order aktif</p>
                    <p class="text-xs mt-1">Order baru akan muncul otomatis</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
                    <template x-for="order in activeOrders" :key="order.id">
                        <div class="bg-white rounded-2xl border overflow-hidden"
                             :class="order._new ? 'border-amber-300 pulse-new order-card-enter' : 'border-stone-100'"
                             :id="'order-' + order.id">
                            {{-- Header --}}
                            <div class="px-4 py-3 flex items-center gap-3 border-b border-stone-50">
                                <div class="w-10 h-10 bg-stone-900 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                     x-text="order.table_number ? 'M' + order.table_number : 'TA'"
                                     :title="order.table_number ? 'Dine In' : 'Take Away'"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-stone-900" x-text="order.order_number"></p>
                                    <p class="text-xs text-stone-400" x-text="order.created_at"></p>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                      :class="{
                                          'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                          'bg-blue-100 text-blue-800': order.status === 'processing',
                                      }" x-text="order.status === 'pending' ? 'Menunggu' : 'Diproses'"></span>
                            </div>

                            {{-- Items --}}
                            <div class="px-4 py-3 space-y-2">
                                <template x-for="(item, idx) in order.items" :key="order.id + '-' + idx">
                                    <div class="flex justify-between items-start gap-2 py-0.5">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-stone-800" x-text="item.name + ' × ' + item.quantity"></p>
                                            <p class="text-xs text-stone-500 mt-0.5"
                                               x-text="[item.has_sugar && item.sugar_label ? item.sugar_label : null, item.has_serve && item.serve_label ? item.serve_label : null].filter(v => v !== null).join(' · ')"></p>
                                            <p class="text-xs text-amber-600 mt-0.5 italic"
                                               x-show="item.notes && item.notes.trim() !== ''"
                                               x-text="'📝 ' + item.notes"></p>
                                        </div>
                                        <p class="text-sm font-semibold text-stone-700 flex-shrink-0" x-text="'Rp ' + formatNum(item.subtotal)"></p>
                                    </div>
                                </template>
                            </div>

                            {{-- Footer --}}
                            <div class="px-4 py-3 bg-stone-50 flex items-center justify-between">
                                <div>
                                    <p class="text-base font-bold text-stone-900" x-text="'Rp ' + formatNum(order.total_price)"></p>
                                    <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded border"
                                          :class="order.payment_method === 'qris' ? 'border-blue-200 text-blue-600 bg-blue-50' : 'border-green-200 text-green-700 bg-green-50'"
                                          x-text="order.payment_method.toUpperCase()"></span>
                                </div>
                                <div class="flex gap-2">
                                    <template x-if="order.status === 'pending'">
                                        <div class="flex gap-2">
                                            <button @click="printReceipt(order.id)"
                                                class="px-3 py-2 bg-stone-100 text-stone-600 hover:bg-stone-200 hover:text-stone-900 rounded-xl text-xs font-semibold transition-all" title="Cetak Ulang Struk">
                                                🖨️ Cetak
                                            </button>
                                            <button @click="if(confirm('Yakin ingin membatalkan order ini?')) updateStatus(order, 'cancelled')"
                                                class="px-3 py-2 bg-red-50 text-red-600 hover:bg-red-500 hover:text-white rounded-xl text-xs font-semibold transition-all">
                                                ✗ Batal
                                            </button>
                                            <button @click="updateStatus(order, 'processing')"
                                                class="px-3 py-2 bg-blue-100 text-blue-800 hover:bg-blue-600 hover:text-white rounded-xl text-xs font-semibold transition-all">
                                                Proses →
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="order.status === 'processing'">
                                        <div class="flex gap-2">
                                            <button @click="printReceipt(order.id)"
                                                class="px-3 py-2 bg-stone-100 text-stone-600 hover:bg-stone-200 hover:text-stone-900 rounded-xl text-xs font-semibold transition-all" title="Cetak Ulang Struk">
                                                🖨️ Cetak
                                            </button>
                                            <button @click="if(confirm('Yakin ingin membatalkan order ini?')) updateStatus(order, 'cancelled')"
                                                class="px-3 py-2 bg-red-50 text-red-600 hover:bg-red-500 hover:text-white rounded-xl text-xs font-semibold transition-all">
                                                ✗ Batal
                                            </button>
                                            <button @click="updateStatus(order, 'completed')"
                                                class="px-3 py-2 bg-green-100 text-green-800 hover:bg-green-600 hover:text-white rounded-xl text-xs font-semibold transition-all">
                                                ✓ Selesai
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- History tab --}}
            <div x-show="tab === 'history'">
                <div class="space-y-3">
                    <template x-for="order in historyOrders" :key="order.id">
                        <div class="bg-white rounded-xl border border-stone-100 px-4 py-3 flex items-center gap-4">
                            <div class="w-9 h-9 bg-stone-100 rounded-xl flex items-center justify-center text-stone-600 text-xs font-bold flex-shrink-0"
                                 x-text="order.table_number ? 'M' + order.table_number : 'TA'"
                                 :title="order.table_number ? 'Dine In' : 'Take Away'"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold" x-text="order.order_number"></p>
                                <p class="text-xs text-stone-400" x-text="(order.items ? order.items.length : 0) + ' item · ' + order.created_at"></p>
                            </div>
                            <p class="text-sm font-bold" x-text="'Rp ' + formatNum(order.total_price)"></p>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                  :class="order.status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                  x-text="order.status === 'cancelled' ? 'Batal' : 'Selesai'"></span>
                        </div>
                    </template>
                    <div x-show="historyOrders.length === 0" class="text-center py-10 text-stone-300 text-sm">
                        Belum ada riwayat hari ini
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TOAST NOTIFICATION --}}
    <div id="toast-notif" class="fixed top-4 right-4 z-50 max-w-sm transform translate-x-full transition-transform duration-300">
        <div class="bg-stone-900 text-white rounded-2xl p-4 shadow-2xl">
            <p class="text-amber-400 font-bold text-sm" id="toast-title">Order Baru!</p>
            <p class="text-white/70 text-xs mt-0.5" id="toast-body"></p>
        </div>
    </div>

    @push('scripts')
    <script>
    function cashierApp() {
        return {
            tab: 'active',
            wsConnected: false,
            activeOrders: {!! json_encode($ordersData) !!},
            historyOrders: [],
            todayRevenue: {{ $revenueToday }},

            get pendingCount() {
                return this.activeOrders.filter(o => o.status === 'pending').length;
            },

            formatNum(n) { return (n ?? 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },

            init() {
                this.listenWebSocket();
            },

            listenWebSocket() {
                if (typeof window.Echo === 'undefined') {
                    console.warn('Echo not loaded');
                    return;
                }
                try {
                    window.Echo.connector.pusher.connection.bind('connected', () => { this.wsConnected = true; });
                    window.Echo.connector.pusher.connection.bind('disconnected', () => { this.wsConnected = false; });

                    window.Echo.channel('orders')
                        .listen('.new-order', (data) => {
                            data._new = true;
                            this.activeOrders.unshift(data);
                            this.todayRevenue += data.total_price;
                            this.showNotification(data);
                            setTimeout(() => {
                                const o = this.activeOrders.find(x => x.id === data.id);
                                if (o) o._new = false;
                            }, 5000);
                        })
                        .listen('.order-status-updated', (data) => {
                            const o = this.activeOrders.find(x => x.id === data.id);
                            if (o) {
                                if (data.status === 'completed' || data.status === 'cancelled') {
                                    this.activeOrders = this.activeOrders.filter(x => x.id !== data.id);
                                } else {
                                    o.status = data.status;
                                }
                            }
                        });
                } catch(e) {
                    console.warn('WebSocket error:', e);
                }
            },

            async updateStatus(order, newStatus) {
                try {
                    const res = await fetch(`/cashier/orders/${order.id}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({ status: newStatus }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (newStatus === 'completed') {
                            this.activeOrders = this.activeOrders.filter(o => o.id !== order.id);
                        } else {
                            order.status = newStatus;
                        }
                    }
                } catch(e) {
                    console.error(e);
                }
            },

            async loadHistory() {
                try {
                    const res = await fetch('/cashier/orders/history');
                    const data = await res.json();
                    this.historyOrders = data;
                } catch(e) { console.error(e); }
            },

            showNotification(order) {
                this.playDing(); // Putar suara notifikasi

                if (Notification.permission === 'granted') {
                    new Notification('🔔 Order Baru — ZCoffee', {
                        body: `${order.table_number ? 'Meja ' + order.table_number : 'Take Away'} · ${order.items.length} item · Rp ${this.formatNum(order.total_price)}`,
                    });
                } else if (Notification.permission !== 'denied') {
                    Notification.requestPermission();
                }

                const toast = document.getElementById('toast-notif');
                document.getElementById('toast-title').textContent = `🔔 Order Baru — ${order.table_number ? 'Meja ' + order.table_number : 'Take Away'}`;
                document.getElementById('toast-body').textContent = `${order.items.length} item · Rp ${this.formatNum(order.total_price)} · ${order.payment_method.toUpperCase()}`;
                toast.classList.remove('translate-x-full');
                setTimeout(() => toast.classList.add('translate-x-full'), 4000);
            },

            printReceipt(orderId) {
                // Membuka struk di iframe tersembunyi agar tidak mengganggu kasir
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = `/cashier/orders/${orderId}/receipt`;
                document.body.appendChild(iframe);
                
                // Hapus iframe setelah beberapa detik agar tidak menumpuk di DOM
                setTimeout(() => {
                    if (document.body.contains(iframe)) {
                        document.body.removeChild(iframe);
                    }
                }, 10000);
            },

            playDing() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gainNode = ctx.createGain();
                    
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(1046.50, ctx.currentTime); // C6 note (bunyi ting/bell)
                    
                    gainNode.gain.setValueAtTime(0.5, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5); // Fade out dalam 0.5 detik
                    
                    osc.connect(gainNode);
                    gainNode.connect(ctx.destination);
                    
                    osc.start();
                    osc.stop(ctx.currentTime + 0.5);
                } catch (e) {
                    console.warn('Audio API tidak didukung browser ini', e);
                }
            },
        };
    }
    </script>
    @endpush
</x-cashier-layout>