<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLectureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $maxKb = config('lectura.max_file_mb') * 1024;

        return [
            // Валидация по расширению (m4a/mp4-контейнеры finfo детектит как video/*,
            // поэтому строгий mimetypes их ложно отклоняет). Реальная проверка
            // содержимого — через ffprobe в AudioPreparer::probe() на этапе обработки.
            'audio' => ['required', 'file', "max:{$maxKb}", 'extensions:mp3,m4a,mp4,wav,ogg,oga'],
            'detail_level' => ['nullable', 'in:short,medium,detailed'],
            'with_diagrams' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'audio.extensions' => 'Поддерживаются аудиофайлы: MP3, M4A, WAV, OGG.',
            'audio.max' => 'Файл слишком большой (максимум :max КБ).',
        ];
    }
}
