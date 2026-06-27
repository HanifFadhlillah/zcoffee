<x-cashier-layout>
    <x-slot:title>Kelola Kasir — ZCoffee</x-slot:title>

    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="font-serif text-2xl text-stone-900">Kelola Akun Kasir</h1>
                <p class="text-stone-400 text-sm">{{ $users->count() }} akun kasir terdaftar</p>
            </div>
            <button onclick="document.getElementById('add-user-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-stone-900 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-stone-800 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Kasir
            </button>
        </div>

        {{-- User Table --}}
        <div class="bg-white rounded-2xl border border-stone-100 overflow-hidden">
            <div class="divide-y divide-stone-50">
                @forelse($users as $user)
                <div class="px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 bg-stone-900 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-stone-900">{{ $user->name }}</p>
                        <p class="text-xs text-stone-400">{{ $user->email }} · Bergabung {{ $user->created_at->format('d M Y') }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-100 text-blue-800">Kasir</span>
                    <div class="flex items-center gap-2">
                        <button onclick="openResetModal({{ $user->id }}, '{{ $user->name }}')"
                                class="text-xs px-3 py-1.5 border border-stone-200 rounded-lg text-stone-600 hover:bg-stone-50 transition-colors font-medium">
                            Reset Password
                        </button>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('Hapus akun {{ $user->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center text-stone-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="px-5 py-12 text-center text-stone-300">
                    <p class="text-sm">Belum ada akun kasir</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── MODAL: ADD USER ── --}}
    <div id="add-user-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-stone-900">Tambah Akun Kasir</h2>
                <button onclick="document.getElementById('add-user-modal').classList.add('hidden')"
                        class="w-7 h-7 bg-stone-100 rounded-full flex items-center justify-center text-stone-500">×</button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf

                {{-- Tampilkan error validasi --}}
                @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                    <p class="text-xs font-semibold text-red-600 mb-1">Gagal membuat akun:</p>
                    <ul class="text-xs text-red-500 space-y-0.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Nama</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-stone-900"
                           placeholder="Nama lengkap kasir">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Email</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-stone-900"
                           placeholder="email@zcoffee.id">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Password</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-stone-900"
                           placeholder="Min. 8 karakter (harus ada huruf & angka)">
                    <p class="text-xs text-stone-400 mt-1">Contoh: <code class="bg-stone-100 px-1 rounded">Kasir123</code></p>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('add-user-modal').classList.add('hidden')"
                            class="flex-1 border border-stone-200 py-2.5 rounded-xl text-sm font-semibold text-stone-600">Batal</button>
                    <button type="submit"
                            class="flex-1 bg-stone-900 text-white py-2.5 rounded-xl text-sm font-semibold">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── MODAL: RESET PASSWORD ── --}}
    <div id="reset-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-stone-900">Reset Password</h2>
                <button onclick="document.getElementById('reset-modal').classList.add('hidden')"
                        class="w-7 h-7 bg-stone-100 rounded-full flex items-center justify-center text-stone-500">×</button>
            </div>
            <p class="text-sm text-stone-500 mb-4">Reset password untuk: <strong id="reset-user-name"></strong></p>
            <form id="reset-form" method="POST" class="space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Password Baru</label>
                    <input type="password" name="password" required minlength="8"
                           class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-stone-900"
                           placeholder="Min. 8 karakter">
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('reset-modal').classList.add('hidden')"
                            class="flex-1 border border-stone-200 py-2.5 rounded-xl text-sm font-semibold text-stone-600">Batal</button>
                    <button type="submit"
                            class="flex-1 bg-stone-900 text-white py-2.5 rounded-xl text-sm font-semibold">Reset</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openResetModal(userId, userName) {
        document.getElementById('reset-user-name').textContent = userName;
        document.getElementById('reset-form').action = `/admin/users/${userId}/reset-password`;
        document.getElementById('reset-modal').classList.remove('hidden');
    }

    // Buka modal tambah kasir otomatis jika ada error validasi
    @if ($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('add-user-modal').classList.remove('hidden');
    });
    @endif
    </script>
    @endpush
</x-cashier-layout>
