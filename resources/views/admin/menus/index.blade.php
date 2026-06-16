<x-cashier-layout>
    <x-slot:title>Kelola Menu — ZCoffee</x-slot:title>

    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="font-serif text-2xl text-stone-900">Kelola Menu</h1>
                <p class="text-stone-400 text-sm">{{ \App\Models\Menu::count() }} menu terdaftar</p>
            </div>
            <a href="{{ route('manage.menus.create') }}"
               class="inline-flex items-center gap-2 bg-stone-900 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-stone-800 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Menu
            </a>
        </div>

        @foreach(['espresso' => 'Espresso Based', 'manual' => 'Manual Brewed', 'noncoffee' => 'Non Coffee', 'maincourse' => 'Main Course', 'snack' => 'Snack'] as $cat => $label)
            @if(isset($menus[$cat]))
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-3">
                    <h2 class="font-semibold text-stone-700">{{ $label }}</h2>
                    <span class="text-xs bg-stone-100 text-stone-500 px-2 py-0.5 rounded-full">{{ $menus[$cat]->count() }} item</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    @foreach($menus[$cat] as $menu)
                    <div class="bg-white rounded-2xl border border-stone-100 p-4 flex items-center gap-4 group hover:border-stone-200 transition-colors">
                        {{-- Image --}}
                         @php
                             $bgClass = match($cat) {
                                 'espresso'   => 'bg-amber-50',
                                 'manual'     => 'bg-emerald-50',
                                 'maincourse' => 'bg-red-50',
                                 'snack'      => 'bg-yellow-50',
                                 default      => 'bg-violet-50',
                             };
                             $emoji = match($cat) {
                                 'espresso'   => '☕',
                                 'manual'     => '🫙',
                                 'maincourse' => '🍽️',
                                 'snack'      => '🍿',
                                 default      => '🥤',
                             };
                         @endphp
                         <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0 {{ $bgClass }} flex items-center justify-center">
                             @if($menu->image)
                                 <img src="{{ $menu->image_url }}" class="w-full h-full object-cover" alt="{{ $menu->name }}">
                             @else
                                 <span class="text-2xl">{{ $emoji }}</span>
                             @endif
                         </div>
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-stone-900 text-sm truncate">{{ $menu->name }}</p>
                            <p class="text-amber-600 font-bold text-sm">{{ $menu->formatted_price }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <button wire:click="toggleActive({{ $menu->id }})"
                                    onclick="toggleActive({{ $menu->id }}, this)"
                                    class="text-xs px-2 py-0.5 rounded-full font-medium transition-colors
                                        {{ $menu->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-stone-100 text-stone-500 hover:bg-stone-200' }}">
                                    {{ $menu->is_active ? '● Aktif' : '○ Non-aktif' }}
                                </button>
                            </div>
                        </div>
                        {{-- Actions --}}
                        <div class="flex flex-col gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('manage.menus.edit', $menu) }}"
                               class="w-8 h-8 bg-stone-100 hover:bg-stone-900 hover:text-white rounded-lg flex items-center justify-center transition-colors text-stone-600">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('manage.menus.destroy', $menu) }}"
                                  onsubmit="return confirm('Hapus menu {{ $menu->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 bg-stone-100 hover:bg-red-500 hover:text-white rounded-lg flex items-center justify-center transition-colors text-stone-600">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>

    @push('scripts')
    <script>
    async function toggleActive(id, btn) {
        const res = await fetch(`/admin/menus/${id}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
        });
        const data = await res.json();
        if (data.success) {
            btn.textContent = data.is_active ? '● Aktif' : '○ Non-aktif';
            btn.className = btn.className.replace(/bg-(green|stone)-\d+|text-(green|stone)-\d+/g, '');
            if (data.is_active) {
                btn.classList.add('bg-green-100', 'text-green-700');
            } else {
                btn.classList.add('bg-stone-100', 'text-stone-500');
            }
        }
    }
    </script>
    @endpush
</x-cashier-layout>
