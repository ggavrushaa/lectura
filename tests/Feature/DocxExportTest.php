<?php

namespace Tests\Feature;

use App\Exports\DocxExporter;
use App\Enums\LectureStatus;
use App\Models\Lecture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocxExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_docx_file(): void
    {
        $user = User::factory()->create();
        $lecture = Lecture::factory()->for($user)->create([
            'status' => LectureStatus::Done, 'title' => 'Нейросети',
            'summary_json' => ['title' => 'Нейросети', 'summary' => 'Резюме',
                'sections' => [['heading' => 'Введение', 'content_markdown' => 'текст', 'terms' => [], 'diagram_png' => null]],
                'key_terms' => [], 'takeaways' => ['вывод']],
        ]);

        $response = $this->actingAs($user)->get("/lectures/{$lecture->id}/export/docx");
        $response->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            $response->headers->get('content-type')
        );
    }
}
