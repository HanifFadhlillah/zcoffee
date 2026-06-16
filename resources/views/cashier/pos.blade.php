<x-cashier-layout>
    <x-slot:title>ZCoffee — POS Kasir</x-slot:title>

    @push('styles')
    <style>
        :root {
            --c-black:   #1A1A1A;
            --c-roast:   #3D2000;
            --c-bean:    #8B5A00;
            --c-golden:  #C47D1A;
            --c-caramel: #D9A84E;
            --c-cream:   #F5ECD7;
            --c-white:   #FFFFFF;
        }

        .menu-card { transition: transform 0.15s, box-shadow 0.15s; }
        .menu-card:active { transform: scale(0.97); }
        .modal-sheet { transition: transform 0.3s cubic-bezier(0.32,0.72,0,1); }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @endpush

    <div x-data="posApp()" x-init="init()" class="flex h-full w-full bg-stone-100 relative">
        
        {{-- ── KIRI: KATEGORI & MENU GRID ── --}}
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            
            {{-- Header Kategori --}}
            <div class="bg-white border-b border-stone-200 p-4 sticky top-0 z-10 shadow-sm">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold text-stone-800">Menu ZCoffee</h2>
                </div>
                
                <div class="flex overflow-x-auto scrollbar-hide gap-2 mt-4">
                    <button @click="activeCategory = 'all'"
                        :class="activeCategory === 'all' ? 'bg-stone-800 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'"
                        class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">Semua</button>
                    @foreach($categories as $key => $label)
                    <button @click="activeCategory = '{{ $key }}'"
                        :class="activeCategory === '{{ $key }}' ? 'bg-stone-800 text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200'"
                        class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-semibold transition-colors">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Grid Menu --}}
            <div class="flex-1 overflow-y-auto p-4 pb-10">
                @foreach($categories as $key => $label)
                    @if(isset($menus[$key]) && $menus[$key]->count() > 0)
                    <div x-show="activeCategory === 'all' || activeCategory === '{{ $key }}'" x-transition>
                        <p class="text-sm font-bold tracking-widest uppercase mb-3 mt-4 text-stone-500">{{ $label }}</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
                            @foreach($menus[$key] as $menu)
                            @php
                                $menuData = array_merge($menu->toArray(), ['image_url' => $menu->image_url]);
                                $isHot = in_array(strtolower($menu->name), ['espresso', 'v60', 'vietnam drip', 'tubruk', 'french press']);
                            @endphp
                            <div class="menu-card bg-white rounded-2xl overflow-hidden cursor-pointer shadow-sm border border-stone-200 hover:border-amber-400"
                                 @click="openModal({{ json_encode($menuData) }})">
                                <div class="relative overflow-hidden bg-stone-100" style="aspect-ratio: 4/3;">
                                    @if($menu->image && file_exists(public_path('storage/'.$menu->image)))
                                        <img src="{{ $menu->image_url }}" class="absolute inset-0 w-full h-full object-cover object-center" alt="{{ $menu->name }}">
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center text-4xl">
                                            {{ match($key) { 'espresso'=>'☕', 'manual'=>'🫙', 'maincourse'=>'🍽️', 'snack'=>'🍿', default=>'🥤' } }}
                                        </div>
                                    @endif
                                    @if($isHot)
                                        <span class="absolute top-2 left-2 text-white text-[10px] font-bold px-2 py-0.5 rounded-full z-10 bg-red-600">HOT</span>
                                    @endif
                                </div>
                                <div class="p-3">
                                    <p class="text-sm font-bold text-stone-800 leading-tight mb-1 truncate">{{ $menu->name }}</p>
                                    <p class="text-amber-600 font-bold text-sm">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ── KANAN: CART & CHECKOUT ── --}}
        <div class="w-96 bg-white border-l border-stone-200 flex flex-col h-full shadow-lg relative z-20">
            <div class="p-4 border-b border-stone-200 bg-stone-50 flex items-center justify-between">
                <h3 class="font-bold text-stone-800 text-lg">Keranjang (Take Away)</h3>
                <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-1 rounded-lg" x-text="cartCount + ' item'"></span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 bg-white">
                <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-stone-400">
                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <p class="font-medium">Belum ada pesanan</p>
                </div>
                
                <template x-for="(item, i) in cart" :key="i">
                    <div class="mb-3 p-3 bg-stone-50 rounded-xl border border-stone-100 relative group">
                        <div class="flex justify-between items-start gap-2 pr-6">
                            <div>
                                <p class="text-sm font-bold text-stone-800" x-text="item.name"></p>
                                <p class="text-[11px] text-stone-500 mt-0.5" 
                                   x-show="item.hasSugar || item.hasIce"
                                   x-text="[item.hasSugar ? 'Sugar: ' + item.sugar : '', item.hasIce ? 'Ice: ' + item.ice : ''].filter(Boolean).join(' · ')"></p>
                                <p class="text-[11px] text-amber-600 mt-0.5 italic"
                                   x-show="item.notes && item.notes.trim() !== ''"
                                   x-text="'📝 ' + item.notes"></p>
                            </div>
                            <p class="text-sm font-bold text-amber-600" x-text="'Rp ' + formatNum(item.price)"></p>
                        </div>
                        
                        <div class="flex items-center justify-between mt-3">
                            <div class="flex items-center gap-2">
                                <button @click="updateQty(i, -1)" class="w-6 h-6 rounded bg-white border border-stone-300 text-stone-600 flex items-center justify-center font-bold hover:bg-stone-100">-</button>
                                <span class="text-sm font-bold w-4 text-center" x-text="item.qty"></span>
                                <button @click="updateQty(i, 1)" class="w-6 h-6 rounded bg-white border border-stone-300 text-stone-600 flex items-center justify-center font-bold hover:bg-stone-100">+</button>
                            </div>
                            <p class="font-bold text-stone-800" x-text="'Rp ' + formatNum(item.subtotal)"></p>
                        </div>
                        
                        <button @click="removeFromCart(i)" class="absolute top-2 right-2 text-stone-400 hover:text-red-500 transition-colors p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                
                <div x-show="cart.length > 0" class="mt-4">
                    <label class="block text-xs font-bold text-stone-500 mb-1">Catatan Pelanggan / Nama</label>
                    <input type="text" x-model="customerNote" class="w-full text-sm rounded-lg border-stone-200 focus:ring-amber-500 focus:border-amber-500" placeholder="Opsional...">
                </div>
            </div>

            <div class="p-4 bg-stone-50 border-t border-stone-200">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-stone-500 font-medium">Total Harga</span>
                    <span class="text-2xl font-bold text-stone-900" x-text="'Rp ' + formatNum(cartTotal)"></span>
                </div>
                
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <button @click="payment = 'cash'"
                        :class="payment === 'cash' ? 'bg-amber-100 border-amber-500 text-amber-800' : 'bg-white border-stone-200 text-stone-600'"
                        class="py-2 border-2 rounded-xl text-sm font-bold transition-colors">
                        💵 Cash
                    </button>
                    <button @click="payment = 'qris'"
                        :class="payment === 'qris' ? 'bg-amber-100 border-amber-500 text-amber-800' : 'bg-white border-stone-200 text-stone-600'"
                        class="py-2 border-2 rounded-xl text-sm font-bold transition-colors">
                        📱 QRIS
                    </button>
                </div>

                <button @click="submitOrder()" :disabled="submitting || cart.length === 0"
                    class="w-full py-4 rounded-xl font-bold text-white flex items-center justify-center gap-2 transition-colors shadow-md"
                    :class="(submitting || cart.length === 0) ? 'bg-stone-400 cursor-not-allowed' : 'bg-stone-900 hover:bg-stone-800'">
                    <span x-show="submitting" class="w-5 h-5 border-2 border-t-transparent rounded-full animate-spin border-white/50"></span>
                    <span x-text="submitting ? 'Menyimpan...' : 'Bayar & Buat Pesanan'"></span>
                </button>
            </div>
        </div>

        {{-- ── MODAL: OPSI MENU (Kompak, tanpa gambar besar) ── --}}
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60" style="display:none;">
            <div @click.outside="modalOpen = false"
                 class="bg-white rounded-2xl w-full shadow-2xl overflow-hidden"
                 style="max-width: 420px; max-height: 80vh; display: flex; flex-direction: column;">

                {{-- Header: nama menu + harga + tombol tutup (tidak scroll) --}}
                <div class="flex items-center gap-3 p-4 border-b border-stone-100 flex-shrink-0">
                    {{-- Thumbnail kecil --}}
                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-stone-100 flex-shrink-0 flex items-center justify-center text-2xl">
                        <template x-if="selectedItem && selectedItem.image_url && !selectedItem.image_url.includes('menu-placeholder')">
                            <img :src="selectedItem.image_url" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!selectedItem || !selectedItem.image_url || selectedItem.image_url.includes('menu-placeholder')">
                            <span x-text="
                                selectedItem?.category === 'espresso' ? '☕' :
                                selectedItem?.category === 'manual' ? '🫙' :
                                selectedItem?.category === 'maincourse' ? '🍽️' :
                                selectedItem?.category === 'snack' ? '🍿' : '🥤'
                            "></span>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-stone-900 truncate" x-text="selectedItem?.name"></p>
                        <p class="text-sm font-bold text-amber-600" x-text="selectedItem ? 'Rp ' + formatNum(selectedItem.price) : ''"></p>
                    </div>
                    <button @click="modalOpen = false" class="w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 flex items-center justify-center text-stone-500 font-bold flex-shrink-0">✕</button>
                </div>

                {{-- Body: opsi (bisa scroll jika konten melebihi layar) --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-4">

                    {{-- Hot / Ice (Khusus Cappuccino) --}}
                    <div x-show="selectedItem && slugName(selectedItem.name) === 'capuccino'">
                        <p class="text-xs font-bold text-stone-500 uppercase tracking-wide mb-2">Sajikan sebagai</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="modalServeType = 'hot'; modalIce = 'normal'"
                                :class="modalServeType === 'hot' ? 'bg-red-50 border-red-400 text-red-700' : 'bg-white border-stone-200 text-stone-600'"
                                class="py-2.5 border-2 rounded-xl text-sm font-bold transition-all">🔥 Hot</button>
                            <button @click="modalServeType = 'ice'"
                                :class="modalServeType === 'ice' ? 'bg-blue-50 border-blue-400 text-blue-700' : 'bg-white border-stone-200 text-stone-600'"
                                class="py-2.5 border-2 rounded-xl text-sm font-bold transition-all">🧊 Ice</button>
                        </div>
                    </div>

                    {{-- Sugar --}}
                    <div x-show="selectedItem && showSugar(selectedItem.name)">
                        <p class="text-xs font-bold text-stone-500 uppercase tracking-wide mb-2">Gula</p>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="opt in [{v:'less',l:'Less Sweet'},{v:'normal',l:'Normal'}]">
                                <button @click="modalSugar = opt.v"
                                    :class="modalSugar === opt.v ? 'bg-amber-50 border-amber-400 text-amber-800' : 'bg-white border-stone-200 text-stone-600'"
                                    class="py-2.5 border-2 rounded-xl text-sm font-bold transition-all"
                                    x-text="opt.l"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Ice Level --}}
                    <div x-show="selectedItem && showIce(selectedItem.name, modalServeType)">
                        <p class="text-xs font-bold text-stone-500 uppercase tracking-wide mb-2">Es</p>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="opt in [{v:'no_ice',l:'No Ice'},{v:'less',l:'Less'},{v:'normal',l:'Normal'}]">
                                <button @click="modalIce = opt.v"
                                    :class="modalIce === opt.v ? 'bg-amber-50 border-amber-400 text-amber-800' : 'bg-white border-stone-200 text-stone-600'"
                                    class="py-2.5 border-2 rounded-xl text-sm font-bold transition-all"
                                    x-text="opt.l"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Catatan Khusus (Maincourse & Snack) --}}
                    <div x-show="selectedItem && (selectedItem.category === 'maincourse' || selectedItem.category === 'snack')">
                        <p class="text-xs font-bold text-stone-500 uppercase tracking-wide mb-2">Catatan / Keterangan</p>
                        <textarea x-model="modalNotes" rows="2"
                            placeholder="Contoh: pedas sedang, tanpa bawang, dll."
                            class="w-full text-sm rounded-xl border border-stone-200 px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-amber-400 transition-colors"></textarea>
                    </div>
                </div>

                {{-- Footer: tombol tambah (tidak scroll, selalu terlihat) --}}
                <div class="p-4 border-t border-stone-100 flex-shrink-0">
                    <button @click="addToCart()"
                        class="w-full bg-stone-900 hover:bg-stone-800 text-white py-3 rounded-xl font-bold shadow transition-colors">
                        + Tambahkan ke Pesanan
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div id="toast-notif" class="fixed top-4 right-4 z-50 max-w-sm transform translate-x-full transition-transform duration-300">
        <div class="bg-stone-900 text-white rounded-2xl p-4 shadow-2xl">
            <p class="text-amber-400 font-bold text-sm" id="toast-title">Order Baru!</p>
            <p class="text-white/70 text-xs mt-0.5" id="toast-body"></p>
        </div>
    </div>

    @push('scripts')
    <script>
    function posApp() {
        return {
            activeCategory: 'all',
            cart: [],
            modalOpen: false,
            selectedItem: null,
            modalQty: 1,
            modalSugar: 'normal',
            modalIce: 'normal',
            modalServeType: 'ice',
            modalNotes: '',
            payment: 'cash',
            submitting: false,
            customerNote: '',

            get cartCount() { return this.cart.reduce((s, i) => s + i.qty, 0); },
            get cartTotal() { return this.cart.reduce((s, i) => s + i.subtotal, 0); },

            formatNum(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },
            slugName(name) { return (name || '').toLowerCase().trim(); },

            init() {
                this.listenWebSocket();
            },

            listenWebSocket() {
                if (typeof window.Echo === 'undefined') {
                    console.warn('Echo not loaded');
                    return;
                }
                try {
                    window.Echo.channel('orders')
                        .listen('.new-order', (data) => {
                            this.showNotification(data);
                        });
                } catch(e) {
                    console.warn('WebSocket error:', e);
                }
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

            playDing() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gainNode = ctx.createGain();
                    
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(1046.50, ctx.currentTime); // C6 note
                    
                    gainNode.gain.setValueAtTime(0.5, ctx.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                    
                    osc.connect(gainNode);
                    gainNode.connect(ctx.destination);
                    
                    osc.start();
                    osc.stop(ctx.currentTime + 0.5);
                } catch (e) {
                    console.warn('Audio API tidak didukung browser ini', e);
                }
            },

            openModal(menu) {
                this.selectedItem = menu;
                this.modalQty = 1;
                this.modalSugar = 'normal';
                this.modalIce = 'normal';
                this.modalServeType = 'ice';
                this.modalNotes = '';
                this.modalOpen = true;
            },

            showSugar(name) {
                const item = this.selectedItem;
                // Kategori maincourse dan snack tidak punya sugar level
                if (item && (item.category === 'maincourse' || item.category === 'snack')) return false;
                const n = this.slugName(name);
                const noSugar = ['espresso', 'americano', 'capuccino', 'french press', 'v60', 'vietnam drip', 'tubruk', 'japanese', 'chocolate', 'red velvet', 'taro', 'matcha', 'lemon tea', 'orange yakults', 'mango yakults', 'blue sparkling'];
                return !noSugar.includes(n);
            },

            showIce(name, serveType) {
                const item = this.selectedItem;
                // Kategori maincourse dan snack tidak punya ice level
                if (item && (item.category === 'maincourse' || item.category === 'snack')) return false;
                const n = this.slugName(name);
                const noIce = ['espresso', 'french press', 'v60', 'vietnam drip', 'tubruk', 'japanese'];
                if (noIce.includes(n)) return false;
                if (n === 'capuccino') return serveType === 'ice';
                return true;
            },

            addToCart() {
                const menu = this.selectedItem;
                const qty = this.modalQty;
                const sugar = this.modalSugar;
                const ice = this.modalIce;

                const hasSugar  = this.showSugar(menu.name);
                const hasIce    = this.showIce(menu.name, this.modalServeType);
                const isCapucc  = this.slugName(menu.name) === 'capuccino';
                const serveType = isCapucc ? this.modalServeType : null; 
                const hasServe  = isCapucc || (!['espresso','french press','v60','vietnam drip','tubruk','japanese'].includes(this.slugName(menu.name)));
                const notes     = this.modalNotes.trim();
                
                const key = `${menu.id}-${hasSugar ? sugar : 'none'}-${hasIce ? ice : 'none'}-${serveType ?? 'none'}-${notes}`;
                const ex = this.cart.find(c => c.key === key);
                
                if (ex) { 
                    ex.qty += qty; 
                    ex.subtotal = ex.qty * menu.price; 
                } else {
                    this.cart.push({
                        key, name: menu.name, menuId: menu.id,
                        qty, sugar: hasSugar ? sugar : '-', ice: hasIce ? ice : '-',
                        hasSugar, hasIce, hasServe, serveType,
                        notes,
                        subtotal: menu.price * qty, price: menu.price
                    });
                }
                this.modalOpen = false;
            },

            updateQty(index, delta) {
                const item = this.cart[index];
                item.qty += delta;
                if (item.qty <= 0) {
                    this.removeFromCart(index);
                } else {
                    item.subtotal = item.qty * item.price;
                }
            },

            removeFromCart(i) { this.cart.splice(i, 1); },

            async submitOrder() {
                if (this.cart.length === 0) return;
                this.submitting = true;
                try {
                    const headers = {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    };
                    if (typeof window.Echo !== 'undefined' && window.Echo.socketId()) {
                        headers['X-Socket-ID'] = window.Echo.socketId();
                    }
                    
                    const res = await fetch('{{ route('cashier.pos.store') }}', {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({
                            payment_method: this.payment,
                            customer_note: this.customerNote,
                            items: this.cart.map(c => ({
                                menu_id:     c.menuId,
                                quantity:    c.qty,
                                sugar_level: c.sugar === '-' ? 'normal' : c.sugar,
                                ice_level:   c.ice   === '-' ? 'normal' : c.ice,
                                serve_type:  c.serveType ?? null,
                                notes:       c.notes || null,
                            })),
                        }),
                    });
                    const data = await res.json();
                    if (data.success && data.receipt_url) {
                        this.playDing();
                        // Buka struk di tab baru agar tab POS ini tidak hilang
                        window.open(data.receipt_url, '_blank');
                        // Reset/Refresh halaman POS agar bersih untuk pelanggan berikutnya
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        alert(data.message || 'Gagal menyimpan pesanan.');
                        this.submitting = false;
                    }
                } catch (e) {
                    alert('Terjadi kesalahan jaringan.');
                    this.submitting = false;
                }
            }
        }
    }
    </script>
    @endpush
</x-cashier-layout>
