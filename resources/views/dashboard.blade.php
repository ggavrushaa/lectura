<x-lectura-layout title="Мои лекции">
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-semibold">Мои лекции</h1>
  <a href="{{ route('lectures.create') }}" class="px-4 py-2 rounded-xl text-white font-medium" style="background:var(--ink)">+ Новая лекция</a>
</div>

@if ($lectures->isEmpty())
  <div class="rounded-2xl p-12 border text-center" style="background:var(--panel);border-color:var(--line)">
    <p style="color:var(--muted)">Пока нет лекций. Загрузите первую запись.</p>
  </div>
@else
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach ($lectures as $lecture)
      <a href="{{ route('lectures.show', $lecture) }}" class="rounded-2xl p-5 border block" style="background:var(--panel);border-color:var(--line)">
        <h3 class="font-medium mb-1">{{ $lecture->title }}</h3>
        <div class="text-sm" style="color:var(--muted)">{{ $lecture->created_at->format('d.m.Y') }}</div>
        <span class="inline-block mt-3 text-xs font-medium px-2.5 py-1 rounded-full"
              style="background:var(--accent-soft);color:var(--accent)">{{ $lecture->status->label() }}</span>
      </a>
    @endforeach
  </div>
@endif
</x-lectura-layout>
