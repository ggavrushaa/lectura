<x-lectura-layout :title="$lecture->title">
@php
  $st = $lecture->status->value;
  $terminal = in_array($st, ['done','failed']);
  $steps = [
    ['transcribing','Расшифровка речи'],
    ['summarizing','Составление конспекта'],
    ['rendering','Генерация схем'],
    ['done','Готово'],
  ];
  $order = ['pending'=>0,'transcribing'=>1,'summarizing'=>2,'rendering'=>3,'done'=>4,'failed'=>0];
  $cur = $order[$st] ?? 0;
@endphp

<div id="lecture" data-status-url="{{ route('lectures.status', $lecture) }}" data-status-route="lectures.status" data-terminal="{{ $terminal ? '1':'0' }}">

  @if ($st === 'failed')
    {{-- ── Ошибка ── --}}
    <div class="max-w-xl mx-auto card p-10 text-center anim-scale-in">
      <div class="w-16 h-16 mx-auto mb-5 rounded-2xl grid place-items-center text-3xl" style="background:var(--danger-soft); color:var(--danger)">⚠</div>
      <h1 class="text-xl font-semibold mb-2">Не удалось обработать лекцию</h1>
      <p class="text-sm mb-1" style="color:var(--muted)">{{ $lecture->error_message ?: 'Произошла ошибка при обработке.' }}</p>
      <div class="flex gap-3 justify-center mt-6">
        <form method="POST" action="{{ route('lectures.retry', $lecture) }}">@csrf
          <button class="btn btn-accent">↻ Повторить</button>
        </form>
        <a href="{{ route('dashboard') }}" class="btn btn-ghost">К списку</a>
      </div>
    </div>

  @elseif (! $terminal)
    {{-- ── В процессе ── --}}
    <div class="max-w-lg mx-auto card p-10 text-center anim-scale-in">
      <div class="relative w-28 h-28 mx-auto mb-6">
        <svg class="w-28 h-28 -rotate-90" viewBox="0 0 100 100">
          <circle cx="50" cy="50" r="44" fill="none" stroke="var(--soft)" stroke-width="7"/>
          <circle id="ring" cx="50" cy="50" r="44" fill="none" stroke="var(--accent)" stroke-width="7" stroke-linecap="round"
                  stroke-dasharray="276.5" stroke-dashoffset="{{ 276.5 * (1 - $lecture->progress/100) }}"
                  style="transition:stroke-dashoffset .6s ease"/>
        </svg>
        <div class="absolute inset-0 grid place-items-center">
          <span id="pct" class="text-2xl font-bold tabular-nums">{{ $lecture->progress }}%</span>
        </div>
      </div>

      <h1 class="text-lg font-semibold">Готовим ваш конспект…</h1>
      <p id="stage" class="text-sm mb-6" style="color:var(--muted)">{{ $lecture->status->label() }}</p>

      <div class="text-left max-w-xs mx-auto space-y-1">
        @foreach ($steps as $i => [$key,$label])
          @php $stepN = $i+1; @endphp
          <div class="flex items-center gap-3 py-1.5 text-sm" data-step="{{ $stepN }}">
            <span class="w-5 h-5 rounded-full grid place-items-center text-xs flex-none step-mark"
                  style="background:{{ $cur > $stepN ? 'var(--ok-soft)' : ($cur === $stepN ? 'var(--accent)' : 'var(--soft)') }}; color:{{ $cur > $stepN ? 'var(--ok)' : ($cur === $stepN ? '#fff' : 'var(--muted)') }}">
              {{ $cur > $stepN ? '✓' : ($cur === $stepN ? '•' : $stepN) }}
            </span>
            <span class="step-label" style="color:{{ $cur >= $stepN ? 'var(--ink)' : 'var(--muted)' }}">{{ $label }}</span>
          </div>
        @endforeach
      </div>

      <p class="text-xs mt-7 pt-5" style="color:var(--muted); border-top:1px solid var(--line)">
        Можно закрыть страницу — обработка продолжится в фоне.
      </p>
    </div>

  @else
    {{-- ── Готовый конспект ── --}}
    @php
      $sj = $lecture->summary_json ?? [];
      $hasFormulas = false;
      foreach (($sj['sections'] ?? []) as $s) { if (preg_match('/\$.+\$|\\\\\(|\\\\\[/', $s['content_markdown'] ?? '')) { $hasFormulas = true; break; } }
      $levelLabels = ['intro' => 'Вводный', 'intermediate' => 'Средний', 'advanced' => 'Продвинутый'];
    @endphp

    <div id="read-progress"></div>

    <div class="grid lg:grid-cols-[260px_1fr] gap-6 lg:gap-8 anim-fade-up">

      {{-- Sticky-сайдбар: содержание + экспорт --}}
      <aside class="lg:sticky lg:top-24 h-max space-y-4">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm hover:underline" style="color:var(--muted)">← К списку</a>

        @if (!empty($sj['sections']))
          <div class="card p-4">
            <div class="text-xs font-semibold uppercase tracking-wider mb-2.5" style="color:var(--muted)">Содержание</div>
            <nav id="toc" class="space-y-0.5 text-sm">
              @foreach ($sj['sections'] as $i => $section)
                <a href="#sec-{{ $i }}" data-toc="sec-{{ $i }}" class="toc-link block px-2.5 py-1.5 rounded-lg transition-colors line-clamp-1" style="color:var(--ink2)">
                  {{ $section['heading'] ?? '—' }}
                </a>
              @endforeach
              @if (!empty($sj['quiz']))
                <a href="#sec-quiz" data-toc="sec-quiz" class="toc-link block px-2.5 py-1.5 rounded-lg transition-colors" style="color:var(--ink2)">Самопроверка</a>
              @endif
            </nav>
          </div>
        @endif

        <div class="card p-4">
          <div class="text-xs font-semibold uppercase tracking-wider mb-2.5" style="color:var(--muted)">Скачать</div>
          <div class="space-y-2">
            <a href="{{ route('lectures.export', [$lecture,'pdf']) }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors" style="border:1px solid var(--line)" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--line)'"><span>📄</span> PDF <span class="ml-auto text-xs" style="color:var(--muted)">оформленный</span></a>
            <a href="{{ route('lectures.export', [$lecture,'docx']) }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors" style="border:1px solid var(--line)" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--line)'"><span>📝</span> Word <span class="ml-auto text-xs" style="color:var(--muted)">.docx</span></a>
            <a href="{{ route('lectures.export', [$lecture,'md']) }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors" style="border:1px solid var(--line)" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--line)'"><span>⤓</span> Markdown <span class="ml-auto text-xs" style="color:var(--muted)">.md</span></a>
            <button type="button" data-copy-all class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors" style="border:1px solid var(--line)" onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--line)'"><span>⧉</span> <span data-copy-label>Копировать всё</span></button>
          </div>
        </div>
      </aside>

      {{-- Документ --}}
      <article id="doc" class="card p-6 sm:p-10 max-w-none">
        @php $meta = array_filter([
          $lecture->created_at->format('d.m.Y'),
          !empty($sj['reading_time_min']) ? '~'.$sj['reading_time_min'].' мин чтения' : null,
          !empty($sj['sections']) ? count($sj['sections']).' '.trans_choice('раздел|раздела|разделов', count($sj['sections'])) : null,
        ]); @endphp

        {{-- Мета-теги: предмет + уровень --}}
        @if (!empty($sj['subject']) || !empty($sj['level']))
          <div class="flex flex-wrap items-center gap-2 mb-4">
            @if (!empty($sj['subject']))<span class="pill pill-accent">{{ $sj['subject'] }}</span>@endif
            @if (!empty($sj['level']) && isset($levelLabels[$sj['level']]))<span class="pill pill-muted">{{ $levelLabels[$sj['level']] }} уровень</span>@endif
          </div>
        @endif

        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm mb-3 tabular-nums" style="color:var(--muted)">
          @foreach ($meta as $m)<span>{{ $m }}</span>@endforeach
        </div>

        <h1 class="font-serif-display text-3xl sm:text-4xl leading-tight mb-5">{{ $sj['title'] ?? $lecture->title }}</h1>

        @if (!empty($sj['summary']))
          <div class="rounded-xl p-5 mb-8" style="background:var(--accent-soft); border:1px solid var(--accent-line)">
            <div class="text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:var(--accent)">Кратко</div>
            <p style="color:var(--ink2)">{{ $sj['summary'] }}</p>
          </div>
        @endif

        @foreach (($sj['sections'] ?? []) as $i => $section)
          <section id="sec-{{ $i }}" class="group scroll-mt-24 {{ $loop->first ? '' : 'mt-10' }}">
            <div class="flex items-center justify-between gap-3 mb-3 pb-2" style="border-bottom:1px solid var(--line)">
              <h2 class="font-serif-display text-2xl">{{ $section['heading'] ?? '' }}</h2>
              <button type="button" class="copy-btn icon-btn flex-none" style="width:2rem;height:2rem"
                      title="Скопировать раздел"
                      data-copy-section="{{ base64_encode(($section['heading'] ?? '')."\n\n".($section['content_markdown'] ?? '')) }}">⧉</button>
            </div>
            <div class="prose-content">{!! \App\Support\ConspectusRenderer::html($section['content_markdown'] ?? '') !!}</div>

            @if (!empty($section['terms']))
              <div class="flex flex-wrap gap-1.5 mt-4">
                @foreach ($section['terms'] as $term)<span class="pill pill-muted">{{ $term }}</span>@endforeach
              </div>
            @endif

            @if (!empty($section['diagram_svg']) && is_file($section['diagram_svg']))
              <div class="my-5 rounded-xl p-5 overflow-x-auto" style="background:var(--inset); border:1px solid var(--line)">
                {!! file_get_contents($section['diagram_svg']) !!}
              </div>
            @endif
          </section>
        @endforeach

        {{-- Глоссарий --}}
        @if (!empty($sj['glossary']))
          <section class="mt-10">
            <h2 class="font-serif-display text-2xl mb-4 pb-2" style="border-bottom:1px solid var(--line)">Глоссарий</h2>
            <dl class="space-y-3">
              @foreach ($sj['glossary'] as $g)
                <div class="rounded-xl p-4" style="background:var(--inset); border:1px solid var(--line)">
                  <dt class="font-semibold mb-0.5">{{ $g['term'] ?? '' }}</dt>
                  <dd class="text-sm" style="color:var(--muted)">{{ $g['definition'] ?? '' }}</dd>
                </div>
              @endforeach
            </dl>
          </section>
        @elseif (!empty($sj['key_terms']))
          <section class="mt-10">
            <h2 class="font-serif-display text-2xl mb-3 pb-2" style="border-bottom:1px solid var(--line)">Ключевые термины</h2>
            <div class="flex flex-wrap gap-1.5">
              @foreach ($sj['key_terms'] as $term)<span class="pill pill-accent">{{ $term }}</span>@endforeach
            </div>
          </section>
        @endif

        @if (!empty($sj['takeaways']))
          <section class="mt-10">
            <h2 class="font-serif-display text-2xl mb-3 pb-2" style="border-bottom:1px solid var(--line)">Выводы</h2>
            <ul class="space-y-2">
              @foreach ($sj['takeaways'] as $t)
                <li class="flex items-start gap-2.5" style="color:var(--ink2)">
                  <span class="mt-1.5 dot dot-ok flex-none"></span><span>{{ $t }}</span>
                </li>
              @endforeach
            </ul>
          </section>
        @endif

        {{-- Вопросы для самопроверки --}}
        @if (!empty($sj['quiz']))
          <section id="sec-quiz" class="mt-10 scroll-mt-24" x-data="{ open: null }">
            <h2 class="font-serif-display text-2xl mb-4 pb-2" style="border-bottom:1px solid var(--line)">Вопросы для самопроверки</h2>
            <div class="space-y-2.5">
              @foreach ($sj['quiz'] as $qi => $q)
                <div class="rounded-xl overflow-hidden" style="border:1px solid var(--line)">
                  <button type="button" @click="open = (open === {{ $qi }} ? null : {{ $qi }})"
                          class="w-full flex items-start gap-3 p-4 text-left">
                    <span class="flex-none w-6 h-6 rounded-full grid place-items-center text-xs font-semibold tabular-nums" style="background:var(--accent-soft); color:var(--accent)">{{ $qi + 1 }}</span>
                    <span class="font-medium flex-1">{{ $q['question'] ?? '' }}</span>
                    <span class="flex-none text-sm transition-transform" :class="open === {{ $qi }} && 'rotate-180'" style="color:var(--muted)">⌄</span>
                  </button>
                  @if (!empty($q['answer']))
                    <div x-show="open === {{ $qi }}" x-collapse x-cloak class="px-4 pb-4 pl-13 text-sm" style="color:var(--ink2)">
                      <div class="rounded-lg p-3" style="background:var(--ok-soft)">
                        <span class="text-xs font-semibold uppercase tracking-wider" style="color:var(--ok)">Ответ</span>
                        <p class="mt-1">{{ $q['answer'] }}</p>
                      </div>
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          </section>
        @endif
      </article>
    </div>

    {{-- KaTeX для формул (только если они есть в конспекте) --}}
    @if ($hasFormulas)
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
      <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
      <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js"
              onload="renderMathInElement(document.getElementById('doc'),{delimiters:[{left:'$$',right:'$$',display:true},{left:'$',right:'$',display:false},{left:'\\\\(',right:'\\\\)',display:false},{left:'\\\\[',right:'\\\\]',display:true}],throwOnError:false})"></script>
    @endif

    <script>
    (function () {
      // Прогресс чтения
      const bar = document.getElementById('read-progress');
      const onScroll = () => {
        const h = document.documentElement.scrollHeight - window.innerHeight;
        bar.style.width = h > 0 ? (window.scrollY / h * 100) + '%' : '0';
      };
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();

      // Активный раздел в оглавлении
      const links = [...document.querySelectorAll('.toc-link')];
      const map = {};
      links.forEach(l => map[l.dataset.toc] = l);
      const secs = [...document.querySelectorAll('article section[id]')];
      if ('IntersectionObserver' in window && secs.length) {
        const io = new IntersectionObserver((entries) => {
          entries.forEach(e => {
            if (e.isIntersecting) {
              links.forEach(l => l.classList.remove('active'));
              if (map[e.target.id]) map[e.target.id].classList.add('active');
            }
          });
        }, { rootMargin: '-20% 0px -70% 0px' });
        secs.forEach(s => io.observe(s));
      }

      // Копирование разделов
      const flash = (btn, txt) => { const o = btn.textContent; btn.textContent = txt; setTimeout(() => btn.textContent = o, 1400); };
      document.querySelectorAll('[data-copy-section]').forEach(btn => {
        btn.addEventListener('click', async () => {
          try { await navigator.clipboard.writeText(atob(btn.dataset.copySection)); flash(btn, '✓'); } catch (e) {}
        });
      });
      const copyAll = document.querySelector('[data-copy-all]');
      if (copyAll) {
        copyAll.addEventListener('click', async () => {
          const parts = [...document.querySelectorAll('[data-copy-section]')].map(b => atob(b.dataset.copySection));
          try { await navigator.clipboard.writeText(parts.join('\n\n')); const lbl = copyAll.querySelector('[data-copy-label]'); const o = lbl.textContent; lbl.textContent = 'Скопировано ✓'; setTimeout(() => lbl.textContent = o, 1400); } catch (e) {}
        });
      }
    })();
    </script>
  @endif
</div>

@if (! $terminal && $st !== 'failed')
<script>
(function () {
  const el = document.getElementById('lecture');
  const ringLen = 276.5;
  let stopped = false;
  async function poll() {
    if (stopped) return;
    try {
      const r = await fetch(el.dataset.statusUrl, { headers: { 'Accept': 'application/json' } });
      if (!r.ok) throw new Error('status ' + r.status);
      const d = await r.json();
      const pct = document.getElementById('pct');
      const stage = document.getElementById('stage');
      const ring = document.getElementById('ring');
      if (pct) pct.textContent = d.progress + '%';
      if (stage) stage.textContent = d.label;
      if (ring) ring.style.strokeDashoffset = ringLen * (1 - d.progress / 100);
      if (d.status === 'done' || d.status === 'failed') { stopped = true; setTimeout(() => location.reload(), 600); return; }
    } catch (e) {
      const stage = document.getElementById('stage');
      if (stage) stage.textContent = 'Переподключение…';
    }
    setTimeout(poll, 3000);
  }
  setTimeout(poll, 3000);
})();
</script>
@endif
</x-lectura-layout>
