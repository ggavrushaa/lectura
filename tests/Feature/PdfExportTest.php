<?php

namespace Tests\Feature;

use App\Exports\PdfExporter;
use App\Enums\LectureStatus;
use App\Models\Lecture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_builds_html_with_title_and_sections(): void
    {
        $lecture = Lecture::factory()->create([
            'status' => LectureStatus::Done, 'title' => 'Нейросети',
            'summary_json' => ['title' => 'Нейросети', 'summary' => 'Резюме',
                'sections' => [['heading' => 'Введение', 'content_markdown' => '**жирный**', 'terms' => [], 'diagram_svg' => null]],
                'key_terms' => [], 'takeaways' => ['вывод']],
        ]);

        $html = app(PdfExporter::class)->html($lecture);

        $this->assertStringContainsString('Нейросети', $html);
        $this->assertStringContainsString('Введение', $html);
        $this->assertStringContainsString('<strong>жирный</strong>', $html);
    }
}
