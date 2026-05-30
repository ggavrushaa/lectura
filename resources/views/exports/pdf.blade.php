<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<style>
  @page { margin: 24mm 18mm; }
  body { font-family: 'DejaVu Sans', sans-serif; color:#16161a; line-height:1.55; font-size:12pt; }
  h1 { font-size:22pt; margin:0 0 6pt; }
  h2 { font-size:15pt; border-bottom:1px solid #e9e9e6; padding-bottom:4pt; margin-top:18pt; }
  .summary { background:#fdf3e7; border:1px solid #f5e2c8; border-radius:8px; padding:10pt; margin:10pt 0; }
  .diagram svg { max-width:100%; height:auto; }
  ul { padding-left:16pt; }
</style>
</head>
<body>
  <h1>{{ $summary['title'] ?? $lecture->title }}</h1>
  <div class="summary">{{ $summary['summary'] ?? '' }}</div>

  @foreach (($summary['sections'] ?? []) as $section)
    <h2>{{ $section['heading'] }}</h2>
    <div>{!! \Illuminate\Support\Str::markdown($section['content_markdown'] ?? '', ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
    @if (!empty($section['diagram_svg']) && is_file($section['diagram_svg']))
      <div class="diagram">{!! file_get_contents($section['diagram_svg']) !!}</div>
    @endif
  @endforeach

  @if (!empty($summary['takeaways']))
    <h2>Выводы</h2>
    <ul>@foreach ($summary['takeaways'] as $t)<li>{{ $t }}</li>@endforeach</ul>
  @endif
</body>
</html>
