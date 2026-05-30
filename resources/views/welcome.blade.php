<x-lectura-layout
    title="Lectura — конспект лекции из аудиозаписи за пару минут"
    description="Загрузите аудиозапись лекции, вебинара или урока — получите готовый структурированный конспект со схемами. Скачивайте в PDF, Word и Markdown. Для студентов, школьников и тех, кто учится по работе.">

{{-- ════════════════ HERO — асимметричный, с превью продукта ════════════════ --}}
<section class="relative pt-6 pb-12 sm:pt-10 sm:pb-20">
  <div class="grid lg:grid-cols-[1.05fr_0.95fr] gap-10 lg:gap-14 items-center">

    {{-- Левая колонка: оффер --}}
    <div class="stagger">
      <span style="--i:0" class="pill pill-accent"><span class="dot dot-live"></span> Конспект из аудио за пару минут</span>

      <h1 style="--i:1" class="text-4xl sm:text-5xl xl:text-6xl font-extrabold leading-[1.04] tracking-tight mt-5 mb-5">
        Прослушали лекцию —<br>
        получите
        <span class="ink-underline font-serif-display italic font-medium" style="color:var(--accent)">конспект<svg viewBox="0 0 200 12" preserveAspectRatio="none" fill="none"><path d="M2 9c40-6 120-7 196-3" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg></span>,
        а не стену текста
      </h1>

      <p style="--i:2" class="text-lg max-w-xl mb-7 leading-relaxed" style="color:var(--muted)">
        Записали пару, вебинар или тренинг на диктофон? Загрузите аудио — Lectura расшифрует речь
        и соберёт структурированный конспект с разделами, терминами и схемами. Без переслушивания часами.
      </p>

      <div style="--i:3" class="flex flex-wrap gap-3 items-center">
        @auth
          <a href="{{ route('lectures.create') }}" class="btn btn-accent text-base px-7 py-3.5">Загрузить аудио →</a>
          <a href="{{ route('dashboard') }}" class="btn btn-ghost text-base px-7 py-3.5">Мои лекции</a>
        @else
          <a href="{{ route('register') }}" class="btn btn-accent text-base px-7 py-3.5">Сделать конспект бесплатно →</a>
          <a href="#how" class="btn btn-ghost text-base px-7 py-3.5">Как это работает</a>
        @endauth
      </div>

      <div style="--i:4" class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-6 text-sm" style="color:var(--muted)">
        <span class="flex items-center gap-1.5"><span class="dot dot-ok"></span> Без установки</span>
        <span class="flex items-center gap-1.5"><span class="dot dot-ok"></span> Русская речь</span>
        <span class="flex items-center gap-1.5"><span class="dot dot-ok"></span> MP3 · M4A · WAV · OGG</span>
      </div>
    </div>

    {{-- Правая колонка: наклонённое окно с конспектом + плавающая «капсула загрузки» --}}
    <div class="relative anim-scale-in" style="animation-delay:.25s">
      <div aria-hidden="true" class="absolute -inset-6 -z-10 rounded-[2rem] blur-2xl opacity-50"
           style="background:radial-gradient(60% 60% at 70% 20%, color-mix(in oklab,var(--accent) 35%,transparent), transparent)"></div>

      <div class="preview-window">
        <div class="preview-chrome"><i></i><i></i><i></i>
          <span class="ml-2 text-xs" style="color:var(--muted)">lectura · Лекция 7. Нейронные сети</span>
        </div>
        <div class="p-6">
          <div class="text-xs mb-2 tabular-nums" style="color:var(--muted)">48 мин · ~6 мин чтения</div>
          <div class="font-serif-display text-2xl mb-3">Нейронные сети: основы</div>
          <div class="rounded-lg p-3 mb-4 text-sm" style="background:var(--accent-soft);border:1px solid var(--accent-line);color:var(--ink2)">
            <span style="color:var(--accent);font-weight:600">Кратко.</span> Модель из связанных нейронов; обучается подбором весов на данных.
          </div>
          <div class="text-sm font-semibold mb-1.5">1. Что такое нейрон</div>
          <div class="space-y-1.5 mb-4">
            <div class="h-2 rounded-full" style="background:var(--soft);width:96%"></div>
            <div class="h-2 rounded-full" style="background:var(--soft);width:88%"></div>
            <div class="h-2 rounded-full" style="background:var(--soft);width:70%"></div>
          </div>
          <div class="flex gap-1.5 flex-wrap">
            <span class="pill pill-muted">нейрон</span><span class="pill pill-muted">веса</span><span class="pill pill-muted">активация</span>
          </div>
        </div>
      </div>

      {{-- плавающая капсула «аудио загружено» --}}
      <div class="absolute -left-3 sm:-left-6 bottom-6 card px-4 py-3 flex items-center gap-3"
           style="box-shadow:var(--shadow-lg); animation:float-slow 6s ease-in-out infinite">
        <span class="w-9 h-9 rounded-lg grid place-items-center" style="background:var(--accent-soft);color:var(--accent)">♪</span>
        <div class="leading-tight">
          <div class="text-xs" style="color:var(--muted)">лекция.m4a</div>
          <div class="text-sm font-semibold flex items-center gap-1.5"><span class="dot dot-ok"></span> Готово</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ════════════════ Маркиза сценариев ════════════════ --}}
<section class="full-bleed py-6 border-y" style="border-color:var(--line)">
  <div class="marquee">
    <div class="marquee-track text-lg" style="color:var(--muted)">
      @foreach (['Лекции в универе','Вебинары','Корпоративные тренинги','Онлайн-курсы','Школьные уроки','Подкасты','Конференции','Инструктажи','Мастер-классы'] as $w)
        <span class="flex items-center gap-3 font-serif-display italic">{{ $w }} <span style="color:var(--accent)">✦</span></span>
      @endforeach
      @foreach (['Лекции в универе','Вебинары','Корпоративные тренинги','Онлайн-курсы','Школьные уроки','Подкасты','Конференции','Инструктажи','Мастер-классы'] as $w)
        <span class="flex items-center gap-3 font-serif-display italic">{{ $w }} <span style="color:var(--accent)">✦</span></span>
      @endforeach
    </div>
  </div>
</section>

{{-- ════════════════ БОЛЬ — редакционная вёрстка ════════════════ --}}
<section class="py-16 sm:py-24">
  <div class="grid lg:grid-cols-[0.8fr_1.2fr] gap-10 lg:gap-16">
    <div class="reveal">
      <div class="section-index">01</div>
      <h2 class="font-serif-display text-3xl sm:text-4xl leading-tight mt-3">
        Запись есть.<br>А толку — мало.
      </h2>
      <p class="mt-4 text-lg leading-relaxed" style="color:var(--muted)">
        Диктофон включить легко. А вот разобрать часовую запись в нормальный конспект — это ещё час-полтора
        вашего вечера, которого нет.
      </p>
    </div>

    <div class="space-y-px rounded-2xl overflow-hidden" style="background:var(--line)">
      @foreach ([
        ['Переслушивать — дольше самой лекции','Чтобы выписать главное, приходится снова слушать всё, ставя на паузу каждые полминуты.'],
        ['В речи нет структуры','Сплошной поток с «эээ» и отступлениями. Где тут тезис, а где — вода, на слух не разделишь.'],
        ['Записи копятся, а руки не доходят','Папка с аудио растёт, конспектов всё нет. Перед экзаменом или дедлайном — паника.'],
      ] as $i => [$h,$p])
        <div class="reveal flex gap-5 p-6 sm:p-7" data-d="{{ $i }}" style="background:var(--panel)">
          <div class="font-serif-display text-2xl tabular-nums flex-none" style="color:var(--accent);opacity:.5">0{{ $i+1 }}</div>
          <div>
            <div class="font-semibold text-lg mb-1">{{ $h }}</div>
            <div class="text-sm leading-relaxed" style="color:var(--muted)">{{ $p }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ════════════════ КАК РАБОТАЕТ — вертикальный таймлайн ════════════════ --}}
<section id="how" class="py-16 sm:py-24 scroll-mt-24">
  <div class="text-center mb-14 reveal">
    <div class="text-xs font-semibold tracking-[.2em] uppercase" style="color:var(--accent)">Как это работает</div>
    <h2 class="font-serif-display text-3xl sm:text-5xl mt-3">Четыре шага. Ноль усилий.</h2>
  </div>

  <div class="relative max-w-3xl mx-auto">
    {{-- вертикальная линия --}}
    <div aria-hidden="true" class="absolute left-[1.35rem] sm:left-1/2 top-2 bottom-2 w-px sm:-translate-x-1/2" style="background:var(--line)"></div>

    <div class="space-y-10">
      @foreach ([
        ['Загрузите запись','Перетащите аудиофайл лекции в окно браузера. Подойдёт запись с диктофона, телефона или из приложения.','↑'],
        ['Мы распознаём речь','Расшифровываем русскую речь — даже не самого студийного качества — в точный текст.','✎'],
        ['Собираем конспект','Выделяем структуру: заголовки, разделы, ключевые термины и наглядные схемы связей.','◆'],
        ['Скачиваете и учитесь','Готовый документ — в PDF для печати, Word для правок или Markdown для заметок.','⤓'],
      ] as $i => [$h,$p,$ic])
        @php $right = $i % 2 === 1; @endphp
        <div class="reveal relative pl-16 sm:pl-0 sm:grid sm:grid-cols-2 sm:gap-10 sm:items-center" data-d="{{ $i % 3 }}">
          {{-- маркер на линии --}}
          <div class="absolute left-0 top-0 sm:left-1/2 sm:-translate-x-1/2 w-11 h-11 rounded-2xl grid place-items-center text-lg z-10"
               style="background:var(--accent);color:#fff;box-shadow:0 8px 20px -6px var(--accent)">{{ $ic }}</div>

          {{-- карточка по нужную сторону --}}
          <div class="card p-5 sm:p-6 {{ $right ? 'sm:col-start-2' : 'sm:col-start-1 sm:text-right' }}">
            <div class="text-sm font-semibold mb-1 tabular-nums" style="color:var(--accent)">Шаг {{ $i+1 }}</div>
            <div class="font-semibold text-lg mb-1">{{ $h }}</div>
            <div class="text-sm leading-relaxed" style="color:var(--muted)">{{ $p }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ════════════════ ТЁМНЫЙ FULL-BLEED БЛОК — пример «было → стало» ════════════════ --}}
<section class="full-bleed py-16 sm:py-24" style="background:linear-gradient(160deg, var(--ink), color-mix(in oklab, var(--ink) 72%, var(--accent)));">
  <div class="max-w-6xl mx-auto px-5 sm:px-7" style="color:#fff">
    <div class="text-center mb-12 reveal">
      <div class="text-xs font-semibold tracking-[.2em] uppercase" style="color:rgba(255,255,255,.55)">Пример</div>
      <h2 class="font-serif-display text-3xl sm:text-5xl mt-3">Из «эээ, ну как бы» — в чёткий документ</h2>
    </div>

    <div class="grid md:grid-cols-[1fr_auto_1fr] gap-6 md:gap-4 items-center max-w-5xl mx-auto">
      {{-- было --}}
      <div class="reveal rounded-2xl p-6" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1)">
        <div class="pill mb-4" style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.7)">Расшифровка как есть</div>
        <p class="text-sm leading-relaxed" style="color:rgba(255,255,255,.6)">
          «…ну вот смотрите, значит, нейросеть это как бы такая штука, ну то есть модель,
          которая состоит из нейронов, и они там связаны, эээ, весами, и вот когда мы подаём
          вход, оно умножается и проходит через активацию, и потом, ну, оно учится…»
        </p>
      </div>

      {{-- стрелка --}}
      <div class="reveal hidden md:grid place-items-center w-12 h-12 rounded-full mx-auto" data-d="1" style="background:var(--accent);color:#fff">→</div>

      {{-- стало --}}
      <div class="reveal rounded-2xl p-6" data-d="2" style="background:#fff;color:var(--ink);box-shadow:var(--shadow-lg)">
        <div class="pill pill-accent mb-4">Конспект Lectura</div>
        <div class="font-serif-display text-xl mb-2">Нейронные сети: основы</div>
        <p class="text-sm mb-3" style="color:var(--ink2)"><strong>Кратко.</strong> Модель из связанных нейронов; обучается подбором весов.</p>
        <ul class="text-sm space-y-1.5" style="color:var(--ink2)">
          <li class="flex gap-2"><span style="color:var(--accent)">›</span> Нейрон: вход → сумма → активация → выход</li>
          <li class="flex gap-2"><span style="color:var(--accent)">›</span> Связи задаются весами</li>
          <li class="flex gap-2"><span style="color:var(--accent)">›</span> Обучение — подбор весов на данных</li>
        </ul>
      </div>
    </div>
  </div>
</section>

{{-- ════════════════ ДЛЯ КОГО — крупная + мелкие ════════════════ --}}
<section class="py-16 sm:py-24">
  <div class="grid lg:grid-cols-3 gap-5">
    {{-- крупная карточка --}}
    <div class="reveal card p-8 lg:row-span-1 flex flex-col justify-between" style="background:linear-gradient(160deg,var(--panel),var(--accent-soft))">
      <div>
        <div class="text-3xl mb-4">💼</div>
        <h3 class="font-serif-display text-2xl mb-2">Учитесь по работе?</h3>
        <p class="text-sm leading-relaxed" style="color:var(--muted)">
          Курсы повышения квалификации, тренинги, инструктажи, аттестации. Запишите занятие —
          и сохраните понятный конспект, к которому вернётесь перед экзаменом или на рабочем месте.
        </p>
      </div>
    </div>

    <div class="lg:col-span-2 grid sm:grid-cols-2 gap-5">
      @foreach ([
        ['🎓','Студентам и школьникам','Конспект пары или урока — пока остальные переписывают доску.'],
        ['🎧','Тем, кто учится сам','Вебинары, подкасты, онлайн-лекции превращаются в аккуратные заметки.'],
        ['🌍','Изучающим язык','Разбирайте аудио на слух с текстовой расшифровкой и структурой.'],
        ['🩺','Профессионалам','Конференции, доклады, разборы — главное под рукой, без диктофонной каши.'],
      ] as $i => [$ic,$h,$p])
        <div class="reveal card p-6" data-d="{{ $i % 3 }}">
          <div class="text-2xl mb-3">{{ $ic }}</div>
          <div class="font-semibold mb-1">{{ $h }}</div>
          <div class="text-sm leading-relaxed" style="color:var(--muted)">{{ $p }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ════════════════ ВЫГОДЫ — горизонтальная лента ════════════════ --}}
<section class="py-12">
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-y-8 gap-x-6">
    @foreach ([
      ['⌚','Экономит часы','Не нужно переслушивать запись и конспектировать вручную.'],
      ['⌗','Понятная структура','Заголовки, разделы, термины и выводы — вместо стены текста.'],
      ['◆','Наглядные схемы','Связи и процессы из лекции — в виде диаграмм.'],
      ['⤓','Любой формат','PDF для печати, Word для правок, Markdown для заметок.'],
    ] as $i => [$ic,$h,$p])
      <div class="reveal" data-d="{{ $i % 3 }}">
        <div class="text-2xl mb-3" style="color:var(--accent)">{{ $ic }}</div>
        <div class="font-semibold mb-1.5 pb-2" style="border-bottom:2px solid var(--accent-line)">{{ $h }}</div>
        <div class="text-sm leading-relaxed mt-2" style="color:var(--muted)">{{ $p }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- ════════════════ FAQ — плавный grid-аккордеон ════════════════ --}}
<section class="py-16 sm:py-24">
  <div class="grid lg:grid-cols-[0.6fr_1.4fr] gap-10">
    <div class="reveal">
      <div class="section-index">FAQ</div>
      <h2 class="font-serif-display text-3xl sm:text-4xl mt-3">Коротко<br>о главном</h2>
      <p class="mt-3 text-sm" style="color:var(--muted)">Не нашли ответ? Просто попробуйте — это бесплатно.</p>
    </div>

    <div class="space-y-3" x-data="{ open: 0 }">
      @foreach ([
        ['Какие форматы аудио поддерживаются?','MP3, M4A (диктофон iPhone), WAV и OGG. Подойдёт запись с телефона, диктофона или из приложения для созвонов.'],
        ['Насколько точно распознаётся русская речь?','Мы используем современную модель распознавания — она хорошо справляется с русской речью, в том числе с записей не студийного качества.'],
        ['Сколько длится обработка?','Обычно пара минут. Длинные лекции (час-два) обрабатываются дольше — можно закрыть страницу, конспект соберётся в фоне.'],
        ['А если лекция очень длинная?','Поддерживаются записи до двух часов. Длинная запись автоматически делится на части и собирается в единый конспект.'],
        ['Это платно?','Регистрация бесплатна — попробуйте на своей первой лекции прямо сейчас.'],
      ] as $i => [$q,$a])
        <div class="acc-item card" :class="open === {{ $i }} && 'acc-open'" style="border-color:var(--line)"
             x-bind:style="open === {{ $i }} && 'border-color:var(--accent-line)'">
          <button type="button" @click="open = (open === {{ $i }} ? null : {{ $i }})"
                  class="w-full flex items-center justify-between gap-4 p-5 text-left font-medium">
            <span>{{ $q }}</span>
            <span class="flex-none w-6 h-6 rounded-full grid place-items-center transition-transform"
                  :class="open === {{ $i }} && 'rotate-45'"
                  style="background:var(--accent-soft);color:var(--accent)">+</span>
          </button>
          <div class="acc-body"><div><p class="px-5 pb-5 text-sm leading-relaxed" style="color:var(--muted)">{{ $a }}</p></div></div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ════════════════ ФИНАЛЬНЫЙ CTA ════════════════ --}}
<section class="py-8 pb-16">
  <div class="reveal full-bleed">
    <div class="max-w-4xl mx-auto px-5 sm:px-7">
      <div class="rounded-[1.75rem] p-10 sm:p-16 text-center relative overflow-hidden"
           style="background:linear-gradient(135deg, var(--ink), color-mix(in oklab, var(--ink) 68%, var(--accent)));color:#fff">
        <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-16 w-64 h-64 rounded-full blur-3xl opacity-40" style="background:var(--accent)"></div>
        <h2 class="font-serif-display text-3xl sm:text-5xl mb-4 relative leading-tight">Следующую лекцию<br>не записывайте зря</h2>
        <p class="max-w-md mx-auto mb-8 relative" style="color:rgba(255,255,255,.7)">
          Загрузите аудио — получите конспект. Регистрация меньше минуты, первая лекция бесплатно.
        </p>
        @auth
          <a href="{{ route('lectures.create') }}" class="btn btn-accent text-base px-8 py-4 relative">Загрузить аудио →</a>
        @else
          <a href="{{ route('register') }}" class="btn btn-accent text-base px-8 py-4 relative">Начать бесплатно →</a>
        @endauth
      </div>
    </div>
  </div>
</section>

</x-lectura-layout>
