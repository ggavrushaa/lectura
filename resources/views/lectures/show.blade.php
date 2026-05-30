<x-lectura-layout :title="$lecture->title">
@php $terminal = in_array($lecture->status->value, ['done','failed']); @endphp

<div id="lecture" data-status-url="{{ route('lectures.status', $lecture) }}" data-status-route="lectures.status" data-terminal="{{ $terminal ? '1':'0' }}">

  @if ($lecture->status->value === 'failed')
    <div class="rounded-2xl p-6 border text-center" style="background:var(--panel);border-color:var(--line)">
      <h2 class="text-xl font-semibold mb-2">Не удалось обработать</h2>
      <p style="color:var(--muted)">{{ $lecture->error_message }}</p>
      <form method="POST" action="{{ route('lectures.retry', $lecture) }}" class="mt-4">@csrf
        <button class="px-4 py-2 rounded-xl text-white" style="background:var(--ink)">Повторить</button>
      </form>
    </div>
  @elseif (! $terminal)
    <div class="rounded-2xl p-10 border text-center" style="background:var(--panel);border-color:var(--line)">
      <div class="text-4xl font-semibold" id="pct">{{ $lecture->progress }}%</div>
      <h2 class="text-lg font-medium mt-3">Готовим ваш конспект…</h2>
      <p id="stage" style="color:var(--muted)">{{ $lecture->status->label() }}</p>
      <p class="text-xs mt-6" style="color:var(--muted)">
        Если прогресс долго не меняется — проверьте, что запущен <code>php artisan horizon</code>.
      </p>
    </div>
  @else
    <div class="grid md:grid-cols-[240px_1fr] gap-6">
      <aside class="rounded-2xl p-5 border h-max" style="background:var(--inset);border-color:var(--line)">
        <h3 class="text-xs uppercase tracking-wide mb-3" style="color:var(--muted)">Скачать</h3>
        <a href="{{ route('lectures.export', [$lecture,'md']) }}" class="block px-3 py-2 rounded-lg border mb-2" style="border-color:var(--line)">Markdown</a>
        <a href="{{ route('lectures.export', [$lecture,'pdf']) }}" class="block px-3 py-2 rounded-lg border mb-2" style="border-color:var(--line)">PDF</a>
        <a href="{{ route('lectures.export', [$lecture,'docx']) }}" class="block px-3 py-2 rounded-lg border" style="border-color:var(--line)">Word</a>
      </aside>

      <article class="rounded-2xl p-8 border max-w-none" style="background:var(--panel);border-color:var(--line)">
        <h1 class="font-serif-display text-3xl mb-2">{{ $lecture->summary_json['title'] ?? $lecture->title }}</h1>
        <div class="rounded-xl p-4 my-4" style="background:var(--accent-soft);border:1px solid var(--accent-line)">
          {{ $lecture->summary_json['summary'] ?? '' }}
        </div>

        @foreach (($lecture->summary_json['sections'] ?? []) as $section)
          <h2 class="font-serif-display text-2xl mt-8 mb-2">{{ $section['heading'] }}</h2>
          <div class="prose-content">{!! \Illuminate\Support\Str::markdown($section['content_markdown'] ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
          @if (!empty($section['diagram_svg']) && is_file($section['diagram_svg']))
            <div class="my-4 rounded-xl p-4" style="background:var(--inset)">
              {!! file_get_contents($section['diagram_svg']) !!}
            </div>
          @elseif (!empty($section['diagram_mermaid']))
            <pre class="mermaid my-4">{{ $section['diagram_mermaid'] }}</pre>
          @endif
        @endforeach

        @if (!empty($lecture->summary_json['takeaways']))
          <h2 class="font-serif-display text-2xl mt-8 mb-2">Выводы</h2>
          <ul class="list-disc pl-5">
            @foreach ($lecture->summary_json['takeaways'] as $t)<li>{{ $t }}</li>@endforeach
          </ul>
        @endif
      </article>
    </div>
  @endif
</div>

@if (! $terminal)
<script>
const el = document.getElementById('lecture');
const poll = async () => {
  const r = await fetch(el.dataset.statusUrl, {headers:{'Accept':'application/json'}});
  const d = await r.json();
  document.getElementById('pct').textContent = d.progress + '%';
  document.getElementById('stage').textContent = d.label;
  if (d.status === 'done' || d.status === 'failed') { location.reload(); return; }
  setTimeout(poll, 3000);
};
setTimeout(poll, 3000);
</script>
@endif
</x-lectura-layout>
