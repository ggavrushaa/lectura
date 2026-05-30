<?php

namespace Tests\Unit;

use App\Services\SummaryMarkdownBuilder;
use App\Support\LectureSummary;
use Tests\TestCase;

class SummaryMarkdownBuilderTest extends TestCase
{
    public function test_builds_markdown_with_sections_and_mermaid(): void
    {
        $summary = LectureSummary::fromArray([
            'title' => 'Лекция 7', 'summary' => 'Резюме', 'reading_time_min' => 6,
            'sections' => [['heading' => 'Введение', 'content_markdown' => 'Текст',
                            'terms' => ['нейрон'], 'diagram_mermaid' => 'graph LR; A-->B']],
            'key_terms' => ['нейрон'], 'takeaways' => ['вывод'],
        ]);

        $md = (new SummaryMarkdownBuilder())->build($summary);

        $this->assertStringContainsString('# Лекция 7', $md);
        $this->assertStringContainsString('## Введение', $md);
        $this->assertStringContainsString('```mermaid', $md);
        $this->assertStringContainsString('graph LR; A-->B', $md);
        $this->assertStringContainsString('## Выводы', $md);
    }
}
