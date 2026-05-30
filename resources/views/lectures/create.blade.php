<x-lectura-layout title="Новая лекция">
<div class="max-w-2xl mx-auto">
  <h1 class="text-2xl font-semibold mb-1">Новая лекция</h1>
  <p class="mb-6" style="color:var(--muted)">Загрузите аудио — получите конспект.</p>

  @if ($errors->any())
    <div class="mb-4 p-3 rounded-lg" style="background:var(--accent-soft);color:var(--accent)">
      {{ $errors->first() }}
    </div>
  @endif

  <form method="POST" action="{{ route('lectures.store') }}" enctype="multipart/form-data"
        class="rounded-2xl p-6 border" style="background:var(--panel);border-color:var(--line)">
    @csrf
    <label class="block border-2 border-dashed rounded-xl p-12 text-center cursor-pointer"
           style="border-color:var(--line);background:var(--inset)">
      <input type="file" name="audio" accept="audio/*" class="hidden" required
             onchange="this.nextElementSibling.textContent = this.files[0]?.name || 'Файл не выбран'">
      <div class="text-3xl mb-2">↑</div>
      <div class="font-medium">Перетащите аудиофайл или нажмите</div>
      <div class="text-sm mt-1" style="color:var(--muted)">MP3 / M4A / WAV / OGG · до {{ config('lectura.max_file_mb') }} МБ</div>
    </label>

    <div class="flex flex-wrap gap-4 mt-5 text-sm">
      <label>Подробность:
        <select name="detail_level" class="rounded-lg px-2 py-1" style="background:var(--soft)">
          <option value="short">Кратко</option>
          <option value="medium" selected>Средне</option>
          <option value="detailed">Подробно</option>
        </select>
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="with_diagrams" value="1" checked> Со схемами
      </label>
    </div>

    <p class="text-xs mt-4" style="color:var(--muted)">
      Аудио отправляется в сервисы распознавания (Groq) и конспектирования (OpenRouter).
    </p>

    <button class="mt-5 px-5 py-2.5 rounded-xl text-white font-medium" style="background:var(--ink)">
      Создать конспект
    </button>
  </form>
</div>
</x-lectura-layout>
