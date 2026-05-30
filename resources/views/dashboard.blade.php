<x-lectura-layout title="Мои лекции">

<div class="flex items-end justify-between mb-7 gap-4 anim-fade-up">
  <div>
    <h1 class="text-3xl font-bold tracking-tight">Мои лекции</h1>
    <p class="text-sm mt-1" style="color:var(--muted)">
      {{ $lectures->count() ? $lectures->count().' '.trans_choice('лекция|лекции|лекций', $lectures->count()) : 'Здесь появятся ваши конспекты' }}
    </p>
  </div>
  <a href="{{ route('lectures.create') }}" class="btn btn-accent">
    <span class="text-lg leading-none">＋</span> Новая лекция
  </a>
</div>

@if ($lectures->isEmpty())
  <div class="card p-12 sm:p-16 text-center anim-scale-in">
    <div class="w-16 h-16 mx-auto mb-5 rounded-2xl grid place-items-center text-3xl"
         style="background:var(--accent-soft); color:var(--accent)">🎙️</div>
    <h2 class="text-xl font-semibold mb-2">Пока нет лекций</h2>
    <p class="max-w-sm mx-auto mb-6" style="color:var(--muted)">
      Загрузите запись лекции — и получите структурированный конспект со схемами уже через пару минут.
    </p>
    <a href="{{ route('lectures.create') }}" class="btn btn-accent">Загрузить первую лекцию →</a>
  </div>
@else
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 stagger">
    @foreach ($lectures as $lecture)
      @php
        $st = $lecture->status->value;
        $isLive = in_array($st, ['pending','transcribing','summarizing','rendering']);
      @endphp
      <a href="{{ route('lectures.show', $lecture) }}" style="--i:{{ $loop->index }}"
         class="card p-5 block group transition-all hover:-translate-y-1"
         onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='var(--shadow-sm)'">
        <div class="flex items-start justify-between gap-3 mb-4">
          <span class="w-10 h-10 rounded-xl grid place-items-center text-lg flex-none"
                style="background:var(--accent-soft); color:var(--accent)">
            {{ $st === 'done' ? '📄' : ($st === 'failed' ? '⚠' : '◴') }}
          </span>
          @if ($st === 'done')
            <span class="pill pill-ok"><span class="dot dot-ok"></span> Готово</span>
          @elseif ($st === 'failed')
            <span class="pill" style="background:var(--danger-soft); color:var(--danger)">Ошибка</span>
          @else
            <span class="pill pill-accent"><span class="dot dot-live"></span> {{ $lecture->status->label() }}</span>
          @endif
        </div>

        <h3 class="font-semibold leading-snug mb-1 line-clamp-2 group-hover:text-[var(--accent)] transition-colors">
          {{ $lecture->title }}
        </h3>
        <div class="text-sm tabular-nums" style="color:var(--muted)">{{ $lecture->created_at->format('d.m.Y · H:i') }}</div>

        @if ($isLive)
          <div class="mt-4 h-1.5 rounded-full overflow-hidden" style="background:var(--soft)">
            <div class="h-full rounded-full transition-all" style="width:{{ max(5,$lecture->progress) }}%; background:var(--accent)"></div>
          </div>
        @endif
      </a>
    @endforeach
  </div>
@endif

</x-lectura-layout>
