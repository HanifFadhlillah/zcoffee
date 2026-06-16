<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ZCoffee Dashboard' }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>☕</text></svg>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full flex bg-stone-50 font-sans antialiased" x-data="{ sidebarOpen: true }">

    {{-- SIDEBAR --}}
    <aside class="w-56 bg-stone-900 flex flex-col flex-shrink-0 h-screen sticky top-0 overflow-y-auto">
        {{-- Logo --}}
        <div class="px-5 py-5 border-b border-white/10">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center font-bold text-stone-900 text-sm">Z</div>
                <div>
                    <div class="font-serif text-white text-base leading-tight">ZCoffee</div>
                    <div class="text-white/40 text-[10px]">
                        {{ auth()->user()->isAdmin() ? 'Admin Panel' : 'Kasir Dashboard' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1">
            @if(auth()->user()->isAdmin())
                <p class="px-2 text-[10px] font-semibold tracking-widest text-white/30 uppercase mb-2">Dashboard</p>
                <a href="{{ route('admin.dashboard') }}" @class([
                    'flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                    'bg-amber-500/20 text-amber-400' => request()->routeIs('admin.dashboard'),
                    'text-white/60 hover:text-white hover:bg-white/8' => !request()->routeIs('admin.dashboard'),
                ])>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Statistik
                </a>
                <a href="{{ route('admin.reports.index') }}" @class([
                    'flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                    'bg-amber-500/20 text-amber-400' => request()->routeIs('admin.reports.*'),
                    'text-white/60 hover:text-white hover:bg-white/8' => !request()->routeIs('admin.reports.*'),
                ])>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Rekap Penjualan
                </a>
            @endif

            <p class="px-2 text-[10px] font-semibold tracking-widest text-white/30 uppercase mb-2 mt-4">Manajemen</p>
            <a href="{{ route('manage.menus.index') }}" @class([
                'flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                'bg-amber-500/20 text-amber-400' => request()->routeIs('manage.menus.*'),
                'text-white/60 hover:text-white hover:bg-white/8' => !request()->routeIs('manage.menus.*'),
            ])>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Kelola Menu
            </a>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}" @class([
                    'flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                    'bg-amber-500/20 text-amber-400' => request()->routeIs('admin.users.*'),
                    'text-white/60 hover:text-white hover:bg-white/8' => !request()->routeIs('admin.users.*'),
                ])>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Kelola Kasir
                </a>
            @endif

            @if(!auth()->user()->isAdmin())
                <p class="px-2 text-[10px] font-semibold tracking-widest text-white/30 uppercase mb-2 mt-4">Operasional</p>
                <a href="{{ route('cashier.dashboard') }}" @class([
                    'flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                    'bg-amber-500/20 text-amber-400' => request()->routeIs('cashier.dashboard'),
                    'text-white/60 hover:text-white hover:bg-white/8' => !request()->routeIs('cashier.dashboard'),
                ])>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Order Masuk
                    @php $pendingCount = \App\Models\Order::pending()->today()->count() @endphp
                    @if($pendingCount > 0)
                        <span class="ml-auto bg-amber-500 text-stone-900 text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $pendingCount }}</span>
                    @endif
                </a>
            @endif
        </nav>

        {{-- User info & logout --}}
        <div class="px-3 py-4 border-t border-white/10">
            <div class="flex items-center gap-2.5 px-2 mb-3">
                <div class="w-8 h-8 bg-stone-700 rounded-full flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-white text-xs font-medium truncate">{{ auth()->user()->name }}</div>
                    <div class="text-white/40 text-[10px] capitalize">{{ auth()->user()->role }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-white/50 hover:text-white hover:bg-white/8 text-xs font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 overflow-y-auto flex flex-col min-h-screen">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mx-6 mt-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2" x-data x-init="setTimeout(() => $el.remove(), 4000)">
                <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm flex items-center gap-2" x-data x-init="setTimeout(() => $el.remove(), 5000)">
                <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
