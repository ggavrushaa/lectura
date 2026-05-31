# Улучшение конспектов (глубина, таблицы, графики, UX) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Сделать «подробный» режим конспекта реально подробным, добавить сравнительные таблицы, графики числовых данных и улучшения UX чтения, не меняя схему DTO.

**Architecture:** Один усиленный LLM-проход — per-level инструкции в системном промпте + `max_tokens` по уровню из конфига. Таблицы и графики ложатся в существующие `content_markdown` (GFM-таблицы) и `diagram_mermaid` (Mermaid `pie`/`xychart-beta`). UX — фронтенд во вью + CSS. Схема `LectureSummary` и пайплайн `ProcessLectureJob` не меняются.

**Tech Stack:** Laravel 11, PHP 8.x, PHPUnit, Blade + Alpine.js, Tailwind/CSS (`resources/css/app.css`), OpenRouter (Gemini 2.0 Flash → Claude Sonnet fallback), Mermaid CLI 11.15.0, KaTeX.

**Спека:** `docs/superpowers/specs/2026-05-31-rich-conspectus-improvements-design.md`

---

## Файловая структура

| Файл | Ответственность | Изменение |
|---|---|---|
| `config/services.php` | конфиг провайдеров | + карта `openrouter.max_tokens` по уровню |
| `app/Services/SummaryService.php` | сборка промпта + вызов LLM | per-level detail-инструкции, передача `max_tokens`, инструкции по таблицам/графикам/выноске exam |
| `app/Support/ConspectusRenderer.php` | markdown→HTML + выноски | + callout-тип `exam` |
| `resources/css/app.css` | стили конспекта (web) | + CSS таблиц, + `.callout-exam` |
| `resources/views/exports/pdf.blade.php` | PDF-шаблон | + CSS таблиц, фикс имён классов выносок (`c-*`→`callout-*`) + `callout-exam` |
| `resources/views/lectures/show.blade.php` | страница готового конспекта | связь терминов с глоссарием, прогрессивное раскрытие разделов |

Тесты: `tests/Feature/SummaryServiceTest.php`, `tests/Unit/ConspectusRendererTest.php` (расширяем существующие).

**Команда запуска тестов:** `php artisan test` (или точечно `php artisan test --filter=ИмяТеста`).

---

## Task 1: max_tokens по уровню детализации в конфиге

**Files:**
- Modify: `config/services.php:44-50` (блок `openrouter`)

- [ ] **Step 1: Добавить карту max_tokens в конфиг openrouter**

В `config/services.php` внутри массива `'openrouter' => [ ... ]` (после ключа `'base'`) добавить:

```php
        'max_tokens' => [
            'short'    => (int) env('OPENROUTER_MAXTOK_SHORT', 2500),
            'medium'   => (int) env('OPENROUTER_MAXTOK_MEDIUM', 4500),
            'detailed' => (int) env('OPENROUTER_MAXTOK_DETAILED', 9000),
        ],
```

- [ ] **Step 2: Проверить, что конфиг читается**

Run: `php artisan tinker --execute="echo config('services.openrouter.max_tokens.detailed');"`
Expected: выводит `9000`

- [ ] **Step 3: Commit**

```bash
git add config/services.php
git commit -m "feat: лимиты max_tokens по уровню детализации конспекта"
```

---

## Task 2: SummaryService передаёт max_tokens по уровню

**Files:**
- Modify: `app/Services/SummaryService.php:13-50` (`summarize` + `call`)
- Test: `tests/Feature/SummaryServiceTest.php`

- [ ] **Step 1: Написать падающий тест на передачу max_tokens**

Добавить метод в `tests/Feature/SummaryServiceTest.php` (используем существующий хелпер `payload()` и стиль `Http::fake`):

```php
    public function test_sends_detailed_max_tokens(): void
    {
        config(['services.openrouter.key' => 'k']);
        config(['services.openrouter.max_tokens' => ['short' => 2500, 'medium' => 4500, 'detailed' => 9000]]);

        Http::fake(['openrouter.ai/*' => Http::response($this->payload([
            'title' => 'T', 'summary' => 'S', 'reading_time_min' => 1,
            'sections' => [], 'key_terms' => [], 'takeaways' => [],
        ]))]);

        app(SummaryService::class)->summarize('текст', new SummaryOptions(
            detailLevel: \App\Enums\DetailLevel::Detailed,
        ));

        Http::assertSent(fn ($req) => ($req->data()['max_tokens'] ?? null) === 9000);
    }
```

- [ ] **Step 2: Запустить тест — убедиться, что падает**

Run: `php artisan test --filter=test_sends_detailed_max_tokens`
Expected: FAIL (в теле запроса нет `max_tokens`)

- [ ] **Step 3: Расширить `call()` параметром maxTokens и прокинуть его из `summarize()`**

В `app/Services/SummaryService.php` изменить сигнатуру `call` и тело запроса:

```php
    private function call(array $messages, ?int $maxTokens = null): string
    {
        $payload = [
            'model' => config('services.openrouter.model'),
            'models' => [config('services.openrouter.fallback')],
            'messages' => $messages,
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
        ];
        if ($maxTokens !== null) {
            $payload['max_tokens'] = $maxTokens;
        }

        $response = $this->client()->post('/chat/completions', $payload);

        $response->throw();

        return (string) ($response->json('choices.0.message.content') ?? '');
    }
```

В `summarize()` вычислить лимит и передать его в оба вызова `call()`:

```php
    public function summarize(string $transcript, SummaryOptions $options): LectureSummary
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt($options)],
            ['role' => 'user', 'content' => $this->userPrompt($transcript)],
        ];

        $maxTokens = config('services.openrouter.max_tokens.'.$options->detailLevel->value);
        $maxTokens = is_numeric($maxTokens) ? (int) $maxTokens : null;

        $content = $this->call($messages, $maxTokens);
        $data = $this->extractJson($content);

        if ($data === null) {
            // repair-retry: просим вернуть строго JSON
            $messages[] = ['role' => 'assistant', 'content' => $content];
            $messages[] = ['role' => 'user', 'content' => 'Верни ТОЛЬКО валидный JSON по схеме, без пояснений и markdown-ограждений.'];
            $data = $this->extractJson($this->call($messages, $maxTokens));
        }

        if ($data === null) {
            throw new RuntimeException('OpenRouter вернул невалидный JSON дважды');
        }

        return LectureSummary::fromArray($data);
    }
```

- [ ] **Step 4: Запустить тест — убедиться, что проходит**

Run: `php artisan test --filter=test_sends_detailed_max_tokens`
Expected: PASS

- [ ] **Step 5: Запустить весь SummaryServiceTest — регрессия**

Run: `php artisan test --filter=SummaryServiceTest`
Expected: PASS (существующие `test_parses_valid_json`, `test_repairs_once...` тоже зелёные)

- [ ] **Step 6: Commit**

```bash
git add app/Services/SummaryService.php tests/Feature/SummaryServiceTest.php
git commit -m "feat: SummaryService передаёт max_tokens по уровню детализации"
```

---

## Task 3: Per-level detail-инструкции в системном промпте

**Files:**
- Modify: `app/Services/SummaryService.php:68-74` (метод `systemPrompt`, переменная `$detail`)
- Test: `tests/Feature/SummaryServiceTest.php`

- [ ] **Step 1: Написать падающий тест на detailed-инструкцию**

Тест проверяет, что для уровня Detailed в system-промпт уходит инструкция про развёрнутость. Добавить в `tests/Feature/SummaryServiceTest.php`:

```php
    public function test_detailed_prompt_asks_for_more_depth(): void
    {
        config(['services.openrouter.key' => 'k']);

        Http::fake(['openrouter.ai/*' => Http::response($this->payload([
            'title' => 'T', 'summary' => 'S', 'reading_time_min' => 1,
            'sections' => [], 'key_terms' => [], 'takeaways' => [],
        ]))]);

        app(SummaryService::class)->summarize('текст', new SummaryOptions(
            detailLevel: \App\Enums\DetailLevel::Detailed,
        ));

        Http::assertSent(function ($req) {
            $system = $req->data()['messages'][0]['content'];
            return str_contains($system, 'Подробный конспект')
                && str_contains($system, 'пример')
                && str_contains($system, 'почему');
        });
    }
```

- [ ] **Step 2: Запустить тест — убедиться, что падает**

Run: `php artisan test --filter=test_detailed_prompt_asks_for_more_depth`
Expected: FAIL (текущая detailed-строка не содержит «почему»)

- [ ] **Step 3: Заменить переменную `$detail` на развёрнутые per-level инструкции**

В `app/Services/SummaryService.php`, метод `systemPrompt`, заменить блок `$detail = match (...)`:

```php
        $detail = match ($o->detailLevel->value) {
            'short' => 'Сжатый конспект: 3–5 разделов, только ключевые мысли тезисно.',
            'detailed' => 'Подробный конспект: раскрой каждую смысловую тему отдельным '
                .'разделом (не экономь на их числе, ориентир 8–14). В каждом разделе — '
                .'развёрнутое объяснение, конкретный пример и, где уместно, замечание, '
                .'почему это важно. Глоссарий и вопросы для самопроверки — расширенные '
                .'(квиз 5–7 вопросов).',
            default => 'Сбалансированный конспект: 5–8 разделов, по абзацу с пояснениями и списками.',
        };
```

- [ ] **Step 4: Запустить тест — убедиться, что проходит**

Run: `php artisan test --filter=test_detailed_prompt_asks_for_more_depth`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Services/SummaryService.php tests/Feature/SummaryServiceTest.php
git commit -m "feat: развёрнутые per-level инструкции детализации конспекта"
```

---

## Task 4: Инструкции по таблицам и графикам в промпте

**Files:**
- Modify: `app/Services/SummaryService.php:79-114` (heredoc `PROMPT` в `systemPrompt`)
- Test: `tests/Feature/SummaryServiceTest.php`

- [ ] **Step 1: Написать падающий тест на инструкции таблиц/графиков**

Добавить в `tests/Feature/SummaryServiceTest.php`:

```php
    public function test_prompt_includes_table_and_chart_instructions(): void
    {
        config(['services.openrouter.key' => 'k']);

        Http::fake(['openrouter.ai/*' => Http::response($this->payload([
            'title' => 'T', 'summary' => 'S', 'reading_time_min' => 1,
            'sections' => [], 'key_terms' => [], 'takeaways' => [],
        ]))]);

        app(SummaryService::class)->summarize('текст', new SummaryOptions());

        Http::assertSent(function ($req) {
            $system = $req->data()['messages'][0]['content'];
            return str_contains($system, 'таблиц')
                && str_contains($system, 'pie')
                && str_contains($system, 'xychart');
        });
    }
```

- [ ] **Step 2: Запустить тест — убедиться, что падает**

Run: `php artisan test --filter=test_prompt_includes_table_and_chart_instructions`
Expected: FAIL

- [ ] **Step 3: Добавить инструкцию по таблицам в блок «Дополнительно»**

В heredoc `PROMPT`, в список «Дополнительно» (после строки про формулы, до строки про `glossary`), добавить пункт:

```
        - Где в материале есть сравнение (методы, подходы, плюсы/минусы,
          классификация, хронология, параметры) — оформляй его Markdown-таблицей
          GFM: строка заголовков «| Критерий | A | B |» и разделитель
          «| --- | --- | --- |». Таблицу используй только когда сравнение по
          двум и более параметрам нагляднее текста.
```

- [ ] **Step 4: Расширить инструкцию `$diagrams` графиками данных**

В `systemPrompt`, в ветке «диаграммы включены» переменной `$diagrams`, заменить значение на:

```php
        $diagrams = $o->withDiagrams
            ? 'Где это уместно, добавляй в раздел поле diagram_mermaid с КОРРЕКТНОЙ '
                .'диаграммой Mermaid. Для процессов/связей — graph/flowchart/sequence '
                .'(идентификаторы узлов латиницей, подписи в кавычках). Для числовых '
                .'данных — pie (доли целого: «pie title ... » и строки «"Метка" : 42») '
                .'или xychart-beta (динамика/сравнение величин, bar или line). Числа '
                .'бери ТОЛЬКО из материала лекции, не выдумывай; если точных данных нет '
                .'— diagram_mermaid = null. Если схема не нужна — null.'
            : 'Не добавляй диаграммы (diagram_mermaid всегда null).';
```

- [ ] **Step 5: Запустить тест — убедиться, что проходит**

Run: `php artisan test --filter=test_prompt_includes_table_and_chart_instructions`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add app/Services/SummaryService.php tests/Feature/SummaryServiceTest.php
git commit -m "feat: инструкции по сравнительным таблицам и графикам данных в промпте"
```

---

## Task 5: Выноска «К экзамену» в ConspectusRenderer

**Files:**
- Modify: `app/Support/ConspectusRenderer.php:13-18` (константа `CALLOUTS`)
- Test: `tests/Unit/ConspectusRendererTest.php`

- [ ] **Step 1: Написать падающий тест на callout exam**

Добавить в `tests/Unit/ConspectusRendererTest.php`:

```php
    public function test_renders_exam_callout(): void
    {
        $html = ConspectusRenderer::html("> [!exam] Запомнить к зачёту\n\nДалее.");
        $this->assertStringContainsString('callout-exam', $html);
        $this->assertStringContainsString('К экзамену', $html);
        $this->assertStringContainsString('Запомнить к зачёту', $html);
    }
```

- [ ] **Step 2: Запустить тест — убедиться, что падает**

Run: `php artisan test --filter=test_renders_exam_callout`
Expected: FAIL (неизвестный тип `exam` падает в дефолт `callout-note`)

- [ ] **Step 3: Добавить тип `exam` в карту CALLOUTS**

В `app/Support/ConspectusRenderer.php` в константу `CALLOUTS` добавить строку:

```php
        'exam' => ['★', 'К экзамену', 'callout-exam'],
```

- [ ] **Step 4: Запустить тест — убедиться, что проходит**

Run: `php artisan test --filter=ConspectusRendererTest`
Expected: PASS (новый + существующие тесты)

- [ ] **Step 5: Commit**

```bash
git add app/Support/ConspectusRenderer.php tests/Unit/ConspectusRendererTest.php
git commit -m "feat: выноска [!exam] «К экзамену» в ConspectusRenderer"
```

---

## Task 6: CSS таблиц и выноски exam (web)

**Files:**
- Modify: `resources/css/app.css` (блок `.prose-content` ~74-92 и блок выносок ~92-108)

Визуальная задача — без юнит-теста; проверка глазами в Task 9.

- [ ] **Step 1: Добавить CSS таблиц в блок `.prose-content`**

В `resources/css/app.css` после строки `.prose-content blockquote{ ... }` (около строки 90) добавить:

```css
/* ---------- Таблицы в конспекте ---------- */
.prose-content table{ width:100%; border-collapse:collapse; margin:1em 0; font-size:.95em; display:block; overflow-x:auto; }
.prose-content thead th{ background:var(--inset); text-align:left; font-weight:600; color:var(--ink); }
.prose-content th, .prose-content td{ border:1px solid var(--line); padding:.5rem .7rem; vertical-align:top; }
.prose-content tbody tr:nth-child(even){ background:color-mix(in oklab,var(--inset) 50%,transparent); }
```

- [ ] **Step 2: Добавить CSS выноски `.callout-exam`**

В `resources/css/app.css` после блока `.callout-tip ...` (около строки 108) добавить:

```css
.callout-exam{ background:color-mix(in oklab,var(--accent) 6%,var(--panel)); border-color:color-mix(in oklab,#7c5cff 35%,transparent); }
.callout-exam .callout-ic{ color:#7c5cff; }
```

- [ ] **Step 3: Собрать ассеты**

Run: `npm run build`
Expected: сборка без ошибок, обновлённый `public/build`

- [ ] **Step 4: Commit**

```bash
git add resources/css/app.css public/build
git commit -m "feat: CSS сравнительных таблиц и выноски «К экзамену» (web)"
```

---

## Task 7: PDF — CSS таблиц + фикс имён классов выносок + exam

**Files:**
- Modify: `resources/views/exports/pdf.blade.php:25-31` (CSS выносок) и `:42` (конец `<style>`)
- Test: `tests/Feature/PdfExportTest.php` (проверить, что существующий тест зелёный после правок)

- [ ] **Step 1: Привести имена классов выносок к `callout-*` и добавить exam**

В `resources/views/exports/pdf.blade.php` заменить блок CSS выносок (строки с `.c-important` … `.c-tip .clbl`) на:

```css
  .callout-important { background:#fbeae8; border-color:#e8b9b3; } .callout-important .clbl { color:#c0392b; }
  .callout-note { background:#fdf3e7; border-color:#f0ddc2; } .callout-note .clbl { color:#a85408; }
  .callout-example { background:#f7f7f5; } .callout-example .clbl { color:#666; }
  .callout-tip { background:#eaf6f1; border-color:#bfe3d4; } .callout-tip .clbl { color:#2e8b6f; }
  .callout-exam { background:#f3efff; border-color:#c9bbff; } .callout-exam .clbl { color:#6b46d9; }
```

> Примечание: `ConspectusRenderer` отдаёт `<div class="callout callout-...">` с
> `<strong>` как меткой (не `.clbl`). Метка получает стиль из общего
> `.callout .callout-body strong` в web, в PDF — наследует жирность; цвет метки
> через `.clbl` исторический. Достаточно совпадения класса контейнера
> `callout-*` для фона/рамки. Цвет текста метки в PDF не критичен.

- [ ] **Step 2: Добавить CSS таблиц в `<style>` PDF**

В `resources/views/exports/pdf.blade.php` перед закрывающим `</style>` (после строки `.foot { ... }`) добавить:

```css
  table { width:100%; border-collapse:collapse; margin:9pt 0; font-size:10pt; page-break-inside:avoid; }
  th, td { border:1px solid #d9d9d4; padding:4pt 6pt; text-align:left; vertical-align:top; }
  thead th { background:#f1f1ee; font-weight:bold; }
```

- [ ] **Step 3: Запустить PDF-тест — регрессия**

Run: `php artisan test --filter=PdfExportTest`
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add resources/views/exports/pdf.blade.php
git commit -m "feat: PDF — таблицы, фикс имён классов выносок, выноска «К экзамену»"
```

---

## Task 8: UX — связь терминов с глоссарием + прогрессивное раскрытие

**Files:**
- Modify: `resources/views/lectures/show.blade.php:67-162` (блок готового конспекта)
- Test: `tests/Feature/LectureShowTest.php` (проверить, что страница рендерится без ошибок)

- [ ] **Step 1: Построить карту term→slug и сделать пилюли-термины ссылками**

В `resources/views/lectures/show.blade.php`, в начале блока `@else` (готовый конспект), внутри `@php ... @endphp` около строки 69-74, добавить построение карты глоссария:

```php
      $glossSlugs = [];
      foreach (($sj['glossary'] ?? []) as $g) {
        if (!empty($g['term'])) {
          $glossSlugs[mb_strtolower(trim($g['term']))] = \Illuminate\Support\Str::slug($g['term']) ?: md5($g['term']);
        }
      }
```

Затем заменить блок пилюль-терминов раздела (строки 150-154) на:

```blade
            @if (!empty($section['terms']))
              <div class="flex flex-wrap gap-1.5 mt-4">
                @foreach ($section['terms'] as $term)
                  @php $tslug = $glossSlugs[mb_strtolower(trim($term))] ?? null; @endphp
                  @if ($tslug)
                    <a href="#glos-{{ $tslug }}" class="pill pill-muted" style="text-decoration:none">{{ $term }}</a>
                  @else
                    <span class="pill pill-muted">{{ $term }}</span>
                  @endif
                @endforeach
              </div>
            @endif
```

- [ ] **Step 2: Дать id и scroll-mt глоссарий-карточкам**

Заменить открытие карточки глоссария (строка 170) на вариант с `id` и отступом для якорного скролла:

```blade
              @foreach ($sj['glossary'] as $g)
                <div id="glos-{{ \Illuminate\Support\Str::slug($g['term'] ?? '') ?: md5($g['term'] ?? '') }}" class="rounded-xl p-4 scroll-mt-24" style="background:var(--inset); border:1px solid var(--line)">
                  <dt class="font-semibold mb-0.5">{{ $g['term'] ?? '' }}</dt>
                  <dd class="text-sm" style="color:var(--muted)">{{ $g['definition'] ?? '' }}</dd>
                </div>
              @endforeach
```

- [ ] **Step 3: Прогрессивное раскрытие длинного раздела**

Заменить обёртку контента раздела (строка 148) на Alpine-вариант со сворачиванием по высоте:

```blade
            <div x-data="{ exp:false, tall:false }"
                 x-init="$nextTick(() => tall = $refs.body.scrollHeight > 560)"
                 class="relative">
              <div x-ref="body" class="prose-content"
                   :style="tall && !exp ? 'max-height:520px;overflow:hidden' : ''">{!! \App\Support\ConspectusRenderer::html($section['content_markdown'] ?? '') !!}</div>
              <div x-show="tall && !exp" x-cloak class="absolute inset-x-0 bottom-0 h-24 pointer-events-none"
                   style="background:linear-gradient(to bottom, transparent, var(--panel))"></div>
              <button type="button" x-show="tall" x-cloak @click="exp = !exp"
                      class="mt-2 text-sm font-medium" style="color:var(--accent)">
                <span x-show="!exp">Читать дальше ⌄</span><span x-show="exp">Свернуть ⌃</span>
              </button>
            </div>
```

> Примечание: `var(--panel)` — фон карточки `.card`, совпадает с фоном статьи,
> поэтому fade выглядит как затухание текста. Если статья на ином фоне —
> заменить на соответствующую переменную фона `#doc`.

- [ ] **Step 4: Запустить тест страницы конспекта — регрессия**

Run: `php artisan test --filter=LectureShowTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/lectures/show.blade.php
git commit -m "feat: связь терминов с глоссарием и прогрессивное раскрытие разделов"
```

---

## Task 9: Полный прогон тестов + ручная проверка

**Files:** —

- [ ] **Step 1: Прогнать весь набор тестов**

Run: `php artisan test`
Expected: PASS (вся suite зелёная)

- [ ] **Step 2: Ручная проверка реальной лекции**

Загрузить лекцию в режиме «Подробно» с включёнными схемами. Проверить глазами на странице конспекта:
- разделов заметно больше, чем в «Средне»; есть примеры и пояснения;
- хотя бы одна сравнительная таблица отрендерилась с рамками/зеброй;
- график (`pie`/`xychart`) отрисовался как SVG (если в материале были числа);
- выноска «★ К экзамену» имеет фиолетовый акцент;
- клик по пилюле-термину скроллит к карточке глоссария;
- длинный раздел сворачивается с fade и кнопкой «Читать дальше».

Скачать PDF — проверить, что таблицы и выноски (включая exam) видны и
типизированы по цвету.

- [ ] **Step 3: Финальный коммит при необходимости (правки после ручной проверки)**

```bash
git add -A
git commit -m "fix: правки по итогам ручной проверки конспекта"
```

---

## Self-Review (выполнено при написании плана)

- **Покрытие спеки:** Ч.1 → Task 1–3; Ч.2 (таблицы) → Task 4 (промпт), 6 (web CSS), 7 (PDF CSS), MD/DOCX без изменений (по спеке); Ч.3 (графики) → Task 4; Ч.4.1 (термины) → Task 8; Ч.4.2 (exam + фикс PDF) → Task 5, 6, 7; Ч.4.3 (раскрытие) → Task 8. DOCX-таблицы осознанно вне объёма (спека).
- **Плейсхолдеры:** отсутствуют — код приведён в каждом шаге.
- **Согласованность имён:** класс выноски `callout-exam` и метка «К экзамену» едины в Task 5/6/7; ключи `max_tokens` (`short/medium/detailed`) совпадают с `DetailLevel::value` в Task 1/2; slug глоссария (`Str::slug` + `md5`-фолбэк) идентичен в Step 1/2 Task 8.
