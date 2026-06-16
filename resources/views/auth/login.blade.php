<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — ZCoffee</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-stone-900 flex items-center justify-center p-4 font-sans">
    <div class="w-full max-w-sm">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 bg-amber-500 rounded-2xl flex items-center justify-center font-bold text-stone-900 text-2xl mx-auto mb-3">Z</div>
            <h1 class="font-serif text-white text-3xl">ZCoffee</h1>
            <p class="text-white/40 text-sm mt-1">Masuk ke dashboard</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-3xl p-7 shadow-2xl">
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900 transition-colors @error('email') border-red-300 bg-red-50 @enderror"
                           placeholder="admin@zcoffee.id">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-500 uppercase tracking-wider mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-stone-900 transition-colors"
                           placeholder="••••••••">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded">
                    <span class="text-sm text-stone-500">Ingat saya</span>
                </label>

                <button type="submit"
                        class="w-full bg-stone-900 text-white py-3.5 rounded-xl font-semibold text-sm hover:bg-stone-800 transition-colors mt-2">
                    Masuk →
                </button>
            </form>
        </div>

        <p class="text-center text-white/30 text-xs mt-6">
            Default: admin@zcoffee.id / password
        </p>
    </div>
</body>
</html>
