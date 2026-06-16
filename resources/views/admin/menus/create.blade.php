<x-cashier-layout>
    <x-slot:title>Tambah Menu — ZCoffee</x-slot:title>

    <div class="p-6 max-w-xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('manage.menus.index') }}" class="w-9 h-9 bg-stone-100 hover:bg-stone-200 rounded-xl flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="font-serif text-2xl text-stone-900">Tambah Menu Baru</h1>
                <p class="text-stone-400 text-sm">Isi detail menu yang akan ditampilkan ke pelanggan</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-stone-100 p-6">
            <form method="POST" action="{{ route('manage.menus.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Nama Menu *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900 transition-colors @error('name') border-red-300 @enderror"
                           placeholder="e.g. Ice Latte">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Kategori *</label>
                    <select name="category" required
                            class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900 transition-colors bg-white">
                        <option value="">Pilih kategori...</option>
                        <option value="espresso" {{ old('category') === 'espresso' ? 'selected' : '' }}>Espresso Based</option>
                        <option value="manual" {{ old('category') === 'manual' ? 'selected' : '' }}>Manual Brewed</option>
                        <option value="noncoffee" {{ old('category') === 'noncoffee' ? 'selected' : '' }}>Non Coffee</option>
                        <option value="maincourse" {{ old('category') === 'maincourse' ? 'selected' : '' }}>Main Course</option>
                        <option value="snack" {{ old('category') === 'snack' ? 'selected' : '' }}>Snack</option>
                    </select>
                    @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Price --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Harga (Rp) *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 text-sm">Rp</span>
                        <input type="number" name="price" value="{{ old('price') }}" required min="1000" step="500"
                               class="w-full border border-stone-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-stone-900 transition-colors @error('price') border-red-300 @enderror"
                               placeholder="12000">
                    </div>
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900 transition-colors resize-none"
                              placeholder="Deskripsi singkat menu...">{{ old('description') }}</textarea>
                </div>

                {{-- Image Upload --}}
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Foto Menu</label>
                    <div class="border-2 border-dashed border-stone-200 rounded-xl p-6 text-center hover:border-stone-400 transition-colors cursor-pointer"
                         onclick="document.getElementById('menu-img').click()">
                        <div id="img-preview" class="hidden mb-3">
                            <img id="preview-src" class="w-24 h-24 object-cover rounded-xl mx-auto">
                        </div>
                        <div id="img-placeholder">
                            <div class="text-2xl mb-2">📷</div>
                            <p class="text-sm text-stone-400">Klik untuk upload foto</p>
                            <p class="text-xs text-stone-300 mt-1">JPG, PNG, WebP · Max 2MB</p>
                        </div>
                        <input type="file" id="menu-img" name="image" accept="image/*" class="hidden"
                               onchange="previewImage(this)">
                    </div>
                    @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Sort Order --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Urutan Tampil</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                               class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Status</label>
                        <label class="flex items-center gap-3 border border-stone-200 rounded-xl px-4 py-3 cursor-pointer hover:bg-stone-50">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded">
                            <span class="text-sm text-stone-700">Aktif (tampil di menu)</span>
                        </label>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('manage.menus.index') }}"
                       class="flex-1 text-center border border-stone-200 text-stone-600 py-3 rounded-xl text-sm font-semibold hover:bg-stone-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="flex-1 bg-stone-900 text-white py-3 rounded-xl text-sm font-semibold hover:bg-stone-800 transition-colors">
                        Simpan Menu
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('preview-src').src = e.target.result;
                document.getElementById('img-preview').classList.remove('hidden');
                document.getElementById('img-placeholder').classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
    @endpush
</x-cashier-layout>
