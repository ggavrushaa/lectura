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
- **БД:** SQLite (по умолчанию, расширения `sqlite3`/`pdo_sqlite` присутствуют)
- **Очереди:** драйвер `database`, воркер `php artisan queue:work` —
  обработка аудио идёт асинхронно
- **Хранилище файлов:** локальный диск (`storage/app/private/audio`)
- **Аудио-препроцессинг:** `ffmpeg` (установлен) — конвертация в сжатый
  16 kHz mono и нарезка длинных файлов на сегменты
- **Экспорт PDF:** Spatie **Browsershot** (headless Chrome через Puppeteer/npx) —
  рендерит Blade-вёрстку со схемами Mermaid в качественный PDF
- **Экспорт DOCX:** **PhpOffice/PhpWord**; схемы вставляются картинками,
  отрендеренными через тот же headless Chrome
- **HTTP к внешним API:** встроенный Laravel `Http`-клиент

### Внешние ключи (`.env`)
```
GROQ_API_KEY=...
GROQ_STT_MODEL=whisper-large-v3
OPENROUTER_API_KEY=...
OPENROUTER_MODEL=anthropic/claude-3.7-sonnet   # настраивается
```

## 3. Архитектура и поток данных

```
Пользователь → загрузка аудио
      │
      ▼
[LectureController@store]  создаёт Lecture(status=pending), сохраняет файл,
      │                    диспатчит job ProcessLectureJob
      ▼
[ProcessLectureJob] (в очереди)
   1. AudioPreparer      — ffmpeg: нормализация + нарезка на сегменты (если нужно)
                           status = transcribing
   2. TranscriptionService (Groq Whisper) — каждый сегмент → текст, склейка
                           сохраняет transcript_text; status = summarizing
   3. SummaryService (OpenRouter) — промпт + транскрипт → структурированный
                           JSON (заголовок, резюме, разделы, термины, схемы, выводы)
                           + готовый Markdown; status = done
   4. при ошибке         — status = failed, текст ошибки, лог
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
  выход: `LectureSummary` DTO (структура + markdown). Знает только про OpenRouter
  и формат промпта. Контракт: `summarize(string $transcript, SummaryOptions $o): LectureSummary`.
- **`Exporters`** (`PdfExporter`, `DocxExporter`, `MarkdownExporter`) — вход:
  `Lecture`; выход: файл/строка. Каждый знает только свой формат.
- **`ProcessLectureJob`** — оркестратор: вызывает три сервиса по очереди,
  обновляет статус, ловит ошибки. Бизнес-логики распознавания/суммаризации не содержит.

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
| status | enum | `pending`/`transcribing`/`summarizing`/`done`/`failed` |
| progress | tinyint | 0–100 для индикатора |
| with_diagrams | bool | строить ли схемы |
| detail_level | enum | `short`/`medium`/`detailed` |
| transcript_text | longtext nullable | расшифровка |
| summary_markdown | longtext nullable | готовый конспект (Markdown) |
| summary_json | json nullable | структура (разделы, термины, схемы) |
| error_message | text nullable | при сбое |
| timestamps | | |

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

- Валидация загрузки: типы `mp3,m4a,wav,ogg`, максимум по размеру (конфигурируемо,
  напр. 200 МБ), максимум длительности (напр. 2 часа).
- Длинные/тяжёлые файлы: `AudioPreparer` сжимает (16 kHz mono) и режет на сегменты
  под лимит Groq (~25 МБ/сегмент); транскрипты склеиваются.
- Сбой внешнего API: job помечает `failed`, сохраняет сообщение, на странице —
  понятная ошибка и кнопка «Повторить».
- Job: `tries=3`, разумный `backoff`, `timeout` с запасом на длинные лекции.
- Ключи только в `.env`; не коммитятся.

## 8. Тестирование (TDD)

- **Unit:** `AudioPreparer` (логика нарезки на фиктивных длительностях),
  `SummaryService` парсинг JSON-ответа (мок HTTP), `MarkdownExporter`.
- **Feature:** загрузка лекции создаёт запись + диспатчит job (`Queue::fake`);
  доступ к чужой лекции запрещён (политика владельца); страница статуса отдаёт
  корректный JSON прогресса; экспорт-роуты возвращают нужный `Content-Type`.
- **Интеграция внешних API** мокается (`Http::fake`) — без реальных вызовов в тестах.

## 9. Этапы реализации (для плана)

1. Скаффолд Laravel + Herd (`lectura.test`), git init, SQLite, Breeze (auth).
2. Тема (светлая/тёмная) + базовый layout/дизайн-система из макета.
3. Модель `Lecture` + миграция + политика владельца.
4. Загрузка аудио (форма, валидация, сохранение, создание записи).
5. `AudioPreparer` (ffmpeg) + тесты.
6. `TranscriptionService` (Groq) + тесты (мок).
7. `SummaryService` (OpenRouter) + контракт JSON + тесты (мок).
8. `ProcessLectureJob` (оркестрация, статусы, ошибки) + очередь.
9. Страница лекции: прогресс (поллинг) + рендер конспекта (TOC, термины, Mermaid).
10. Кабинет (список лекций).
11. Экспорт: Markdown → PDF (Browsershot) → DOCX (PhpWord).
12. Главная (лендинг) + полировка дизайна, пустые состояния, ошибки.
13. Прогон: реальная лекция от загрузки до экспорта.

## 10. Вне рамок (YAGNI на старте)

- Оплата/тарифы (есть только в макете как пункт меню).
- Командный доступ, шаринг по ссылке.
- Генерация картинок (DALL·E) — только Mermaid-схемы.
- Экспорт в Google Docs (OAuth) — отложено; Markdown легко вставляется в Google Docs.
- Мобильное приложение.
