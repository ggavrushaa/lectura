<x-lectura-layout title="Новая лекция">
<div class="max-w-2xl mx-auto anim-fade-up">

  <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm mb-5 hover:underline" style="color:var(--muted)">← К списку лекций</a>

  <h1 class="text-3xl font-bold tracking-tight mb-1">Новая лекция</h1>
  <p class="mb-7" style="color:var(--muted)">Загрузите аудио — получите готовый конспект.</p>

  @if ($errors->any())
    <div class="mb-5 p-4 rounded-xl flex items-start gap-3 anim-scale-in"
         style="background:var(--danger-soft); color:var(--danger); border:1px solid color-mix(in oklab, var(--danger) 25%, transparent)">
      <span class="text-lg leading-none">⚠</span>
      <span class="text-sm">{{ $errors->first() }}</span>
    </div>
  @endif

  <form method="POST" action="{{ route('lectures.store') }}" enctype="multipart/form-data"
        x-data="uploader()" @submit="submitting = true">
    @csrf

    {{-- Drag & drop --}}
    <label class="card block p-10 text-center cursor-pointer transition-all relative overflow-hidden"
           x-bind:style="dragging ? 'border-color:var(--accent); background:var(--accent-soft)' : ''"
           style="border-style:dashed; border-width:2px"
           @dragover.prevent="dragging=true" @dragleave.prevent="dragging=false"
           @drop.prevent="handleDrop($event)">
      <input type="file" name="audio" accept="audio/*,.m4a,.mp3,.wav,.ogg" class="hidden" required
             x-ref="file" @change="pick($event.target.files)">

      <template x-if="!fileName">
        <div>
          <div class="w-14 h-14 mx-auto mb-4 rounded-2xl grid place-items-center text-2xl transition-transform"
               x-bind:class="dragging && 'scale-110'"
               style="background:var(--accent-soft); color:var(--accent)">↑</div>
          <div class="font-semibold">Перетащите аудиофайл или нажмите</div>
          <div class="text-sm mt-1.5" style="color:var(--muted)">MP3 · M4A · WAV · OGG — до {{ config('lectura.max_file_mb') }} МБ</div>
        </div>
      </template>

      <template x-if="fileName">
        <div class="anim-scale-in">
          <div class="w-14 h-14 mx-auto mb-4 rounded-2xl grid place-items-center text-2xl"
               style="background:var(--ok-soft); color:var(--ok)">✓</div>
          <div class="font-semibold break-all" x-text="fileName"></div>
          <div class="text-sm mt-1.5" style="color:var(--muted)" x-text="fileSize"></div>
          <button type="button" class="text-sm mt-3 hover:underline" style="color:var(--accent)"
                  @click.prevent="reset()">Выбрать другой</button>
        </div>
      </template>
    </label>

    {{-- Подробность --}}
    <div class="mt-6">
      <div class="text-sm font-medium mb-2.5" style="color:var(--ink2)">Подробность конспекта</div>
      <div class="grid grid-cols-3 gap-2" x-data="{ d:'medium' }">
        @foreach ([['short','Кратко','главное'],['medium','Средне','баланс'],['detailed','Подробно','с деталями']] as [$val,$lbl,$hint])
          <label class="card p-3.5 text-center cursor-pointer transition-all"
                 x-bind:style="d==='{{ $val }}' ? 'border-color:var(--accent); background:var(--accent-soft)' : ''">
            <input type="radio" name="detail_level" value="{{ $val }}" class="hidden" @if($val==='medium') checked @endif @click="d='{{ $val }}'">
            <div class="font-medium text-sm">{{ $lbl }}</div>
            <div class="text-xs mt-0.5" style="color:var(--muted)">{{ $hint }}</div>
          </label>
        @endforeach
      </div>
    </div>

    {{-- Схемы --}}
    <label class="card mt-4 p-4 flex items-center gap-3 cursor-pointer" x-data="{ on:true }"
           x-bind:style="on ? 'border-color:var(--accent-line)' : ''">
      <input type="checkbox" name="with_diagrams" value="1" checked class="hidden" @change="on=$event.target.checked">
      <span class="w-10 h-6 rounded-full relative transition-colors flex-none"
            x-bind:style="on ? 'background:var(--accent)' : 'background:var(--line-strong)'">
        <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white transition-all" x-bind:style="on ? 'left:1.125rem' : 'left:0.125rem'"></span>
      </span>
      <span>
        <span class="font-medium text-sm block">Строить схемы</span>
        <span class="text-xs" style="color:var(--muted)">Диаграммы из структуры лекции, где это уместно</span>
      </span>
    </label>

    <p class="text-xs mt-5 flex items-start gap-2" style="color:var(--muted)">
      <span>🔒</span>
      <span>Аудио отправляется в сервисы распознавания (Groq) и конспектирования (OpenRouter). Исходный файл удаляется после обработки.</span>
    </p>

    <button type="submit" class="btn btn-accent w-full justify-center mt-6 text-base py-3.5"
            x-bind:disabled="!fileName || submitting">
      <span x-show="!submitting">Создать конспект →</span>
      <span x-show="submitting" x-cloak>Загрузка…</span>
    </button>
  </form>
</div>

<script>
function uploader(){
  return {
    dragging:false, fileName:'', fileSize:'', submitting:false,
    pick(files){
      if(!files.length) return;
      const f = files[0];
      this.fileName = f.name;
      this.fileSize = (f.size/1024/1024).toFixed(1) + ' МБ';
    },
    handleDrop(e){
      this.dragging=false;
      const files = e.dataTransfer.files;
      if(files.length){ this.$refs.file.files = files; this.pick(files); }
    },
    reset(){ this.$refs.file.value=''; this.fileName=''; this.fileSize=''; }
  }
}
</script>
</x-lectura-layout>
