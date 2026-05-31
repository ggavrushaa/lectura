<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Превращает markdown раздела конспекта в безопасный HTML с поддержкой
 * выносок-акцентов (> [!important] ...) и сохранением формул LaTeX для KaTeX.
 */
class ConspectusRenderer
{
    private const CALLOUTS = [
        'important' => ['⚠', 'Важно', 'callout-important'],
        'note' => ['◆', 'Заметка', 'callout-note'],
        'example' => ['▹', 'Пример', 'callout-example'],
        'tip' => ['✦', 'Совет', 'callout-tip'],
        'exam' => ['★', 'К экзамену', 'callout-exam'],
    ];

    public static function html(string $markdown): string
    {
        // 1) Вырезаем callout-блоки до markdown-рендера, заменяем плейсхолдерами.
        $callouts = [];
        $markdown = preg_replace_callback(
            '/^>\s*\[!(\w+)\][ \t]*(.*(?:\n>.*)*)/mi',
            function ($m) use (&$callouts) {
                $type = strtolower($m[1]);
                $body = preg_replace('/^>\s?/m', '', $m[2]); // снять "> " с продолжений
                $key = '%%CALLOUT'.count($callouts).'%%';
                $callouts[$key] = ['type' => $type, 'body' => trim($body)];

                return $key;
            },
            $markdown
        ) ?? $markdown;

        // 2) Markdown → HTML (без сырого HTML от модели).
        $rendered = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        // 3) Возвращаем callout-блоки уже как готовый HTML.
        foreach ($callouts as $key => $c) {
            [$icon, $label, $cls] = self::CALLOUTS[$c['type']] ?? ['◆', 'Заметка', 'callout-note'];
            $bodyHtml = Str::markdown($c['body'], ['html_input' => 'strip', 'allow_unsafe_links' => false]);
            $html = '<div class="callout '.$cls.'">'
                .'<span class="callout-ic">'.$icon.'</span>'
                .'<div class="callout-body"><strong>'.e($label).'</strong>'.$bodyHtml.'</div>'
                .'</div>';
            // плейсхолдер мог обернуться в <p>...</p> — заменяем с учётом этого
            $rendered = str_replace(['<p>'.$key.'</p>', $key], $html, $rendered);
        }

        return $rendered;
    }
}
