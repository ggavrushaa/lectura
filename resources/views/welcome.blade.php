<x-lectura-layout title="Lectura — конспекты лекций из аудио">

<section class="relative text-center pt-10 pb-16 sm:pt-16 sm:pb-24">
  {{-- декоративные плавающие свечения --}}
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
    <div class="absolute left-[8%] top-10 w-44 h-44 rounded-full blur-3xl opacity-40"
         style="background:var(--accent); animation:float-slow 7s ease-in-out infinite"></div>
    <div class="absolute right-[10%] top-28 w-56 h-56 rounded-full blur-3xl opacity-25"
         style="background:var(--accent); animation:float-slow 9s ease-in-out infinite reverse"></div>
  </div>

  <div class="stagger">
    <div style="--i:0" class="flex justify-center">
      <span class="pill pill-accent">
        <span class="dot dot-live"></span> На базе Whisper + OpenRouter
      </span>
    </div>

    <h1 style="--i:1" class="text-5xl sm:text-6xl font-extrabold leading-[1.04] tracking-tight mt-6 mb-5 max-w-3xl mx-auto">
      Запись лекции —<br>в <span class="font-serif-display italic font-medium" style="color:var(--accent)">стройный конспект</span>
    </h1>

    <p style="--i:2" class="text-lg sm:text-xl max-w-xl mx-auto mb-9 leading-relaxed" style="color:var(--muted)">
      Загрузите аудио с диктофона и получите структурированный конспект со схемами. Без часов прослушивания.
    </p>

    <div style="--i:3" class="flex flex-wrap gap-3 justify-center">
      @auth
        <a href="{{ route('lectures.create') }}" class="btn btn-accent text-base px-6 py-3.5">Загрузить аудио →</a>
        <a href="{{ route('dashboard') }}" class="btn btn-ghost text-base px-6 py-3.5">Мои лекции</a>
      @else
        <a href="{{ route('register') }}" class="btn btn-accent text-base px-6 py-3.5">Начать бесплатно →</a>
        <a href="{{ route('login') }}" class="btn btn-ghost text-base px-6 py-3.5">Войти</a>
      @endauth
    </div>
  </div>
</section>

{{-- Как это работает --}}
<section class="anim-fade-up" style="animation-delay:.3s">
  <div class="text-center mb-8">
    <div class="text-xs font-semibold tracking-[.18em] uppercase" style="color:var(--muted)">Как это работает</div>
    <h2 class="font-serif-display text-3xl mt-2">Четыре шага до конспекта</h2>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach ([
      ['01','Загрузка','Перетащите аудиофайл — MP3, M4A, WAV, OGG.','↑'],
      ['02','Расшифровка','Whisper точно распознаёт русскую речь.','✎'],
      ['03','Конспект','ИИ структурирует, выделяет термины, строит схемы.','◆'],
      ['04','Экспорт','Скачайте в PDF, Word или Markdown.','⤓'],
    ] as $i => [$n,$h,$p,$ic])
      <div class="card p-5 text-left transition-all hover:-translate-y-1" style="box-shadow:var(--shadow-sm)"
           onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='var(--shadow-sm)'">
        <div class="flex items-center justify-between mb-4">
          <span class="text-sm font-semibold tabular-nums" style="color:var(--accent)">{{ $n }}</span>
          <span class="w-9 h-9 rounded-xl grid place-items-center text-lg" style="background:var(--accent-soft); color:var(--accent)">{{ $ic }}</span>
        </div>
        <div class="font-semibold mb-1">{{ $h }}</div>
        <div class="text-sm leading-relaxed" style="color:var(--muted)">{{ $p }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- Финальный CTA --}}
@guest
<section class="anim-fade-up mt-16" style="animation-delay:.4s">
  <div class="card p-10 text-center relative overflow-hidden"
       style="background:linear-gradient(135deg, var(--panel), var(--accent-soft))">
    <h2 class="font-serif-display text-3xl mb-3">Попробуйте на первой лекции</h2>
    <p class="max-w-md mx-auto mb-6" style="color:var(--muted)">Регистрация занимает меньше минуты.</p>
    <a href="{{ route('register') }}" class="btn btn-accent text-base px-6 py-3.5">Создать аккаунт →</a>
  </div>
</section>
@endguest

</x-lectura-layout>
