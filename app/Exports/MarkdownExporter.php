<?php

namespace App\Exports;

use App\Models\Lecture;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarkdownExporter
{
    public function download(Lecture $lecture): StreamedResponse
    {
        $filename = \Illuminate\Support\Str::slug($lecture->title ?: 'lecture').'.md';
        $content = $lecture->summary_markdown ?? '';

        return response()->streamDownload(fn () => print($content), $filename, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
        ]);
    }
}
