@props(['title' => 'Lectura', 'heading' => 'Добро пожаловать', 'subheading' => null])
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
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
<body class="min-h-screen grid place-items-center px-5 py-10">

    <button type="button" onclick="toggleTheme()" aria-label="Сменить тему" class="icon-btn fixed top-5 right-5 z-10">
        <span class="block dark:hidden">☾</span><span class="hidden dark:block">☀</span>
    </button>

    <div class="w-full max-w-md anim-fade-up">
        {{-- Брендинг --}}
        <a href="{{ route('home') }}" class="flex items-center justify-center gap-2.5 font-semibold text-lg mb-7">
            <span class="w-9 h-9 rounded-xl grid place-items-center text-white font-bold"
                  style="background:linear-gradient(135deg, var(--ink), color-mix(in oklab, var(--ink) 70%, var(--accent)));">L</span>
            Lectura
        </a>

        {{-- Карточка с формой --}}
        <div class="card p-7 sm:p-9" style="box-shadow:var(--shadow-lg)">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold tracking-tight">{{ $heading }}</h1>
                @if ($subheading)
                    <p class="text-sm mt-1.5" style="color:var(--muted)">{{ $subheading }}</p>
                @endif
            </div>

            {{ $slot }}
        </div>

        <p class="text-center text-xs mt-6" style="color:var(--muted)">
            Конспекты лекций из аудио за пару минут
        </p>
    </div>
</body>
</html>
