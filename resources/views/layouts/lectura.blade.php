@props(['title' => 'Lectura', 'description' => null])
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Lectura' }}</title>
    @isset($description)
        <meta name="description" content="{{ $description }}">
        <meta property="og:title" content="{{ $title ?? 'Lectura' }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:type" content="website">
        <meta property="og:locale" content="ru_RU">
        <meta name="twitter:card" content="summary_large_image">
    @endisset
    {{-- Тему ставим до рендера, чтобы не было вспышки светлой темы --}}
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
<body class="min-h-screen flex flex-col">
    <header class="sticky top-0 z-40 anim-fade-in"
            x-data
            style="backdrop-filter:saturate(160%) blur(12px); background:color-mix(in oklab, var(--bg) 82%, transparent); border-bottom:1px solid var(--line)">
        <nav class="max-w-6xl mx-auto w-full flex items-center justify-between px-5 sm:px-7 py-3.5">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}"
               class="group flex items-center gap-2.5 font-semibold text-lg tracking-tight">
                <span class="w-8 h-8 rounded-[10px] grid place-items-center text-white font-bold transition-transform group-hover:rotate-[-8deg] group-hover:scale-105"
                      style="background:linear-gradient(135deg, var(--ink), color-mix(in oklab, var(--ink) 70%, var(--accent)));">L</span>
                Lectura
            </a>

            <div class="flex items-center gap-2">
                <button type="button" data-theme-toggle onclick="toggleTheme()" aria-label="Сменить тему"
                        class="icon-btn">
                    <span class="block dark:hidden">☾</span>
                    <span class="hidden dark:block">☀</span>
                </button>

                @auth
                    <div class="relative" x-data="{ open:false }" @keydown.escape.window="open=false">
                        <button type="button" @click="open=!open" :aria-expanded="open"
                                class="flex items-center gap-2 pl-1.5 pr-2.5 py-1.5 rounded-[10px] transition-colors"
                                style="border:1px solid var(--line)"
                                onmouseover="this.style.background='var(--soft)'" onmouseout="this.style.background='transparent'">
                            <span class="w-7 h-7 rounded-full grid place-items-center text-white text-sm font-semibold"
                                  style="background:var(--accent)">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="text-sm font-medium hidden sm:block max-w-[10rem] truncate">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 transition-transform" :class="open && 'rotate-180'" style="color:var(--muted)" viewBox="0 0 20 20" fill="none"><path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>

                        <div x-show="open" x-transition.origin.top.right
                             @click.outside="open=false" x-cloak
                             class="absolute right-0 mt-2 w-56 rounded-2xl p-1.5 z-50 origin-top-right"
                             style="background:var(--panel); border:1px solid var(--line); box-shadow:var(--shadow-lg)">
                            <div class="px-3 py-2.5 mb-1">
                                <div class="text-sm font-medium truncate">{{ auth()->user()->name }}</div>
                                <div class="text-xs truncate" style="color:var(--muted)">{{ auth()->user()->email }}</div>
                            </div>
                            <div style="height:1px;background:var(--line)"></div>
                            <a href="{{ route('dashboard') }}" class="dropdown-item">
                                <span style="color:var(--muted)">▤</span> Мои лекции
                            </a>
                            <a href="{{ route('lectures.create') }}" class="dropdown-item">
                                <span style="color:var(--muted)">＋</span> Новая лекция
                            </a>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <span style="color:var(--muted)">⚙</span> Профиль
                            </a>
                            <div style="height:1px;background:var(--line)" class="my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button type="submit" class="dropdown-item w-full text-left" style="color:var(--danger)">
                                    <span>⏻</span> Выйти
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost hidden sm:inline-flex">Войти</a>
                    <a href="{{ route('register') }}" class="btn btn-accent">Начать</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="flex-1 w-full max-w-6xl mx-auto px-5 sm:px-7 py-8 sm:py-12">
        {{ $slot }}
    </main>

    <footer class="w-full border-t mt-auto" style="border-color:var(--line)">
        <div class="max-w-6xl mx-auto px-5 sm:px-7 py-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm" style="color:var(--muted)">
            <span>Lectura — конспекты лекций из аудио</span>
            <span>© {{ date('Y') }}</span>
        </div>
    </footer>

    <style>
        [x-cloak]{ display:none !important; }
        .dropdown-item{
            display:flex; align-items:center; gap:.65rem; padding:.6rem .75rem;
            border-radius:.6rem; font-size:.9rem; color:var(--ink2); cursor:pointer;
            transition:background .15s ease, color .15s ease;
        }
        .dropdown-item:hover{ background:var(--soft); color:var(--ink); }
    </style>
</body>
</html>
