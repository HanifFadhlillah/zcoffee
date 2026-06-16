<x-app-layout>
    <x-slot:title>ZCoffee — Meja {{ $table }}</x-slot:title>

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
        .cart-fab { transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1); }
        .modal-sheet { transition: transform 0.3s cubic-bezier(0.32,0.72,0,1); }
        html { scroll-behavior: smooth; }

        /* scrollbar hide */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @endpush

    {{-- Wrapper: di desktop terlihat seperti tampilan HP terpusat --}}
    <div class="min-h-screen" style="background: #2a1a0a;">
    <div x-data="orderApp({{ $table }})" class="min-h-screen mx-auto" style="max-width: 480px; background: var(--c-cream);">

        {{-- ── HEADER ── --}}
        <div class="sticky top-0 z-30" style="background: var(--c-black);">
            <div class="px-4 pt-5 pb-3 flex flex-col items-center text-center">

                {{-- ══ LOGO BISNIS ══--}}
                @php $logoPath = public_path('images/logo.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Logo ZCoffee"
                         class="object-contain mb-2"
                         style="max-height: 64px; max-width: 220px; width: auto;">
                @else
                    <div class="font-serif text-2xl font-bold mb-2" style="color: var(--c-white);">ZCoffee</div>
                @endif

                {{-- Badge nomor meja --}}
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border"
                     style="background: rgba(196,125,26,0.15); color: var(--c-caramel); border-color: rgba(196,125,26,0.3);">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h8a2 2 0 012 2v1h1a1 1 0 010 2h-1v9a2 2 0 01-2 2H6a2 2 0 01-2-2V7H3a1 1 0 010-2h1V4z"/></svg>
                    Meja {{ $table }}
                </div>
            </div>

            {{-- Category Tabs --}}
            <div class="flex overflow-x-auto scrollbar-hide px-4 gap-1">
                <button @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'border-b-2' : 'border-b-2 border-transparent'"
                    :style="activeCategory === 'all' ? 'color: var(--c-golden); border-color: var(--c-golden);' : 'color: rgba(245,236,215,0.4);'"
                    class="flex-shrink-0 px-4 py-2.5 text-sm font-medium transition-colors hover:opacity-80">Semua</button>
                @foreach($categories as $key => $label)
                <button @click="activeCategory = '{{ $key }}'"
                    :class="activeCategory === '{{ $key }}' ? 'border-b-2' : 'border-b-2 border-transparent'"
                    :style="activeCategory === '{{ $key }}' ? 'color: var(--c-golden); border-color: var(--c-golden);' : 'color: rgba(245,236,215,0.4);'"
                    class="flex-shrink-0 px-4 py-2.5 text-sm font-medium transition-colors hover:opacity-80">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        {{-- ── MENU GRID ── --}}
        <div class="px-4 py-4 pb-32">
            @foreach($categories as $key => $label)
                @if(isset($menus[$key]) && $menus[$key]->count() > 0)
                <div x-show="activeCategory === 'all' || activeCategory === '{{ $key }}'" x-transition>
                    <p class="text-xs font-semibold tracking-widest uppercase mb-3 mt-2"
                       style="color: var(--c-bean);">{{ $label }}</p>
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        @foreach($menus[$key] as $menu)
                        @php
                            $menuData = array_merge($menu->toArray(), ['image_url' => $menu->image_url]);
                            $bgStyle  = match($key) {
                                'espresso'   => 'background: rgba(139,90,0,0.08);',
                                'manual'     => 'background: rgba(61,32,0,0.06);',
                                'maincourse' => 'background: rgba(220,38,38,0.06);',
                                'snack'      => 'background: rgba(234,179,8,0.07);',
                                default      => 'background: rgba(196,125,26,0.08);',
                            };
                            $hotMenus = ['espresso', 'v60', 'vietnam drip', 'tubruk', 'french press'];
                            $isHot    = in_array(strtolower($menu->name), $hotMenus);
                        @endphp
                        <div class="menu-card rounded-2xl overflow-hidden cursor-pointer shadow-sm"
                             style="background: var(--c-white); border: 1px solid rgba(139,90,0,0.12);"
                             @click="openModal({{ json_encode($menuData) }})">
                            {{-- Image --}}
                            <div class="relative overflow-hidden" style="aspect-ratio: 4 / 3; {{ $bgStyle }}">
                                @if($menu->image && file_exists(public_path('storage/'.$menu->image)))
                                    <img src="{{ $menu->image_url }}"
                                         class="absolute inset-0 w-full h-full object-cover object-center"
                                         alt="{{ $menu->name }}">
                                @else
                                    <div class="absolute inset-0 w-full h-full flex items-center justify-center">
                                        <span class="text-5xl">{{ match($key) {
                                            'espresso'   => '☕',
                                            'manual'     => '🫙',
                                            'maincourse' => '🍽️',
                                            'snack'      => '🍿',
                                            default      => '🥤',
                                        } }}</span>
                                    </div>
                                @endif
                                {{-- Badge HOT --}}
                                @if($isHot)
                                    <span class="absolute top-2 left-2 text-white text-[10px] font-bold px-2 py-0.5 rounded-full z-10"
                                          style="background: #e53e3e; letter-spacing: 0.05em;">HOT</span>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="text-sm font-semibold leading-tight mb-1.5" style="color: var(--c-black);">{{ $menu->name }}</p>
                                @if($menu->description)
                                    <p class="text-xs leading-tight mb-1.5 line-clamp-1" style="color: rgba(26,26,26,0.45);">{{ $menu->description }}</p>
                                @endif
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                      style="color: var(--c-roast); background: rgba(196,125,26,0.12);">
                                    Rp {{ number_format($menu->price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        </div>

        {{-- ── CART FAB ── --}}
        <div x-show="cartCount > 0" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="fixed bottom-5 z-40" style="left: 50%; transform: translateX(-50%); width: min(calc(480px - 2rem), calc(100vw - 2rem));">
            <button @click="showCheckout = true"
                class="cart-fab w-full rounded-2xl px-5 py-4 flex items-center justify-between shadow-2xl"
                style="background: var(--c-black); color: var(--c-white);"
                onmouseover="this.style.background='var(--c-roast)';"
                onmouseout="this.style.background='var(--c-black)';">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                         style="background: var(--c-golden); color: var(--c-black);" x-text="cartCount"></div>
                    <span class="font-semibold text-sm">Lihat Pesanan</span>
                </div>
                <span class="font-bold text-sm" style="color: var(--c-caramel);" x-text="'Rp ' + formatNum(cartTotal)"></span>
            </button>
        </div>

        {{-- ── MODAL: ITEM DETAIL ── --}}
        <div x-show="modalOpen" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" @click.self="modalOpen = false"
             class="fixed inset-0 z-50 flex items-end justify-center" style="background: rgba(26,26,26,0.6); display:none;">
            <div class="modal-sheet w-full max-w-sm rounded-t-3xl overflow-hidden max-h-[90vh] overflow-y-auto"
                 style="background: var(--c-white);"
                 x-transition:enter="transition duration-300" x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0">
                <div class="w-10 h-1 rounded mx-auto mt-3 mb-0" style="background: rgba(139,90,0,0.2);"></div>

                {{-- Item image --}}
                <div class="h-48 flex items-center justify-center relative overflow-hidden"
                     style="background: var(--c-cream);">
                    <template x-if="selectedItem && selectedItem.image_url && !selectedItem.image_url.includes('menu-placeholder')">
                        <img :src="selectedItem.image_url" :alt="selectedItem.name" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!selectedItem || !selectedItem.image_url || selectedItem.image_url.includes('menu-placeholder')">
                        <span class="text-7xl" x-text="
                            selectedItem?.category === 'espresso' ? '☕' :
                            selectedItem?.category === 'manual' ? '🫙' : '🥤'
                        "></span>
                    </template>
                </div>

                <div class="p-5">
                    <h2 class="font-serif text-xl mb-1" style="color: var(--c-black);" x-text="selectedItem?.name"></h2>
                    <p class="text-xs mb-1" style="color: rgba(26,26,26,0.45);" x-text="selectedItem?.description"></p>
                    <p class="text-lg font-bold mb-5" style="color: var(--c-bean);" x-text="selectedItem ? 'Rp ' + formatNum(selectedItem.price) : ''"></p>

                    {{-- ── CAPPUCCINO: Pilih Hot / Ice dulu ── --}}
{{-- SEBELUMNYA pakai x-if → ganti x-show agar reactive --}}
<div class="mb-4" 
     x-show="selectedItem && slugName(selectedItem.name) === 'capuccino'" 
     x-transition>
    <p class="text-[11px] font-semibold tracking-widest uppercase mb-2" 
       style="color: rgba(26,26,26,0.4);">Sajikan sebagai</p>
    <div class="flex gap-2">
        <button @click="modalServeType = 'hot'; modalIce = 'normal'"
            :style="modalServeType === 'hot'
                ? 'background: #e53e3e; color: #fff; border-color: #e53e3e;'
                : 'background: var(--c-white); color: var(--c-black); border-color: rgba(139,90,0,0.25);'"
            class="flex-1 py-2 rounded-xl border-2 text-xs font-semibold transition-all">Hot</button>
        <button @click="modalServeType = 'ice'"
            :style="modalServeType === 'ice'
                ? 'background: var(--c-black); color: var(--c-white); border-color: var(--c-black);'
                : 'background: var(--c-white); color: var(--c-black); border-color: rgba(139,90,0,0.25);'"
            class="flex-1 py-2 rounded-xl border-2 text-xs font-semibold transition-all">Ice</button>
    </div>
</div>

                    {{-- ── SUGAR LEVEL (less + normal saja) ── --}}
                    {{-- Pakai x-show agar reactive saat ganti menu --}}
                    <div class="mb-4" x-show="selectedItem && showSugar(selectedItem.name)" x-transition>
                        <p class="text-[11px] font-semibold tracking-widest uppercase mb-2" style="color: rgba(26,26,26,0.4);">Sugar Level</p>
                        <div class="flex gap-2">
                            <template x-for="opt in [{v:'less',l:'Less'},{v:'normal',l:'Normal'}]">
                                <button @click="modalSugar = opt.v"
                                    :style="modalSugar === opt.v
                                        ? 'background: var(--c-black); color: var(--c-white); border-color: var(--c-black);'
                                        : 'background: var(--c-white); color: var(--c-black); border-color: rgba(139,90,0,0.25);'"
                                    class="flex-1 py-2 rounded-xl border-2 text-xs font-semibold transition-all"
                                    x-text="opt.l"></button>
                            </template>
                        </div>
                    </div>

                    {{-- ── ICE LEVEL ── --}}
                    {{-- Pakai x-show (bukan x-if) agar reactive saat toggle Hot/Ice cappuccino --}}
                    <div class="mb-5" x-show="selectedItem && showIce(selectedItem.name, modalServeType)" x-transition>
                        <p class="text-[11px] font-semibold tracking-widest uppercase mb-2" style="color: rgba(26,26,26,0.4);">Ice Level</p>
                        <div class="flex gap-2">
                            <template x-for="opt in [{v:'no_ice',l:'No Ice'},{v:'less',l:'Less'},{v:'normal',l:'Normal'}]">
                                <button @click="modalIce = opt.v"
                                    :style="modalIce === opt.v
                                        ? 'background: var(--c-black); color: var(--c-white); border-color: var(--c-black);'
                                        : 'background: var(--c-white); color: var(--c-black); border-color: rgba(139,90,0,0.25);'"
                                    class="flex-1 py-2 rounded-xl border-2 text-xs font-semibold transition-all"
                                    x-text="opt.l"></button>
                            </template>
                        </div>
                    </div>

                    {{-- ── CATATAN KHUSUS (Maincourse & Snack) ── --}}
                    <div class="mb-4" x-show="selectedItem && (selectedItem.category === 'maincourse' || selectedItem.category === 'snack')" x-transition>
                        <p class="text-[11px] font-semibold tracking-widest uppercase mb-2" style="color: rgba(26,26,26,0.4);">Catatan / Keterangan</p>
                        <textarea x-model="modalNotes" rows="2"
                            placeholder="Contoh: pedas sedang, tanpa bawang, dll."
                            class="w-full text-sm rounded-xl border-2 px-3 py-2 resize-none focus:outline-none transition-colors"
                            style="border-color: rgba(139,90,0,0.25); background: var(--c-white); color: var(--c-black);"
                            onfocus="this.style.borderColor='var(--c-bean)';"
                            onblur="this.style.borderColor='rgba(139,90,0,0.25)';"></textarea>
                    </div>

                    {{-- Quantity --}}
                    <div class="flex items-center gap-4 mb-5">
                        <button @click="modalQty = Math.max(1, modalQty - 1)"
                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-xl font-light transition-colors"
                            style="border-color: rgba(139,90,0,0.25); color: var(--c-black);">−</button>
                        <span class="text-xl font-bold w-8 text-center" style="color: var(--c-black);" x-text="modalQty"></span>
                        <button @click="modalQty++"
                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-xl font-light transition-colors"
                            style="border-color: rgba(139,90,0,0.25); color: var(--c-black);">+</button>
                        <span class="text-sm ml-2" style="color: rgba(26,26,26,0.4);"
                              x-text="selectedItem ? '= Rp ' + formatNum(selectedItem.price * modalQty) : ''"></span>
                    </div>

                    <button @click="addToCart()"
                        class="w-full py-4 rounded-2xl font-semibold text-sm transition-colors"
                        style="background: var(--c-black); color: var(--c-white);"
                        onmouseover="this.style.background='var(--c-roast)';"
                        onmouseout="this.style.background='var(--c-black)';">
                        Tambah ke Pesanan
                    </button>
                </div>
            </div>
        </div>

        {{-- ── CHECKOUT SHEET ── --}}
        <div x-show="showCheckout" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100" @click.self="showCheckout = false"
             class="fixed inset-0 z-50 flex items-end justify-center" style="background: rgba(26,26,26,0.6); display:none;">
            <div class="modal-sheet w-full max-w-sm rounded-t-3xl max-h-[92vh] flex flex-col"
                 style="background: var(--c-white);"
                 x-transition:enter="transition duration-300" x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0">
                <div class="w-10 h-1 rounded mx-auto mt-3" style="background: rgba(139,90,0,0.2);"></div>
                <div class="px-5 py-4 flex items-center justify-between" style="border-bottom: 1px solid rgba(139,90,0,0.1);">
                    <h2 class="font-semibold" style="color: var(--c-black);">Ringkasan Pesanan</h2>
                    <button @click="showCheckout = false"
                        class="w-7 h-7 rounded-full flex items-center justify-center text-lg transition-colors"
                        style="background: var(--c-cream); color: rgba(26,26,26,0.5);">×</button>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-3">
                    {{-- Cart Items --}}
                    <template x-for="(item, i) in cart" :key="i">
                        <div class="flex items-center gap-3 py-3" style="border-bottom: 1px solid rgba(139,90,0,0.07);">
                            <div class="flex-1">
                                <p class="text-sm font-semibold" style="color: var(--c-black);" x-text="item.name + ' × ' + item.qty"></p>
                                <p class="text-xs" style="color: rgba(26,26,26,0.4);"
                                   x-text="[item.hasSugar ? 'Sugar: ' + item.sugar : '', item.hasIce ? 'Ice: ' + item.ice : ''].filter(Boolean).join(' · ')"></p>
                            </div>
                            <div class="text-sm font-bold" style="color: var(--c-bean);" x-text="'Rp ' + formatNum(item.subtotal)"></div>
                            <button @click="removeFromCart(i)"
                                class="w-6 h-6 text-lg leading-none transition-colors"
                                style="color: rgba(26,26,26,0.25);"
                                onmouseover="this.style.color='#c0392b';"
                                onmouseout="this.style.color='rgba(26,26,26,0.25)';">×</button>
                        </div>
                    </template>

                    {{-- Payment Method --}}
                    <div class="mt-4 mb-2">
                        <p class="text-[11px] font-semibold tracking-widest uppercase mb-2" style="color: rgba(26,26,26,0.4);">Metode Pembayaran</p>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="payment = 'qris'"
                                :style="payment === 'qris'
                                    ? 'border-color: var(--c-bean); background: var(--c-cream);'
                                    : 'border-color: rgba(139,90,0,0.2); background: var(--c-white);'"
                                class="border-2 rounded-2xl p-3 text-center transition-all">
                                <div class="text-2xl mb-1">📱</div>
                                <div class="text-xs font-semibold" style="color: var(--c-black);">QRIS</div>
                            </button>
                            <button @click="payment = 'cash'"
                                :style="payment === 'cash'
                                    ? 'border-color: var(--c-bean); background: var(--c-cream);'
                                    : 'border-color: rgba(139,90,0,0.2); background: var(--c-white);'"
                                class="border-2 rounded-2xl p-3 text-center transition-all">
                                <div class="text-2xl mb-1">💵</div>
                                <div class="text-xs font-semibold" style="color: var(--c-black);">Cash</div>
                            </button>
                        </div>
                    </div>

                    {{-- Total --}}
                    <div class="mt-4 pt-3" style="border-top: 1px solid rgba(139,90,0,0.1);">
                        <div class="flex justify-between text-sm mb-1" style="color: rgba(26,26,26,0.5);">
                            <span>Subtotal</span>
                            <span x-text="'Rp ' + formatNum(cartTotal)"></span>
                        </div>
                        <div class="flex justify-between font-bold" style="color: var(--c-black);">
                            <span>Total</span>
                            <span x-text="'Rp ' + formatNum(cartTotal)"></span>
                        </div>
                    </div>
                </div>

                <div class="px-5 pb-6 pt-3" style="border-top: 1px solid rgba(139,90,0,0.1);">
                    <button @click="submitOrder()" :disabled="submitting"
                        class="w-full py-4 rounded-2xl font-semibold text-sm transition-colors flex items-center justify-center gap-2"
                        :style="submitting
                            ? 'background: rgba(139,90,0,0.4); color: var(--c-white); cursor: not-allowed;'
                            : 'background: var(--c-black); color: var(--c-white);'"
                        onmouseover="if(!this.disabled) this.style.background='var(--c-roast)';"
                        onmouseout="if(!this.disabled) this.style.background='var(--c-black)';">
                        <span x-show="submitting" class="w-4 h-4 border-2 border-t-transparent rounded-full animate-spin"
                              style="border-color: rgba(245,236,215,0.4); border-top-color: var(--c-cream);"></span>
                        <span x-text="submitting ? 'Mengirim...' : 'Pesan Sekarang 🚀'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── SUCCESS OVERLAY ── --}}
        <div x-show="orderSuccess" 
             x-transition:enter="transition-all transform duration-300 ease-out" 
             x-transition:enter-start="opacity-0 translate-x-16"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition-all transform duration-300 ease-in-out"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-16"
             class="fixed inset-0 z-50 overflow-y-auto" style="background: var(--c-cream); display:none;">
            <div class="min-h-full flex flex-col items-center justify-start px-6 py-8 text-center">

                {{-- ══ PHASE 1: QRIS — Bayar via QR Statis ══ --}}
                <div x-show="payment === 'qris' && !paymentPaid && !paymentTimeout" x-transition>
                    <div class="text-5xl mb-3" style="animation: pulse 2s infinite;">⏳</div>
                    <h2 class="font-serif text-xl font-bold mb-1" style="color: var(--c-black);">Menunggu Pembayaran</h2>
                    <p class="text-sm mb-1" style="color: rgba(26,26,26,0.5);">
                        Order <span class="font-bold" style="color: var(--c-bean);" x-text="orderNumber"></span>
                    </p>
                    <p class="text-xs mb-5" style="color: rgba(26,26,26,0.4);">Silakan scan QR Code di bawah ini untuk membayar</p>

                    {{-- Kartu pembayaran QR Statis --}}
                    <div class="rounded-2xl p-5 mb-5 w-full max-w-xs mx-auto"
                         style="background: var(--c-white); border: 1px solid rgba(139,90,0,0.15);">
                        <p class="text-xs mb-1" style="color: rgba(26,26,26,0.4);">Total Tagihan</p>
                        <p class="font-bold text-2xl mb-4" style="color: var(--c-black);" x-text="'Rp ' + formatNum(qrisTotal)"></p>

                        <div class="bg-white p-2 rounded-xl border border-stone-100 shadow-sm mx-auto w-48 h-auto overflow-hidden flex items-center justify-center">
                            <img src="{{ asset('images/qris_zcoffeex.jpeg') }}" class="w-full h-auto object-contain" alt="QRIS ZCoffee">
                        </div>

                        <p class="text-xs mt-4 text-stone-500">Gunakan aplikasi m-banking atau e-wallet (GoPay, OVO, DANA, ShopeePay)</p>
                        <p class="text-xs mt-1 text-amber-600 font-semibold">Tunggu di halaman ini setelah bayar, kasir akan mengkonfirmasi otomatis.</p>
                    </div>

                    {{-- Countdown Timer --}}
                    <div class="rounded-2xl px-6 py-4 mb-5 w-full max-w-xs mx-auto"
                         style="background: var(--c-white); border: 1px solid rgba(139,90,0,0.1);">
                        <p class="text-xs font-semibold tracking-widest uppercase mb-2" style="color: rgba(26,26,26,0.4);">Batas Waktu Pembayaran</p>
                        <div class="text-3xl font-bold font-mono"
                             :style="qrisCountdown < 60 ? 'color: #e53e3e;' : 'color: var(--c-black);'"
                             x-text="formatCountdown(qrisCountdown)"></div>
                        {{-- Progress bar --}}
                        <div class="mt-3 rounded-full overflow-hidden" style="height: 4px; background: rgba(139,90,0,0.1);">
                            <div class="h-full rounded-full transition-all duration-1000"
                                 :style="'width: ' + (qrisCountdown / 600 * 100) + '%; background: ' + (qrisCountdown < 60 ? '#e53e3e' : 'var(--c-golden)') + ';'"></div>
                        </div>
                    </div>

                    {{-- Polling indicator --}}
                    <div class="flex items-center justify-center gap-2 text-xs" style="color: rgba(26,26,26,0.35);">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background: #48bb78;"></span>
                        <span>Mengecek pembayaran setiap 5 detik...</span>
                    </div>
                </div>

                {{-- ══ PHASE 2: Pembayaran Berhasil (QRIS) ══ --}}
                <div x-show="paymentPaid && payment === 'qris'" x-transition>
                    <div class="text-5xl mb-3 animate-bounce">✅</div>
                    <h2 class="font-serif text-xl font-bold mb-1" style="color: var(--c-black);">Pembayaran Berhasil!</h2>
                    <p class="text-sm mb-1" style="color: rgba(26,26,26,0.5);">
                        Order <span class="font-bold" style="color: var(--c-bean);" x-text="orderNumber"></span>
                    </p>
                    <p class="text-xs mb-6" style="color: rgba(26,26,26,0.4);">Terima kasih! Pesanan Anda sedang diproses.</p>

                    {{-- Riwayat Pesanan --}}
                    <div class="rounded-2xl w-full max-w-xs mx-auto mb-6 overflow-hidden"
                         style="background: var(--c-white); border: 1px solid rgba(139,90,0,0.12);">
                        <div class="px-4 py-3" style="border-bottom: 1px solid rgba(139,90,0,0.08); background: rgba(196,125,26,0.06);">
                            <p class="text-xs font-semibold tracking-widest uppercase" style="color: var(--c-bean);">Riwayat Pembelian</p>
                        </div>
                        <div class="px-4 py-2">
                            <template x-for="(item, idx) in orderItems" :key="idx">
                                <div class="py-2.5" style="border-bottom: 1px solid rgba(139,90,0,0.05);">
                                    <div class="flex justify-between items-start gap-2">
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-semibold" style="color: var(--c-black);" x-text="item.name + ' ×' + item.qty"></p>
                                            <p class="text-xs mt-0.5"
                                               style="color: rgba(26,26,26,0.45);"
                                               x-show="item.has_sugar || item.has_serve"
                                               x-text="[item.has_sugar ? item.sugar_label : '', item.has_serve ? item.serve_label : ''].filter(Boolean).join(' · ')"></p>
                                            <p class="text-xs mt-0.5 italic"
                                               style="color: var(--c-golden);"
                                               x-show="item.notes && item.notes.trim() !== ''"
                                               x-text="'📝 ' + item.notes"></p>
                                        </div>
                                        <p class="text-sm font-bold flex-shrink-0" style="color: var(--c-bean);" x-text="'Rp ' + formatNum(item.subtotal)"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="px-4 py-3 flex justify-between items-center" style="background: rgba(196,125,26,0.06); border-top: 1px solid rgba(139,90,0,0.08);">
                            <span class="text-sm font-bold" style="color: var(--c-black);">Total</span>
                            <span class="text-sm font-bold" style="color: var(--c-bean);" x-text="'Rp ' + formatNum(qrisTotal)"></span>
                        </div>
                    </div>

                    <button @click="resetOrder()"
                        class="w-full max-w-xs py-4 rounded-2xl font-semibold text-sm transition-colors"
                        style="background: var(--c-black); color: var(--c-white);"
                        onmouseover="this.style.background='var(--c-roast)';"
                        onmouseout="this.style.background='var(--c-black)';">
                        ⬅️ Kembali ke Menu
                    </button>
                </div>

                {{-- ══ PHASE 3: Pembayaran Cash ══ --}}
                <div x-show="payment === 'cash'" x-transition>
                    <div class="text-5xl mb-3 animate-bounce">🏃‍♂️</div>
                    <h2 class="font-serif text-xl font-bold mb-1" style="color: var(--c-black);">Silakan Bayar ke Kasir</h2>
                    <p class="text-sm mb-1" style="color: rgba(26,26,26,0.5);">
                        Order <span class="font-bold" style="color: var(--c-bean);" x-text="orderNumber"></span>
                    </p>
                    <p class="text-xs mb-6" style="color: rgba(26,26,26,0.4);">Pesanan Anda sudah masuk sistem. Silakan menuju kasir untuk melakukan pembayaran secara tunai.</p>

                    {{-- Riwayat Pesanan --}}
                    <div class="rounded-2xl w-full max-w-xs mx-auto mb-6 overflow-hidden"
                         style="background: var(--c-white); border: 1px solid rgba(139,90,0,0.12);">
                        <div class="px-4 py-3" style="border-bottom: 1px solid rgba(139,90,0,0.08); background: rgba(196,125,26,0.06);">
                            <p class="text-xs font-semibold tracking-widest uppercase" style="color: var(--c-bean);">Riwayat Pembelian</p>
                        </div>
                        <div class="px-4 py-2">
                            <template x-for="(item, idx) in orderItems" :key="idx">
                                <div class="py-2.5" style="border-bottom: 1px solid rgba(139,90,0,0.05);">
                                    <div class="flex justify-between items-start gap-2">
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-semibold" style="color: var(--c-black);" x-text="item.name + ' ×' + item.qty"></p>
                                            <p class="text-xs mt-0.5"
                                               style="color: rgba(26,26,26,0.45);"
                                               x-show="item.has_sugar || item.has_serve"
                                               x-text="[item.has_sugar ? item.sugar_label : '', item.has_serve ? item.serve_label : ''].filter(Boolean).join(' · ')"></p>
                                            <p class="text-xs mt-0.5 italic"
                                               style="color: var(--c-golden);"
                                               x-show="item.notes && item.notes.trim() !== ''"
                                               x-text="'📝 ' + item.notes"></p>
                                        </div>
                                        <p class="text-sm font-bold flex-shrink-0" style="color: var(--c-bean);" x-text="'Rp ' + formatNum(item.subtotal)"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="px-4 py-3 flex justify-between items-center" style="background: rgba(196,125,26,0.06); border-top: 1px solid rgba(139,90,0,0.08);">
                            <span class="text-sm font-bold" style="color: var(--c-black);">Total yang harus dibayar</span>
                            <span class="text-lg font-bold" style="color: #c47d1a;" x-text="'Rp ' + formatNum(qrisTotal)"></span>
                        </div>
                    </div>

                    <button @click="resetOrder()"
                        class="w-full max-w-xs py-4 rounded-2xl font-semibold text-sm transition-colors"
                        style="background: var(--c-black); color: var(--c-white);"
                        onmouseover="this.style.background='var(--c-roast)';"
                        onmouseout="this.style.background='var(--c-black)';">
                        ⬅️ Kembali ke Menu
                    </button>
                </div>

                {{-- ══ PHASE 3: Timeout ══ --}}
                <div x-show="paymentTimeout && !paymentPaid" x-transition>
                    <div class="text-5xl mb-3">⏰</div>
                    <h2 class="font-serif text-xl font-bold mb-1" style="color: var(--c-black);">Waktu Pembayaran Habis</h2>
                    <p class="text-sm mb-1" style="color: rgba(26,26,26,0.5);">
                        Order <span class="font-bold" style="color: var(--c-bean);" x-text="orderNumber"></span>
                    </p>
                    <p class="text-xs mb-6" style="color: rgba(26,26,26,0.4);">Pembayaran tidak terdeteksi dalam 10 menit.<br>Silakan konfirmasi ke kasir atau pesan ulang.</p>

                    <button @click="resetOrder()"
                        class="w-full max-w-xs py-4 rounded-2xl font-semibold text-sm transition-colors"
                        style="background: var(--c-black); color: var(--c-white);"
                        onmouseover="this.style.background='var(--c-roast)';"
                        onmouseout="this.style.background='var(--c-black)';">
                        ⬅️ Kembali ke Menu
                    </button>
                </div>

            </div>
        </div>

        {{-- ── TOAST ── --}}
        <div x-show="toast" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition duration-200" x-transition:leave-end="opacity-0"
             class="fixed top-4 left-4 right-4 z-[60] px-4 py-3 rounded-2xl shadow-xl text-sm font-medium"
             style="background: var(--c-roast); color: var(--c-cream); display:none;">
            <span x-text="toastMsg"></span>
        </div>
    </div>{{-- end max-width wrapper --}}
    </div>{{-- end dark outer wrapper --}}

    @push('scripts')
    {{-- Midtrans Snap.js — load sebelum Alpine --}}
    <script src="{{ config('midtrans.snap_url') }}"
            data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
    function orderApp(tableNumber) {
        return {
            tableNumber,
            activeCategory: 'all',
            cart: [],
            modalOpen: false,
            showCheckout: false,
            orderSuccess: false,
            selectedItem: null,
            modalQty: 1,
            modalSugar: 'normal',
            modalIce: 'normal',
            modalServeType: 'ice',
            modalNotes: '',
            payment: 'qris',
            submitting: false,
            orderNumber: '',
            toast: false,
            toastMsg: '',

            // ── Payment state ──
            paymentPaid: false,
            paymentTimeout: false,
            qrisCountdown: 600,   // 10 menit dalam detik
            qrisTotal: 0,         // total disimpan saat submit (cart akan di-clear setelah paid)
            orderItems: [],       // riwayat item untuk ditampilkan di nota
            _pollingId: null,
            _countdownId: null,

            // ── Midtrans Snap state ──
            snapToken: null,      // token dari Midtrans untuk membuka popup Snap
            snapLoading: false,   // loading saat request token

            get cartCount() { return this.cart.reduce((s, i) => s + i.qty, 0); },
            get cartTotal() { return this.cart.reduce((s, i) => s + i.subtotal, 0); },

            formatNum(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); },

            formatCountdown(sec) {
                const m = String(Math.floor(sec / 60)).padStart(2, '0');
                const s = String(sec % 60).padStart(2, '0');
                return m + ':' + s;
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

            slugName(name) { return (name || '').toLowerCase().trim(); },

            showSugar(name) {
                const item = this.selectedItem;
                // Kategori maincourse dan snack tidak punya sugar level
                if (item && (item.category === 'maincourse' || item.category === 'snack')) return false;
                const n = this.slugName(name);
                const noSugar = ['espresso', 'americano', 'capuccino',
                                  'french press', 'v60', 'vietnam drip',
                                  'tubruk', 'japanese', 'chocolate',
                                  'red velvet', 'taro', 'matcha', 'lemon tea',
                                  'orange yakults', 'mango yakults', 'blue sparkling'];
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

            quickAdd(menu) {
                this.addItemToCart(menu, 1, 'normal', 'normal');
                this.showToast('✅ ' + menu.name + ' ditambahkan');
            },

            addToCart() {
                this.addItemToCart(this.selectedItem, this.modalQty, this.modalSugar, this.modalIce, this.modalNotes);
                this.modalOpen = false;
                this.showToast('✅ ' + this.selectedItem.name + ' ditambahkan');
            },

            addItemToCart(menu, qty, sugar, ice, notes = '') {
                const hasSugar  = this.showSugar(menu.name);
                const hasIce    = this.showIce(menu.name, this.modalServeType);
                const isCapucc  = this.slugName(menu.name) === 'capuccino';
                const serveType = isCapucc ? this.modalServeType : null; // 'hot' | 'ice' | null
                const hasServe  = isCapucc || (!['espresso','french press','v60','vietnam drip','tubruk','japanese'].includes(this.slugName(menu.name)));
                const key = `${menu.id}-${hasSugar ? sugar : 'none'}-${hasIce ? ice : 'none'}-${serveType ?? 'none'}-${notes}`;
                const ex = this.cart.find(c => c.key === key);
                if (ex) { ex.qty += qty; ex.subtotal = ex.qty * menu.price; }
                else {
                    this.cart.push({
                        key, name: menu.name, menuId: menu.id,
                        qty,
                        sugar: hasSugar ? sugar : '-',
                        ice:   hasIce   ? ice   : '-',
                        hasSugar, hasIce, hasServe,
                        serveType,
                        notes: notes || '',
                        subtotal: menu.price * qty, price: menu.price
                    });
                }
            },

            removeFromCart(i) { this.cart.splice(i, 1); if (this.cart.length === 0) this.showCheckout = false; },

            showToast(msg) {
                this.toastMsg = msg; this.toast = true;
                setTimeout(() => this.toast = false, 2500);
            },

            // ── QRIS Polling & Countdown ────────────────────────────────────
            async startQrisPolling(orderId) {
                this.qrisCountdown = 600;
                this.paymentPaid    = false;
                this.paymentTimeout = false;

                // Countdown: turun 1 detik setiap 1000ms
                this._countdownId = setInterval(() => {
                    if (this.qrisCountdown > 0) {
                        this.qrisCountdown--;
                    } else {
                        // Waktu habis
                        this.stopPolling();
                        this.paymentTimeout = true;
                    }
                }, 1000);

                // Polling: cek status tiap 5 detik
                this._pollingId = setInterval(() => this.checkPaymentStatus(), 5000);
            },

            stopPolling() {
                if (this._countdownId) { clearInterval(this._countdownId); this._countdownId = null; }
                if (this._pollingId)   { clearInterval(this._pollingId);   this._pollingId   = null; }
            },

            async checkPaymentStatus() {
                if (!this.orderNumber || this.paymentPaid) return;
                try {
                    const res  = await fetch('/order/check/' + encodeURIComponent(this.orderNumber));
                    const data = await res.json();
                    if (data.success && data.paid) {
                        this.stopPolling();
                        this.orderItems = data.items || [];
                        this.paymentPaid = true;
                    }
                } catch (e) {
                    // Abaikan error jaringan sementara, polling akan coba lagi
                }
            },

            // ── Submit Order ────────────────────────────────────────────────
            async submitOrder() {
                if (this.cart.length === 0) return;
                this.submitting = true;
                try {
                    const res = await fetch('/order', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({
                            table_number: this.tableNumber,
                            payment_method: this.payment,
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
                    if (data.success) {
                        this.orderNumber = data.order_number;
                        this.qrisTotal   = data.total_price;
                        // Untuk cash: simpan items dari cart sekarang, lalu clear
                        if (this.payment === 'cash') {
                            this.orderItems = this.cart.map(c => {
                                const sugarLabel = c.sugar === 'less' ? 'Less Sweet' : c.sugar === 'extra' ? 'Extra Sweet' : 'Normal';
                                const iceLabel   = c.ice   === 'no_ice' ? 'No Ice' : c.ice === 'less' ? 'Less Ice' : 'Normal Ice';
                                const serveLabel = c.serveType === 'hot' ? 'Hot' : iceLabel;
                                return {
                                    name:        c.name,
                                    qty:         c.qty,
                                    subtotal:    c.subtotal,
                                    sugar_label: c.hasSugar ? sugarLabel : null,
                                    serve_label: c.hasServe ? serveLabel : null,
                                    has_sugar:   c.hasSugar,
                                    has_serve:   c.hasServe,
                                    notes:       c.notes || null,
                                };
                            });
                        }
                        this.showCheckout = false;
                        this.orderSuccess  = true;
                        // Mulai polling hanya untuk QRIS (kirim order_id untuk ambil snap token)
                        if (this.payment === 'qris') {
                            this.startQrisPolling(data.order_id);
                        }
                    } else {
                        alert('Gagal mengirim pesanan. Coba lagi.');
                    }
                } catch (e) {
                    alert('Koneksi bermasalah. Silakan coba lagi.');
                } finally {
                    this.submitting = false;
                }
            },

            resetOrder() {
                this.stopPolling();
                // Sembunyikan overlay terlebih dahulu agar animasi transisi berjalan
                this.orderSuccess = false;
                
                // Tunggu 350ms (sampai animasi slide selesai) baru bersihkan data
                setTimeout(() => {
                    this.cart           = [];
                    this.paymentPaid    = false;
                    this.paymentTimeout = false;
                    this.qrisCountdown  = 600;
                    this.orderItems     = [];
                    this.qrisTotal      = 0;
                    this.payment        = 'qris';
                    this.orderNumber    = '';
                    this.snapToken      = null;
                    this.snapLoading    = false;
                }, 350);
            },
        };
    }
    </script>
    @endpush
</x-app-layout>