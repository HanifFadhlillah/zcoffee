<x-cashier-layout>
    <x-slot:title>Edit Menu — ZCoffee</x-slot:title>

    <div class="p-6 max-w-xl">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('manage.menus.index') }}" class="w-9 h-9 bg-stone-100 hover:bg-stone-200 rounded-xl flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="font-serif text-2xl text-stone-900">Edit Menu</h1>
                <p class="text-stone-400 text-sm">{{ $menu->name }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-stone-100 p-6">
            <form method="POST" action="{{ route('manage.menus.update', $menu) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Nama Menu *</label>
                    <input type="text" name="name" value="{{ old('name', $menu->name) }}" required
                           class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Kategori *</label>
                    <select name="category" required
                            class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900 bg-white">
                        <option value="espresso" {{ old('category', $menu->category) === 'espresso' ? 'selected' : '' }}>Espresso Based</option>
                        <option value="manual" {{ old('category', $menu->category) === 'manual' ? 'selected' : '' }}>Manual Brewed</option>
                        <option value="noncoffee" {{ old('category', $menu->category) === 'noncoffee' ? 'selected' : '' }}>Non Coffee</option>
                        <option value="maincourse" {{ old('category', $menu->category) === 'maincourse' ? 'selected' : '' }}>Main Course</option>
                        <option value="snack" {{ old('category', $menu->category) === 'snack' ? 'selected' : '' }}>Snack</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Harga (Rp) *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-stone-400 text-sm">Rp</span>
                        <input type="number" name="price" value="{{ old('price', $menu->price) }}" required min="1000" step="500"
                               class="w-full border border-stone-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-stone-900">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="3"
                              class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900 resize-none">{{ old('description', $menu->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Foto Menu</label>
                    @if($menu->image)
                    <div class="flex items-center gap-3 mb-3 p-3 bg-stone-50 rounded-xl">
                        <img src="{{ $menu->image_url }}" class="w-14 h-14 object-cover rounded-lg">
                        <div>
                            <p class="text-xs font-medium text-stone-700">Foto saat ini</p>
                            <p class="text-xs text-stone-400">Upload baru untuk mengganti</p>
                        </div>
                    </div>
                    @endif
                    <div class="border-2 border-dashed border-stone-200 rounded-xl p-4 text-center hover:border-stone-400 transition-colors cursor-pointer"
                         onclick="document.getElementById('menu-img').click()">
                        <div id="img-preview" class="hidden mb-2">
                            <img id="preview-src" class="w-20 h-20 object-cover rounded-xl mx-auto">
                        </div>
                        <div id="img-placeholder">
                            <p class="text-sm text-stone-400">📷 Klik untuk ganti foto</p>
                        </div>
                        <input type="file" id="menu-img" name="image" accept="image/*" class="hidden" onchange="previewImage(this)">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Urutan Tampil</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $menu->sort_order) }}" min="0"
                               class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Status</label>
                        <label class="flex items-center gap-3 border border-stone-200 rounded-xl px-4 py-3 cursor-pointer hover:bg-stone-50">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $menu->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded">
                            <span class="text-sm text-stone-700">Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('manage.menus.index') }}"
                       class="flex-1 text-center border border-stone-200 text-stone-600 py-3 rounded-xl text-sm font-semibold hover:bg-stone-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            class="flex-1 bg-stone-900 text-white py-3 rounded-xl text-sm font-semibold hover:bg-stone-800 transition-colors">
                        Simpan Perubahan
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
