<?php

namespace App\Services;

use App\Support\LectureSummary;
use App\Support\SummarySection;

class SummaryMarkdownBuilder
{
    public function build(LectureSummary $s): string
    {
        $out = [];
        $out[] = "# {$s->title}";
        $out[] = '';
        $out[] = "> {$s->summary}";
        $out[] = '';

        foreach ($s->sections as $section) {
            $out[] = "## {$section->heading}";
            $out[] = '';
            $out[] = $section->contentMarkdown;
            $out[] = '';
            if ($section->diagramMermaid) {
                $out[] = '```mermaid';
                $out[] = $section->diagramMermaid;
                $out[] = '```';
                $out[] = '';
            }
        }

        if ($s->keyTerms) {
            $out[] = '## Ключевые термины';
            $out[] = '';
            $out[] = implode(', ', $s->keyTerms);
            $out[] = '';
        }

        if ($s->takeaways) {
            $out[] = '## Выводы';
            $out[] = '';
            foreach ($s->takeaways as $t) {
                $out[] = "- {$t}";
            }
            $out[] = '';
        }

        return implode("\n", $out);
    }
}
