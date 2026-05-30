<x-lectura-layout
    title="Lectura — конспект лекции из аудиозаписи за пару минут"
    description="Загрузите аудиозапись лекции, вебинара или урока — получите готовый структурированный конспект со схемами. Скачивайте в PDF, Word и Markdown. Для студентов, школьников и тех, кто учится по работе.">

{{-- ─────────── HERO ─────────── --}}
<section class="relative text-center pt-8 pb-16 sm:pt-14 sm:pb-24">
  <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
    <div class="absolute left-[6%] top-8 w-48 h-48 rounded-full blur-3xl opacity-40" style="background:var(--accent); animation:float-slow 7s ease-in-out infinite"></div>
    <div class="absolute right-[8%] top-24 w-60 h-60 rounded-full blur-3xl opacity-20" style="background:var(--accent); animation:float-slow 9s ease-in-out infinite reverse"></div>
  </div>

  <div class="stagger max-w-3xl mx-auto">
    <div style="--i:0" class="flex justify-center">
      <span class="pill pill-accent"><span class="dot dot-live"></span> Конспект готов за пару минут</span>
    </div>

    <h1 style="--i:1" class="text-4xl sm:text-6xl font-extrabold leading-[1.05] tracking-tight mt-6 mb-5">
      Лекция голосом —<br>а на выходе <span class="font-serif-display italic font-medium" style="color:var(--accent)">готовый конспект</span>
    </h1>

    <p style="--i:2" class="text-lg sm:text-xl max-w-2xl mx-auto mb-8 leading-relaxed" style="color:var(--muted)">
      Записали лекцию, вебинар или урок на диктофон? Загрузите аудио — и через пару минут получите
      структурированный конспект с заголовками, ключевыми терминами и схемами. Не нужно переслушивать часами.
    </p>

    <div style="--i:3" class="flex flex-wrap gap-3 justify-center">
      @auth
        <a href="{{ route('lectures.create') }}" class="btn btn-accent text-base px-7 py-3.5">Загрузить аудио →</a>
        <a href="{{ route('dashboard') }}" class="btn btn-ghost text-base px-7 py-3.5">Мои лекции</a>
      @else
        <a href="{{ route('register') }}" class="btn btn-accent text-base px-7 py-3.5">Сделать конспект бесплатно →</a>
        <a href="#how" class="btn btn-ghost text-base px-7 py-3.5">Как это работает</a>
      @endauth
    </div>

    <p style="--i:4" class="text-sm mt-4" style="color:var(--muted)">
      Без установки · поддержка русской речи · MP3, M4A, WAV, OGG
    </p>
  </div>
</section>

{{-- ─────────── БОЛЬ / ПРОБЛЕМА ─────────── --}}
<section class="py-12">
  <div class="text-center max-w-2xl mx-auto mb-10">
    <h2 class="font-serif-display text-3xl sm:text-4xl mb-3">Знакомо?</h2>
    <p style="color:var(--muted)">Записать лекцию — легко. Превратить запись в нормальный конспект — мучительно.</p>
  </div>
  <div class="grid sm:grid-cols-3 gap-4 max-w-4xl mx-auto">
    @foreach ([
      ['⏳','Час записи — час прослушивания','Чтобы законспектировать, приходится слушать всё заново, ставя на паузу.'],
      ['🌀','Поток речи без структуры','В записи нет заголовков и разделов — сложно понять, что главное.'],
      ['📵','Некогда','Работа, учёба, дела — сесть и разобрать запись руками просто нет времени.'],
    ] as [$ic,$h,$p])
      <div class="card p-6">
        <div class="text-2xl mb-3">{{ $ic }}</div>
        <div class="font-semibold mb-1.5">{{ $h }}</div>
        <div class="text-sm leading-relaxed" style="color:var(--muted)">{{ $p }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- ─────────── КАК РАБОТАЕТ ─────────── --}}
<section id="how" class="py-12 scroll-mt-24">
  <div class="text-center mb-10">
    <div class="text-xs font-semibold tracking-[.18em] uppercase" style="color:var(--muted)">Как это работает</div>
    <h2 class="font-serif-display text-3xl sm:text-4xl mt-2">Четыре шага до конспекта</h2>
  </div>
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach ([
      ['01','Загрузите запись','Перетащите аудиофайл лекции — MP3, M4A, WAV или OGG.','↑'],
      ['02','Мы распознаём речь','Точная расшифровка русской речи, даже с диктофона.','✎'],
      ['03','Собираем конспект','Структура, разделы, ключевые термины и наглядные схемы.','◆'],
      ['04','Скачиваете','Готовый документ в PDF, Word или Markdown.','⤓'],
    ] as [$n,$h,$p,$ic])
      <div class="card p-5 text-left transition-all hover:-translate-y-1"
           onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow='var(--shadow-sm)'">
        <div class="flex items-center justify-between mb-4">
          <span class="text-sm font-semibold tabular-nums" style="color:var(--accent)">{{ $n }}</span>
          <span class="w-9 h-9 rounded-xl grid place-items-center text-lg" style="background:var(--accent-soft); color:var(--accent)">{{ $ic }}</span>
        </div>
        <div class="font-semibold mb-1">{{ $h }}</div>
        <div class="text-sm leading-relaxed" style="color:var(--muted)">{{ $p }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- ─────────── ПРИМЕР «БЫЛО → СТАЛО» ─────────── --}}
<section class="py-12">
  <div class="text-center mb-10">
    <div class="text-xs font-semibold tracking-[.18em] uppercase" style="color:var(--muted)">Пример</div>
    <h2 class="font-serif-display text-3xl sm:text-4xl mt-2">Из потока речи — в стройный документ</h2>
  </div>
  <div class="grid md:grid-cols-2 gap-4 max-w-4xl mx-auto items-stretch">
    {{-- было --}}
    <div class="card p-6">
      <div class="pill pill-muted mb-4">Было — расшифровка</div>
      <p class="text-sm leading-relaxed" style="color:var(--muted)">
        «…ну вот смотрите, значит, нейросеть это как бы такая штука, ну то есть модель, которая
        состоит из нейронов, и они там связаны, эээ, весами, и вот когда мы подаём вход, оно
        умножается и проходит через активацию, и потом, ну, оно учится, подбирая эти веса…»
      </p>
    </div>
    {{-- стало --}}
    <div class="card p-6" style="border-color:var(--accent-line)">
      <div class="pill pill-accent mb-4">Стало — конспект</div>
      <div class="font-serif-display text-lg mb-2">Нейронные сети: основы</div>
      <p class="text-sm mb-3" style="color:var(--ink2)"><strong>Кратко.</strong> Нейросеть — модель из связанных нейронов; обучается подбором весов на данных.</p>
      <div class="text-sm font-semibold mb-1">Ключевые идеи</div>
      <ul class="text-sm space-y-1 mb-3" style="color:var(--ink2)">
        <li class="flex gap-2"><span style="color:var(--accent)">›</span> Нейрон: вход → взвешенная сумма → активация → выход</li>
        <li class="flex gap-2"><span style="color:var(--accent)">›</span> Сила связи задаётся весами</li>
        <li class="flex gap-2"><span style="color:var(--accent)">›</span> Обучение — подбор весов на размеченных данных</li>
      </ul>
      <div class="flex flex-wrap gap-1.5">
        <span class="pill pill-muted">нейрон</span><span class="pill pill-muted">веса</span><span class="pill pill-muted">активация</span>
      </div>
    </div>
  </div>
</section>

{{-- ─────────── ДЛЯ КОГО ─────────── --}}
<section class="py-12">
  <div class="text-center mb-10">
    <h2 class="font-serif-display text-3xl sm:text-4xl">Кому пригодится</h2>
  </div>
  <div class="grid sm:grid-cols-3 gap-4 max-w-4xl mx-auto">
    @foreach ([
      ['🎓','Студентам и школьникам','Конспект пары или урока — пока другие переписывают доску.'],
      ['💼','Тем, кого учат по работе','Курсы повышения квалификации, тренинги, инструктажи — всё в виде понятного документа.'],
      ['🎧','Всем, кто учится сам','Вебинары, подкасты, онлайн-лекции — превращайте прослушанное в заметки.'],
    ] as [$ic,$h,$p])
      <div class="card p-6 text-center">
        <div class="text-3xl mb-3">{{ $ic }}</div>
        <div class="font-semibold mb-1.5">{{ $h }}</div>
        <div class="text-sm leading-relaxed" style="color:var(--muted)">{{ $p }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- ─────────── ВЫГОДЫ ─────────── --}}
<section class="py-12">
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 max-w-5xl mx-auto">
    @foreach ([
      ['Экономит часы','Не нужно переслушивать запись и конспектировать вручную.'],
      ['Понятная структура','Заголовки, разделы, термины и выводы — а не сплошной текст.'],
      ['Наглядные схемы','Связи и процессы из лекции — в виде диаграмм.'],
      ['Удобный экспорт','PDF для печати, Word для правок, Markdown для заметок.'],
    ] as [$h,$p])
      <div class="card p-5">
        <div class="w-8 h-8 rounded-lg grid place-items-center mb-3" style="background:var(--ok-soft); color:var(--ok)">✓</div>
        <div class="font-semibold mb-1">{{ $h }}</div>
        <div class="text-sm leading-relaxed" style="color:var(--muted)">{{ $p }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- ─────────── FAQ ─────────── --}}
<section class="py-12 max-w-2xl mx-auto">
  <div class="text-center mb-8">
    <h2 class="font-serif-display text-3xl sm:text-4xl">Частые вопросы</h2>
  </div>
  <div class="space-y-3" x-data="{ open:0 }">
    @foreach ([
      ['Какие форматы аудио поддерживаются?','MP3, M4A (диктофон iPhone), WAV и OGG. Подойдёт запись с телефона, диктофона или из приложения.'],
      ['Насколько точно распознаётся русская речь?','Мы используем современную модель распознавания, которая хорошо справляется с русской речью, в том числе с записей не самого высокого качества.'],
      ['Сколько длится обработка?','Обычно пара минут. Длинные лекции (час-два) обрабатываются дольше — можно закрыть страницу, конспект соберётся в фоне.'],
      ['Что с длинными лекциями?','Поддерживаются записи до двух часов. Длинная запись автоматически делится на части и собирается в единый конспект.'],
      ['Это платно?','Регистрация бесплатна — попробуйте на первой лекции.'],
    ] as $i => [$q,$a])
      <div class="card overflow-hidden">
        <button type="button" @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                class="w-full flex items-center justify-between gap-4 p-4 text-left font-medium">
          <span>{{ $q }}</span>
          <svg class="w-5 h-5 flex-none transition-transform" :class="open === {{ $i }} && 'rotate-180'" style="color:var(--muted)" viewBox="0 0 20 20" fill="none"><path d="M6 8l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div x-show="open === {{ $i }}" x-transition x-cloak class="px-4 pb-4 text-sm leading-relaxed" style="color:var(--muted)">{{ $a }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- ─────────── ФИНАЛЬНЫЙ CTA ─────────── --}}
<section class="py-12">
  <div class="card p-10 sm:p-14 text-center relative overflow-hidden max-w-3xl mx-auto"
       style="background:linear-gradient(135deg, var(--panel), var(--accent-soft))">
    <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 w-48 h-48 rounded-full blur-3xl opacity-30" style="background:var(--accent)"></div>
    <h2 class="font-serif-display text-3xl sm:text-4xl mb-3 relative">Превратите первую запись в конспект</h2>
    <p class="max-w-md mx-auto mb-7 relative" style="color:var(--muted)">Регистрация занимает меньше минуты. Загрузите аудио — остальное сделаем мы.</p>
    @auth
      <a href="{{ route('lectures.create') }}" class="btn btn-accent text-base px-7 py-3.5 relative">Загрузить аудио →</a>
    @else
      <a href="{{ route('register') }}" class="btn btn-accent text-base px-7 py-3.5 relative">Начать бесплатно →</a>
    @endauth
  </div>
</section>

</x-lectura-layout>
