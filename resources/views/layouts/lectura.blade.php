<!DOCTYPE html>
<html lang="ru" @class(['dark' => false])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Lectura' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <nav class="flex items-center justify-between px-7 py-4 border-b" style="border-color:var(--line)">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold text-lg">
            <span class="w-7 h-7 rounded-lg grid place-items-center text-white" style="background:var(--ink)">L</span>
            Lectura
        </a>
        <div class="flex items-center gap-3">
            <button data-theme-toggle onclick="toggleTheme()" class="px-3 py-2 rounded-lg text-sm" style="background:var(--soft)">🌓</button>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="px-3 py-2 rounded-lg text-sm" style="color:var(--ink2)">Выйти</button>
            </form>
        </div>
    </nav>
    <main class="max-w-5xl mx-auto px-5 py-8">
        {{ $slot }}
    </main>
</body>
</html>
