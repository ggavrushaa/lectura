<?php

namespace Tests\Unit;

use App\Support\LectureSummary;
use Tests\TestCase;

class LectureSummaryTest extends TestCase
{
    public function test_from_array_builds_sections(): void
    {
        $summary = LectureSummary::fromArray([
            'title' => 'Лекция 7',
            'summary' => 'Кратко',
            'reading_time_min' => 6,
            'sections' => [
                ['heading' => 'Введение', 'content_markdown' => 'Текст',
                 'terms' => ['нейрон'], 'diagram_mermaid' => 'graph LR; A-->B'],
            ],
            'key_terms' => ['нейрон', 'веса'],
            'takeaways' => ['вывод 1'],
        ]);

        $this->assertSame('Лекция 7', $summary->title);
        $this->assertCount(1, $summary->sections);
        $this->assertSame('Введение', $summary->sections[0]->heading);
        $this->assertSame('graph LR; A-->B', $summary->sections[0]->diagramMermaid);
        $this->assertSame(['нейрон', 'веса'], $summary->keyTerms);
    }

    public function test_from_array_tolerates_missing_optional_fields(): void
    {
        $summary = LectureSummary::fromArray([
            'title' => 'X', 'summary' => 'Y',
            'sections' => [['heading' => 'H', 'content_markdown' => 'C']],
        ]);

        $this->assertSame(0, $summary->readingTimeMin);
        $this->assertNull($summary->sections[0]->diagramMermaid);
        $this->assertSame([], $summary->keyTerms);
    }
}
