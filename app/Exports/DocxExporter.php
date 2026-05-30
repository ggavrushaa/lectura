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

        // Мета: предмет + уровень
        $levelLabels = ['intro' => 'Вводный', 'intermediate' => 'Средний', 'advanced' => 'Продвинутый'];
        $metaParts = array_filter([
            $data['subject'] ?? null,
            isset($data['level'], $levelLabels[$data['level']]) ? $levelLabels[$data['level']].' уровень' : null,
        ]);
        if ($metaParts) {
            $section->addText(implode('  ·  ', $metaParts), ['italic' => true, 'color' => '888888']);
        }

        if (! empty($data['summary'])) {
            $section->addText($data['summary'], ['bold' => true]);
        }
        $section->addTextBreak();

        foreach (($data['sections'] ?? []) as $s) {
            $section->addTitle($s['heading'] ?? '', 2);
            // markdown упрощаем до текста (PhpWord не парсит markdown)
            $section->addText(strip_tags(\Illuminate\Support\Str::markdown($s['content_markdown'] ?? '', ['html_input' => 'strip'])));
            if (! empty($s['diagram_png']) && is_file($s['diagram_png'])) {
                $section->addImage($s['diagram_png'], ['width' => 420, 'alignment' => 'center']);
            }
            $section->addTextBreak();
        }

        if (! empty($data['glossary'])) {
            $section->addTitle('Глоссарий', 2);
            foreach ($data['glossary'] as $g) {
                if (empty($g['term'])) {
                    continue;
                }
                $p = $section->addTextRun();
                $p->addText($g['term'].' — ', ['bold' => true]);
                $p->addText($g['definition'] ?? '');
            }
            $section->addTextBreak();
        }

        if (! empty($data['takeaways'])) {
            $section->addTitle('Выводы', 2);
            foreach ($data['takeaways'] as $t) {
                $section->addListItem($t);
            }
            $section->addTextBreak();
        }

        if (! empty($data['quiz'])) {
            $section->addTitle('Вопросы для самопроверки', 2);
            foreach ($data['quiz'] as $qi => $q) {
                if (empty($q['question'])) {
                    continue;
                }
                $section->addText(($qi + 1).'. '.$q['question'], ['bold' => true]);
                if (! empty($q['answer'])) {
                    $section->addText('Ответ: '.$q['answer'], ['italic' => true, 'color' => '2e8b6f']);
                }
                $section->addTextBreak();
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
