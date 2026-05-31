<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
@php $levelLabels = ['intro' => 'Вводный', 'intermediate' => 'Средний', 'advanced' => 'Продвинутый']; @endphp
<style>
  @page { margin: 22mm 18mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; color:#1a1a1f; line-height:1.6; font-size:11.5pt; }
  .tags { margin-bottom: 8pt; }
  .tag { display:inline-block; font-size:8.5pt; font-weight:bold; padding:2pt 7pt; border-radius:20pt; margin-right:4pt; }
  .tag-accent { background:#fdf3e7; color:#a85408; }
  .tag-muted { background:#f1f1ee; color:#666; }
  .meta { color:#86868f; font-size:9.5pt; margin-bottom:6pt; }
  h1 { font-size:23pt; margin:0 0 8pt; line-height:1.2; }
  h2 { font-size:15pt; border-bottom:1.5px solid #e9e9e6; padding-bottom:4pt; margin-top:20pt; color:#16161a; }
  .summary { background:#fdf3e7; border:1px solid #f5e2c8; border-radius:8px; padding:10pt 12pt; margin:12pt 0; }
  .summary .lbl { font-size:8.5pt; font-weight:bold; text-transform:uppercase; letter-spacing:1pt; color:#a85408; }
  .diagram { margin:10pt 0; }
  .diagram svg { max-width:100%; height:auto; }
  ul, ol { padding-left:16pt; margin:6pt 0; }
  li { margin:3pt 0; }
  strong { color:#16161a; }
  code { font-family:'DejaVu Sans Mono', monospace; background:#f1f1ee; padding:1pt 3pt; border-radius:3px; font-size:.9em; }
  /* выноски */
  .callout { padding:8pt 11pt; border-radius:7px; margin:9pt 0; border:1px solid #e9e9e6; page-break-inside:avoid; }
  .callout .clbl { font-weight:bold; display:block; margin-bottom:2pt; }
  .callout-important { background:#fbeae8; border-color:#e8b9b3; } .callout-important .clbl { color:#c0392b; }
  .callout-note { background:#fdf3e7; border-color:#f0ddc2; } .callout-note .clbl { color:#a85408; }
  .callout-example { background:#f7f7f5; } .callout-example .clbl { color:#666; }
  .callout-tip { background:#eaf6f1; border-color:#bfe3d4; } .callout-tip .clbl { color:#2e8b6f; }
  .callout-exam { background:#f3efff; border-color:#c9bbff; } .callout-exam .clbl { color:#6b46d9; }
  /* глоссарий */
  .gloss { background:#f7f7f5; border:1px solid #e9e9e6; border-radius:7px; padding:8pt 11pt; margin:6pt 0; page-break-inside:avoid; }
  .gloss dt { font-weight:bold; }
  .gloss dd { margin:1pt 0 0; color:#555; font-size:10.5pt; }
  /* квиз */
  .quiz-item { margin:8pt 0; page-break-inside:avoid; }
  .quiz-q { font-weight:bold; }
  .quiz-a { background:#eaf6f1; border-radius:6px; padding:6pt 9pt; margin-top:3pt; font-size:10.5pt; }
  .quiz-a .lbl { font-size:8pt; font-weight:bold; text-transform:uppercase; color:#2e8b6f; letter-spacing:.5pt; }
  .foot { margin-top:24pt; padding-top:8pt; border-top:1px solid #e9e9e6; color:#aaa; font-size:8.5pt; text-align:center; }
  table { width:100%; border-collapse:collapse; margin:9pt 0; font-size:10pt; page-break-inside:avoid; }
  th, td { border:1px solid #d9d9d4; padding:4pt 6pt; text-align:left; vertical-align:top; }
  thead th { background:#f1f1ee; font-weight:bold; }
</style>
</head>
<body>
  @if (!empty($summary['subject']) || !empty($summary['level']))
    <div class="tags">
      @if (!empty($summary['subject']))<span class="tag tag-accent">{{ $summary['subject'] }}</span>@endif
      @if (!empty($summary['level']) && isset($levelLabels[$summary['level']]))<span class="tag tag-muted">{{ $levelLabels[$summary['level']] }} уровень</span>@endif
    </div>
  @endif

  <div class="meta">
    {{ $lecture->created_at->format('d.m.Y') }}@if(!empty($summary['reading_time_min'])) · ~{{ $summary['reading_time_min'] }} мин чтения @endif
  </div>

  <h1>{{ $summary['title'] ?? $lecture->title }}</h1>

  @if (!empty($summary['summary']))
    <div class="summary"><span class="lbl">Кратко</span><div>{{ $summary['summary'] }}</div></div>
  @endif

  @foreach (($summary['sections'] ?? []) as $section)
    <h2>{{ $section['heading'] }}</h2>
    <div>{!! \App\Support\ConspectusRenderer::html($section['content_markdown'] ?? '') !!}</div>
    @if (!empty($section['diagram_svg']) && is_file($section['diagram_svg']))
      <div class="diagram">{!! file_get_contents($section['diagram_svg']) !!}</div>
    @endif
  @endforeach

  @if (!empty($summary['glossary']))
    <h2>Глоссарий</h2>
    @foreach ($summary['glossary'] as $g)
      <dl class="gloss"><dt>{{ $g['term'] ?? '' }}</dt><dd>{{ $g['definition'] ?? '' }}</dd></dl>
    @endforeach
  @endif

  @if (!empty($summary['takeaways']))
    <h2>Выводы</h2>
    <ul>@foreach ($summary['takeaways'] as $t)<li>{{ $t }}</li>@endforeach</ul>
  @endif

  @if (!empty($summary['quiz']))
    <h2>Вопросы для самопроверки</h2>
    @foreach ($summary['quiz'] as $qi => $q)
      <div class="quiz-item">
        <div class="quiz-q">{{ $qi + 1 }}. {{ $q['question'] ?? '' }}</div>
        @if (!empty($q['answer']))<div class="quiz-a"><span class="lbl">Ответ</span><div>{{ $q['answer'] }}</div></div>@endif
      </div>
    @endforeach
  @endif

  <div class="foot">Сгенерировано в Lectura · {{ $lecture->created_at->format('d.m.Y') }}</div>
</body>
</html>
