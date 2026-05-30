<x-lectura-layout title="Lectura — конспекты лекций из аудио">
<section class="text-center py-20">
  <div class="inline-flex items-center gap-2 text-sm px-3 py-1.5 rounded-full mb-6" style="background:var(--soft)">
    На базе Whisper + OpenRouter
  </div>
  <h1 class="text-5xl font-semibold leading-tight tracking-tight mb-5">
    Запись лекции — в <span class="font-serif-display italic" style="color:var(--accent)">стройный конспект</span>
  </h1>
  <p class="text-lg max-w-xl mx-auto mb-8" style="color:var(--muted)">
    Загрузите аудио с диктофона и получите структурированный конспект со схемами. Без часов прослушивания.
  </p>
  <div class="flex gap-3 justify-center">
    @auth
      <a href="{{ route('lectures.create') }}" class="px-5 py-3 rounded-xl text-white font-medium" style="background:var(--ink)">Загрузить аудио</a>
    @else
      <a href="{{ route('register') }}" class="px-5 py-3 rounded-xl text-white font-medium" style="background:var(--ink)">Начать бесплатно</a>
      <a href="{{ route('login') }}" class="px-5 py-3 rounded-xl border" style="border-color:var(--line)">Войти</a>
    @endauth
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-px mt-16 rounded-2xl overflow-hidden border max-w-4xl mx-auto text-left" style="border-color:var(--line);background:var(--line)">
    @foreach ([['01','Загрузка','MP3, M4A, WAV, OGG'],['02','Расшифровка','Whisper по-русски'],['03','Конспект','структура, термины, схемы'],['04','Экспорт','PDF, Word, Markdown']] as [$n,$h,$p])
      <div class="p-5" style="background:var(--panel)">
        <div class="text-sm" style="color:var(--muted)">{{ $n }}</div>
        <div class="font-medium mt-2">{{ $h }}</div>
        <div class="text-sm mt-1" style="color:var(--muted)">{{ $p }}</div>
      </div>
    @endforeach
  </div>
</section>
</x-lectura-layout>
