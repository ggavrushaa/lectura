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
<body class="min-h-screen lg:grid lg:grid-cols-[1.05fr_1fr] lg:min-h-screen" style="min-height:100vh">

    {{-- ═══════ ЛЕВО: брендовый showcase (скрыт на мобильных) ═══════ --}}
    <aside class="hidden lg:flex relative flex-col justify-between p-12 xl:p-16 overflow-hidden text-white"
           style="isolation:isolate; background:#2a1505; box-shadow:inset -1px 0 0 rgba(255,255,255,.08)">
        {{-- атмосфера: насыщенный градиентный меш --}}
        <div aria-hidden="true" class="absolute inset-0 -z-10" style="
             background:
               radial-gradient(46rem 32rem at 8% 4%, #fbab2c, transparent 56%),
               radial-gradient(40rem 32rem at 96% 98%, #c2410c, transparent 54%),
               radial-gradient(34rem 28rem at 82% 14%, #8b5cf6, transparent 52%),
               radial-gradient(32rem 28rem at 24% 82%, #ea580c, transparent 56%),
               #2a1505;"></div>
        {{-- лёгкое затемнение только снизу — под соц-доказательство --}}
        <div aria-hidden="true" class="absolute inset-x-0 bottom-0 h-2/5 -z-10" style="background:linear-gradient(to top, rgba(15,8,2,.55), transparent)"></div>
        <div aria-hidden="true" class="absolute inset-0 -z-10 opacity-[.06]" style="
             background-image:url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E&quot;);"></div>

        {{-- лого --}}
        <a href="{{ route('home') }}" class="relative flex items-center gap-2.5 font-semibold text-lg">
            <span class="w-9 h-9 rounded-xl grid place-items-center font-bold" style="background:rgba(255,255,255,.16); backdrop-filter:blur(6px)">L</span>
            Lectura
        </a>

        {{-- центр: оффер + плавающее превью --}}
        <div class="relative">
            <h2 class="font-serif-display text-4xl xl:text-5xl leading-[1.1] mb-5">
                Аудио лекции —<br>в стройный конспект
            </h2>
            <p class="text-base max-w-sm mb-10" style="color:rgba(255,255,255,.7)">
                Загрузите запись — получите структуру, термины и схемы. Готово к скачиванию за пару минут.
            </p>

            {{-- мини-превью конспекта (glassmorphism) --}}
            <div class="rounded-2xl p-5 max-w-sm" style="background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.14); backdrop-filter:blur(14px); box-shadow:0 24px 60px -20px rgba(0,0,0,.6); animation:float-slow 7s ease-in-out infinite">
                <div class="flex items-center gap-2 mb-3" style="color:rgba(255,255,255,.5)">
                    <span class="w-2 h-2 rounded-full" style="background:rgba(255,255,255,.3)"></span>
                    <span class="w-2 h-2 rounded-full" style="background:rgba(255,255,255,.3)"></span>
                    <span class="w-2 h-2 rounded-full" style="background:rgba(255,255,255,.3)"></span>
                    <span class="ml-1 text-xs">Лекция 7 · Нейросети</span>
                </div>
                <div class="font-serif-display text-lg mb-2">Нейронные сети: основы</div>
                <div class="space-y-1.5 mb-3">
                    <div class="h-2 rounded-full" style="background:rgba(255,255,255,.16); width:94%"></div>
                    <div class="h-2 rounded-full" style="background:rgba(255,255,255,.16); width:80%"></div>
                    <div class="h-2 rounded-full" style="background:rgba(255,255,255,.16); width:88%"></div>
                </div>
                <div class="flex gap-1.5">
                    <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(255,255,255,.14)">нейрон</span>
                    <span class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(255,255,255,.14)">веса</span>
                    <span class="text-xs px-2 py-0.5 rounded-full" style="background:var(--accent); color:#fff">+3</span>
                </div>
            </div>
        </div>

        {{-- низ: соц-доказательство --}}
        <div class="relative flex items-center gap-4 text-sm" style="color:rgba(255,255,255,.6)">
            <div class="flex -space-x-2">
                @foreach (['А','М','К','Д'] as $ini)
                    <span class="w-8 h-8 rounded-full grid place-items-center text-xs font-semibold text-white"
                          style="background:linear-gradient(135deg,var(--accent),#7c4dff); border:2px solid #0c0b10">{{ $ini }}</span>
                @endforeach
            </div>
            <span>Студенты и профессионалы уже экономят часы на конспектах</span>
        </div>
    </aside>

    {{-- ═══════ ПРАВО: форма ═══════ --}}
    <main class="relative flex flex-col items-center justify-center px-5 py-12 min-h-screen lg:min-h-0">
        <button type="button" onclick="toggleTheme()" aria-label="Сменить тему" class="icon-btn absolute top-5 right-5 z-10">
            <span class="block dark:hidden">☾</span><span class="hidden dark:block">☀</span>
        </button>

        <div class="w-full max-w-sm anim-fade-up">
            {{-- компактный бренд (виден только на мобильных, где левой панели нет) --}}
            <a href="{{ route('home') }}" class="lg:hidden flex items-center justify-center gap-2.5 font-semibold text-lg mb-8">
                <span class="w-9 h-9 rounded-xl grid place-items-center text-white font-bold"
                      style="background:linear-gradient(135deg, var(--ink), color-mix(in oklab, var(--ink) 70%, var(--accent)));">L</span>
                Lectura
            </a>

            <div class="mb-7">
                <h1 class="text-3xl font-bold tracking-tight">{{ $heading }}</h1>
                @if ($subheading)
                    <p class="text-sm mt-2" style="color:var(--muted)">{{ $subheading }}</p>
                @endif
            </div>

            {{ $slot }}
        </div>
    </main>
</body>
</html>
