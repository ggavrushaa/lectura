<?php

namespace App\Exports;

use App\Models\Lecture;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class DocxExporter
{
    public function download(Lecture $lecture)
    {
        $data = $lecture->summary_json ?? [];
        $word = new PhpWord();
        $word->setDefaultFontName('Calibri');
        $section = $word->addSection();

        $section->addTitle($data['title'] ?? $lecture->title, 1);
        $section->addText($data['summary'] ?? '');
        $section->addTextBreak();

        foreach (($data['sections'] ?? []) as $s) {
            $section->addTitle($s['heading'] ?? '', 2);
            // markdown упрощаем до текста (PhpWord не парсит markdown)
            $section->addText(strip_tags(\Illuminate\Support\Str::markdown($s['content_markdown'] ?? '')));
            if (! empty($s['diagram_png']) && is_file($s['diagram_png'])) {
                $section->addImage($s['diagram_png'], ['width' => 420, 'alignment' => 'center']);
            }
            $section->addTextBreak();
        }

        if (! empty($data['takeaways'])) {
            $section->addTitle('Выводы', 2);
            foreach ($data['takeaways'] as $t) {
                $section->addListItem($t);
            }
        }

        $filename = \Illuminate\Support\Str::slug($lecture->title ?: 'lecture').'.docx';
        $tmp = storage_path('app/private/exports/'.uniqid('docx_').'.docx');
        @mkdir(dirname($tmp), 0775, true);
        IOFactory::createWriter($word, 'Word2007')->save($tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend();
    }
}
