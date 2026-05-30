<?php

namespace Tests\Unit;

use App\Support\LectureSummary;
use Tests\TestCase;

class LectureSummaryRichTest extends TestCase
{
    public function test_parses_rich_fields(): void
    {
        $s = LectureSummary::fromArray([
            'title' => 'T', 'summary' => 'S', 'subject' => 'Физика', 'level' => 'advanced',
            'sections' => [],
            'glossary' => [['term' => 'Сила', 'definition' => 'мера взаимодействия'], ['term' => '']],
            'quiz' => [['question' => 'Что такое сила?', 'answer' => 'мера'], ['question' => '']],
            'key_terms' => [], 'takeaways' => [],
        ]);

        $this->assertSame('Физика', $s->subject);
        $this->assertSame('advanced', $s->level);
        $this->assertCount(1, $s->glossary); // пустой term отброшен
        $this->assertSame('Сила', $s->glossary[0]['term']);
        $this->assertCount(1, $s->quiz); // пустой question отброшен
        $this->assertArrayHasKey('glossary', $s->toArray());
        $this->assertArrayHasKey('quiz', $s->toArray());
    }

    public function test_backward_compatible_without_new_fields(): void
    {
        $s = LectureSummary::fromArray([
            'title' => 'T', 'summary' => 'S',
            'sections' => [['heading' => 'H', 'content_markdown' => 'C']],
        ]);

        $this->assertNull($s->subject);
        $this->assertNull($s->level);
        $this->assertSame([], $s->glossary);
        $this->assertSame([], $s->quiz);
    }

    public function test_invalid_level_becomes_null(): void
    {
        $s = LectureSummary::fromArray(['title' => 'T', 'summary' => 'S', 'sections' => [], 'level' => 'bogus']);
        $this->assertNull($s->level);
    }
}
