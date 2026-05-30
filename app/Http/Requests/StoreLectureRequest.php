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
            'audio' => ['required', 'file', "max:{$maxKb}", 'mimetypes:audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,audio/x-wav,audio/ogg'],
            'detail_level' => ['nullable', 'in:short,medium,detailed'],
            'with_diagrams' => ['nullable', 'boolean'],
        ];
    }
}
