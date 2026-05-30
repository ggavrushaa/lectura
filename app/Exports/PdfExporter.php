<?php

namespace App\Exports;

use App\Models\Lecture;
use Spatie\Browsershot\Browsershot;

class PdfExporter
{
    public function html(Lecture $lecture): string
    {
        return view('exports.pdf', [
            'lecture' => $lecture,
            'summary' => $lecture->summary_json ?? [],
        ])->render();
    }

    public function download(Lecture $lecture)
    {
        $filename = \Illuminate\Support\Str::slug($lecture->title ?: 'lecture').'.pdf';
        $tmp = storage_path('app/private/exports/'.uniqid('pdf_').'.pdf');
        @mkdir(dirname($tmp), 0775, true);

        Browsershot::html($this->html($lecture))
            ->setChromePath(config('lectura.chrome_path'))
            ->setNodeBinaryPath(config('lectura.node_path'))
            ->setNpmBinaryPath(config('lectura.npm_path'))
            ->format('A4')
            ->showBackground()
            ->margins(10, 10, 10, 10)
            ->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend();
    }
}
