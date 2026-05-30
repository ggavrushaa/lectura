@props(['title' => 'Lectura', 'heading' => 'Добро пожаловать', 'subheading' => null])
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Lectura' }}</title>
    <script>
        (function () {
            var s = localStorage.getItem('theme');
            if (s === 'dark' || (!s && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen grid lg:grid-cols-2">
    {{-- Левая панель: брендинг (скрыта на мобильных) --}}
    <aside class="hidden lg:flex relative flex-col justify-between p-12 overflow-hidden"
           style="background:linear-gradient(150deg, var(--ink), color-mix(in oklab, var(--ink) 62%, var(--accent)));">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-30">
            <div class="absolute -left-10 top-1/4 w-72 h-72 rounded-full blur-3xl" style="background:var(--accent); animation:float-slow 8s ease-in-out infinite"></div>
            <div class="absolute right-0 bottom-10 w-80 h-80 rounded-full blur-3xl" style="background:#fff; opacity:.06"></div>
        </div>
        <a href="{{ route('home') }}" class="relative flex items-center gap-2.5 font-semibold text-lg text-white">
            <span class="w-8 h-8 rounded-[10px] grid place-items-center font-bold" style="background:rgba(255,255,255,.14)">L</span>
            Lectura
        </a>
        <div class="relative text-white">
            <p class="font-serif-display text-3xl leading-snug mb-4" style="color:rgba(255,255,255,.96)">
                «Запись лекции —<br>в стройный конспект.»
            </p>
            <p class="text-sm max-w-sm" style="color:rgba(255,255,255,.6)">
                Загрузил аудио — получил структурированный конспект со схемами, готовый к скачиванию.
            </p>
        </div>
        <div class="relative text-xs" style="color:rgba(255,255,255,.45)">Whisper · OpenRouter</div>
    </aside>

    {{-- Правая панель: форма --}}
    <main class="flex flex-col items-center justify-center px-6 py-12 relative">
        <button type="button" onclick="toggleTheme()" aria-label="Сменить тему" class="icon-btn absolute top-5 right-5">
            <span class="block dark:hidden">☾</span><span class="hidden dark:block">☀</span>
        </button>

        <div class="w-full max-w-sm anim-fade-up">
            <a href="{{ route('home') }}" class="lg:hidden flex items-center justify-center gap-2.5 font-semibold text-lg mb-8">
                <span class="w-8 h-8 rounded-[10px] grid place-items-center text-white font-bold" style="background:var(--ink)">L</span>
                Lectura
            </a>

            <div class="mb-7">
                <h1 class="text-2xl font-bold tracking-tight">{{ $heading ?? 'Добро пожаловать' }}</h1>
                @isset($subheading)
                    <p class="text-sm mt-1.5" style="color:var(--muted)">{{ $subheading }}</p>
                @endisset
            </div>

            {{ $slot }}
        </div>
    </main>
</body>
</html>
