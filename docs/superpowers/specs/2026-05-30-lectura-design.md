# Lectura — дизайн и спецификация

**Дата:** 2026-05-30
**Статус:** на ревью пользователя
**Локальный домен (Herd):** `lectura.test`
**Папка проекта:** `/Users/h.nikitin/Herd/lectura`

## 1. Суть продукта

Веб-сервис, который превращает аудиозапись лекции (с диктофона) в качественный,
красиво оформленный конспект. Пользователь загружает аудиофайл — получает
структурированный конспект со схемами и скачивает его в PDF, Word или Markdown.
Цель — не слушать лекцию заново, а быстро получить готовый конспект.

### Ключевые решения (согласовано с пользователем)

| Вопрос | Решение |
|---|---|
| Распознавание речи (STT) | **Groq Whisper** (`whisper-large-v3`) — дёшево, быстро, хорошо по-русски |
| Составление конспекта (LLM) | **OpenRouter** (модель настраивается через `.env`) |
| Пользователи | **Регистрация и личные кабинеты** (Laravel Breeze) |
| Форматы выгрузки | **PDF** (оформленный), **Word/DOCX**, **Markdown/копирование** |
| Богатство конспекта | **Структурный текст + схемы** (Mermaid), без генерации картинок |
| Дизайн | **Премиальный минимализм**, светлая + тёмная тема, акцент — **амбра** (light `#c2620a`, dark `#f5a623`) |
| Название / домен | **Lectura** / `lectura.test` |

## 2. Технологический стек

- **Laravel 12** (PHP 8.4, Herd), создаётся через `composer create-project`
- **Аутентификация:** Laravel Breeze (Blade-стек)
- **Фронтенд:** Blade + **Tailwind CSS** + **Alpine.js** (лёгкая интерактивность),
  **Mermaid.js** для схем в браузере
- **БД:** **MySQL** (Herd предоставляет MySQL-сервис; расширение `pdo_mysql` присутствует).
  При скаффолде создаём БД `lectura` и прописываем подключение в `.env`.
  `summary_json` — нативный JSON-столбец MySQL; `transcript`/`markdown` — `longtext`.
- **Очереди:** **Laravel Horizon + Redis** (Herd предоставляет Redis).
  Драйвер `redis`, воркеры под управлением Horizon (`php artisan horizon`),
  дашборд `/horizon` для мониторинга (закрыт гейтом — только для аутентифицированных).
  Очередь `transcribe` (тяжёлая, 1–2 воркера) отделена от `default`.
  Драйвер подключения к Redis — `phpredis` если расширение есть, иначе `predis`.
  ⚠️ Horizon должен быть запущен (`php artisan horizon`), иначе лекции «зависнут»
  в `pending` (см. §11, риск Q-1).
- **Хранилище файлов:** локальный диск (`storage/app/private/audio`)
- **Аудио-препроцессинг:** `ffmpeg` (установлен) — конвертация в сжатый
  16 kHz mono и нарезка длинных файлов на сегменты
- **Рендер схем:** **`@mermaid-js/mermaid-cli` (`mmdc`)** — каждая Mermaid-схема
  один раз рендерится сервером в **SVG** (и в PNG для DOCX) на этапе обработки,
  результат сохраняется. Это снимает зависимость PDF от JS-таймингов в браузере
  и переиспользуется во всех трёх форматах (см. §11, риск D-1).
- **Экспорт PDF:** Spatie **Browsershot** (headless Chrome) рендерит печатный
  Blade-шаблон с уже **готовыми SVG** (без выполнения Mermaid во время снимка).
  Шрифты (Inter, Newsreader) **самохостятся** локально для детерминированного PDF.
  Путь к Chrome задаётся в конфиге (Herd/системный Chrome).
- **Экспорт DOCX:** **PhpOffice/PhpWord** строит документ из `summary_json`,
  схемы вставляются как **предрендеренные PNG**.
- **HTTP к внешним API:** встроенный Laravel `Http`-клиент с таймаутами и retry.

### Внешние ключи (`.env`)
```
GROQ_API_KEY=...
GROQ_STT_MODEL=whisper-large-v3
OPENROUTER_API_KEY=...
# Модель с большим контекстом (транскрипт 2-ч лекции ≈ 30-45k токенов).
# Точный id сверяем на openrouter.ai/models при настройке — он может меняться.
OPENROUTER_MODEL=google/gemini-2.0-flash-001   # дёшево + 1M контекст; настраивается
OPENROUTER_FALLBACK_MODEL=anthropic/claude-sonnet-4   # запасная, если основная недоступна
OPENROUTER_SITE_URL=https://lectura.test       # для заголовков OpenRouter

QUEUE_CONNECTION=redis
REDIS_CLIENT=predis                # phpredis-расширения нет → predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

**Предпосылки для Horizon+Redis — проверено, всё на месте:**
- ✅ Redis установлен (`/opt/homebrew/bin/redis-server`) и запущен (`redis-cli ping → PONG`).
- ✅ `pcntl` и `posix` включены в активном PHP 8.4 (`pcntl_fork`/`posix_kill` доступны,
  `disable_functions` пуст) — Horizon заработает без правок конфигурации.
- Ставим только composer-пакеты: `predis/predis` (клиент, т.к. расширения phpredis нет)
  и `laravel/horizon`.

## 3. Архитектура и поток данных

```
Пользователь → загрузка аудио
      │
      ▼
[LectureController@store]  создаёт Lecture(status=pending), сохраняет файл,
      │                    диспатчит job ProcessLectureJob
      ▼
[ProcessLectureJob] (в очереди)
   1. AudioPreparer      — ffmpeg: нормализация (16 kHz mono) + нарезка по тишине
                           (silencedetect) с перекрытием, чтобы не резать слова;
                           status = transcribing, progress растёт по сегментам
   2. TranscriptionService (Groq Whisper) — сегменты последовательно с throttle/
                           backoff (RPM-лимиты Groq) → текст, склейка с дедупом
                           перекрытий; transcript_text; status = summarizing
   3. SummaryService (OpenRouter) — промпт + транскрипт → структурированный JSON
                           (response_format=json_schema), парсинг + валидация +
                           1 repair-retry при невалидном JSON; status = rendering
   4. DiagramRenderer    — каждую diagram_mermaid валидирует через mmdc, рендерит
                           в SVG (+PNG); невалидные схемы выкидываются (graceful),
                           конспект не ломается; собирается summary_markdown;
                           status = done
   5. при ошибке         — status = failed, текст ошибки, лог; UI даёт «Повторить»
      │
      ▼
Фронтенд страницы лекции опрашивает статус (Alpine + polling каждые 3 с),
показывает прогресс, по готовности рендерит конспект.
```

### Изоляция модулей (одна ответственность на класс)

- **`AudioPreparer`** — вход: путь к файлу; выход: список путей сегментов (готовых
  для STT). Знает только про ffmpeg и лимиты размера. Тестируется на фиктивных файлах.
- **`TranscriptionService`** — вход: пути сегментов + язык; выход: единый транскрипт.
  Знает только про Groq API. Контракт: `transcribe(array $paths, ?string $lang): string`.
- **`SummaryService`** — вход: транскрипт + опции (подробность, схемы да/нет);
  выход: `LectureSummary` DTO (структура + markdown). Знает только про OpenRouter,
  формат промпта и валидацию/ремонт JSON. Контракт:
  `summarize(string $transcript, SummaryOptions $o): LectureSummary`.
- **`DiagramRenderer`** — вход: строка Mermaid; выход: пути SVG/PNG или `null`
  (если схема невалидна). Знает только про `mmdc`. Изолирует хрупкость Mermaid
  от остального конвейера.
- **`Exporters`** (`PdfExporter`, `DocxExporter`, `MarkdownExporter`) — вход:
  `Lecture`; выход: файл/строка. Используют уже готовые SVG/PNG схем.
- **`ProcessLectureJob`** — оркестратор: вызывает сервисы по очереди,
  обновляет статус/прогресс, ловит ошибки. Бизнес-логики не содержит.

Такое разбиение позволяет тестировать каждый сервис отдельно (мокая HTTP) и менять
внутренности (например, модель OpenRouter) без правок оркестратора.

## 4. Модель данных

### `users` (стандарт Breeze)
id, name, email, password, timestamps.

### `lectures`
| Поле | Тип | Описание |
|---|---|---|
| id | bigint | PK |
| user_id | FK → users | владелец |
| title | string | название (из имени файла, потом уточняется ИИ) |
| original_filename | string | исходное имя |
| audio_path | string | путь к файлу |
| duration_seconds | int nullable | длительность |
| language | string nullable | язык (`ru`/auto) |
| status | string | `pending`/`transcribing`/`summarizing`/`rendering`/`done`/`failed` (string + валидация через PHP enum-cast, чтобы не упираться в ALTER ENUM в MySQL) |
| progress | tinyint | 0–100 для индикатора |
| with_diagrams | bool | строить ли схемы |
| detail_level | string | `short`/`medium`/`detailed` |
| transcript_text | longtext nullable | расшифровка |
| summary_markdown | longtext nullable | готовый конспект (Markdown) |
| summary_json | json nullable | структура (разделы, термины, схемы + пути SVG/PNG) |
| error_message | text nullable | при сбое |
| timestamps | | |

Файлы схем хранятся на диске (`storage/app/private/lectures/{id}/diagrams/*.svg|png`),
а их пути кладутся в соответствующие `sections[].diagram_svg/diagram_png` внутри `summary_json`.

`summary_json` (контракт ответа LLM):
```json
{
  "title": "string",
  "summary": "string",
  "reading_time_min": 6,
  "sections": [{ "heading": "string", "content_markdown": "string",
                 "terms": ["string"], "diagram_mermaid": "string|null" }],
  "key_terms": ["string"],
  "takeaways": ["string"]
}
```

## 5. Экраны (по утверждённому макету v4)

1. **Главная** (`/`) — hero, как работает (4 шага), CTA. Публичная.
2. **Регистрация / Вход** (Breeze) — премиальные минималистичные формы.
3. **Кабинет** (`/dashboard`) — сетка карточек лекций со статусами, кнопка «Новая лекция».
4. **Загрузка** (`/lectures/create`) — drag&drop, выбор языка/подробности/схем.
5. **Лекция** (`/lectures/{id}`) — при обработке: прогресс-кольцо + чек-лист шагов
   (поллинг); по готовности: оглавление (TOC) слева, документ-конспект справа,
   панель экспорта (PDF/Word/Markdown), схемы Mermaid, выделенные термины.
6. **Тема** — переключатель светлая/тёмная (Alpine + `localStorage`, класс на `html`).

Дизайн: палитра и компоненты из `premium.html` (Inter + Newsreader, акцент — амбра
`#c2620a` / `#f5a623`, светлая/тёмная темы, мягкие границы, минимум теней).

## 6. Экспорт

- **Markdown** — берётся `summary_markdown`; кнопка «копировать» + скачать `.md`.
- **PDF** — Blade-шаблон `exports/pdf.blade.php` (печатная вёрстка) →
  Browsershot (headless Chrome) рендерит со схемами Mermaid → `.pdf`.
- **DOCX** — PhpWord строит документ из `summary_json`; каждая схема Mermaid
  заранее рендерится в PNG (headless Chrome) и вставляется картинкой.
  Если рендер схемы недоступен — вставляется текст/код схемы (graceful degradation).

## 7. Обработка ошибок и лимиты

- Валидация загрузки: расширения `mp3,m4a,wav,ogg` **+ реальная проверка через
  `ffprobe`** (расширение подделывается), максимум по размеру (конфиг, напр. 200 МБ),
  максимум длительности (напр. 2 часа, тоже из `ffprobe`).
- Длинные/тяжёлые файлы: `AudioPreparer` сжимает (16 kHz mono) и режет по тишине
  под лимит Groq (~25 МБ/сегмент); транскрипты склеиваются с дедупом перекрытий.
- Лимиты Groq (RPM/размер): последовательная отправка сегментов с throttle и
  экспоненциальным backoff при 429.
- Сбой внешнего API: job помечает `failed`, сохраняет сообщение, на странице —
  понятная ошибка и кнопка «Повторить» (сбрасывает статус/прогресс, без дублей).
- Job: `tries=3`, `backoff`, большой `timeout` (длинные лекции = минуты обработки).
- **Очистка:** после успешной обработки исходный аудиофайл удаляется (конфиг
  `LECTURA_KEEP_AUDIO`), чтобы не забивать диск; SVG/PNG схем остаются.
- **Приватность:** аудио уходит в Groq, транскрипт — в OpenRouter (третьи стороны);
  явно указываем это на странице загрузки. Ключи только в `.env`, не коммитятся.
- **Защита от перерасхода:** мягкий лимит на число лекций в обработке на пользователя
  (конфиг), чтобы один аккаунт не сжёг все API-кредиты.

## 8. Тестирование (TDD)

- **Unit:** `AudioPreparer` (логика нарезки на фиктивных длительностях),
  `SummaryService` парсинг JSON-ответа (мок HTTP), `MarkdownExporter`.
- **Feature:** загрузка лекции создаёт запись + диспатчит job (`Queue::fake`);
  доступ к чужой лекции запрещён (политика владельца); страница статуса отдаёт
  корректный JSON прогресса; экспорт-роуты возвращают нужный `Content-Type`.
- **Интеграция внешних API** мокается (`Http::fake`) — без реальных вызовов в тестах.

## 9. Этапы реализации (для плана)

1. Скаффолд Laravel + Herd (`lectura.test`), git init, **MySQL** (создать БД `lectura`),
   Breeze (auth). Redis уже запущен → `composer require predis/predis laravel/horizon`,
   `horizon:install`, `QUEUE_CONNECTION=redis`, гейт на `/horizon`.
2. Тема (светлая/тёмная) + базовый layout/дизайн-система из макета.
3. Модель `Lecture` + миграция + политика владельца.
4. Загрузка аудио (форма, валидация, сохранение, создание записи).
5. `AudioPreparer` (ffmpeg: ffprobe-валидация, 16 kHz mono, нарезка по тишине) + тесты.
6. `TranscriptionService` (Groq, throttle/backoff) + тесты (мок).
7. `SummaryService` (OpenRouter, json_schema + валидация + repair-retry) + тесты (мок).
8. `DiagramRenderer` (mmdc → SVG/PNG, валидация, fallback) + тесты.
9. `ProcessLectureJob` (оркестрация, статусы, прогресс, ошибки) на очереди Redis/Horizon.
10. Страница лекции: прогресс (поллинг) + рендер конспекта (TOC, термины, готовые SVG).
11. Кабинет (список лекций) + мягкий лимит на параллельную обработку.
12. Экспорт: Markdown → PDF (Browsershot, самохост-шрифты) → DOCX (PhpWord).
13. Главная (лендинг) + полировка дизайна, пустые состояния, ошибки, очистка аудио.
14. Прогон: реальная лекция от загрузки до экспорта + проверка `/horizon`.

## 11. Devil's advocate: риски и решения

Критический разбор спеки — что может сломаться и как это закрыто.

### Очереди / инфраструктура (Horizon + Redis)
- **Q-1. Horizon не запущен → лекции вечно `pending`.** Главный операционный риск
  (остаётся актуальным). Решение: явная инструкция `php artisan horizon`; на странице
  лекции — детектор «застряла» (если `updated_at` давно не менялся) с подсказкой;
  в README — запуск.
- **Q-2. `pcntl`/`posix` для Horizon.** ✅ Проверено — уже включены в активном PHP 8.4,
  правок не требуется. **Fallback** на случай иной машины: `redis` + `queue:work`
  (без pcntl), без дашборда.
- **Q-3. Redis.** ✅ Проверено — установлен и запущен (`PONG`). Fallback на иной
  машине: `brew install redis` или деградация на `QUEUE_CONNECTION=database`.

### Внешние AI-API
- **A-1. Контекст LLM на 2-часовой лекции (~30–45k токенов).** Решение: дефолт —
  модель с большим контекстом (Gemini 2.0 Flash 1M); id сверяем на старте.
- **A-2. LLM возвращает невалидный JSON / обёртки в markdown.** Решение:
  `response_format=json_schema`, строгий парсинг, 1 repair-retry, иначе `failed`.
- **A-3. Лимиты Groq (RPM, 25 МБ/запрос).** Решение: сжатие 16 kHz mono, нарезка
  по тишине, последовательная отправка с throttle + backoff на 429.
- **A-4. Стоимость/злоупотребление.** Решение: лимиты размера/длительности +
  мягкий лимит на число одновременных обработок на пользователя.

### Mermaid / экспорт
- **D-1. LLM генерирует синтаксически битый Mermaid → ломает страницу/PDF.**
  Главный риск качества. Решение: `DiagramRenderer` валидирует через `mmdc`
  на этапе обработки; битые схемы выкидываются (конспект остаётся целым).
- **D-2. PDF через «живой» Mermaid в Chrome — гонки таймингов.** Решение:
  предрендер схем в SVG **до** PDF; Browsershot вставляет готовый SVG.
- **D-3. Кириллица/шрифты в PDF.** Решение: самохост Inter+Newsreader, путь к Chrome
  в конфиге; не зависим от внешних Google Fonts при рендере.
- **D-4. Нарезка аудио по фикс-времени режет слова на стыках.** Решение: нарезка
  по тишине (silencedetect) с перекрытием и дедупом на склейке.

### Данные / безопасность
- **S-1. Доступ к чужой лекции/экспорту.** Решение: `LecturePolicy` (owner-only)
  на show/status/export, feature-тесты на 403.
- **S-2. Подделка типа файла (расширение).** Решение: проверка через `ffprobe`.
- **S-3. Диск забивается аудио/файлами.** Решение: удаление исходника после
  успешной обработки (конфиг), хранение только лёгких SVG/PNG.
- **S-4. Приватность (аудио/текст уходят третьим сторонам).** Решение: явное
  уведомление на загрузке; ключи в `.env`.
- **S-5. MySQL `ALTER ENUM` болезнен.** Решение: `status`/`detail_level` — `string`
  + PHP enum-cast и валидация, без SQL-ENUM.

### Принятый компромисс
Horizon+Redis добавляет инфраструктурные зависимости (Redis, pcntl/posix) ради
наблюдаемости и управления воркерами. Если на машине это завести не удастся —
без потери функциональности откатываемся на `redis`+`queue:work` или `database`.

## 12. Вне рамок (YAGNI на старте)

- Оплата/тарифы (есть только в макете как пункт меню).
- Командный доступ, шаринг по ссылке.
- Генерация картинок (DALL·E) — только Mermaid-схемы.
- Экспорт в Google Docs (OAuth) — отложено; Markdown легко вставляется в Google Docs.
- Мобильное приложение.
