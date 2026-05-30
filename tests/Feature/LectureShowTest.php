<?php

namespace Tests\Feature;

use App\Enums\LectureStatus;
use App\Models\Lecture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LectureShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_processing_shows_progress(): void
    {
        $user = User::factory()->create();
        $lecture = Lecture::factory()->for($user)->create([
            'status' => LectureStatus::Transcribing, 'progress' => 35,
        ]);

        $this->actingAs($user)->get("/lectures/{$lecture->id}")
            ->assertOk()
            ->assertSee('lectures.status', false) // url для поллинга присутствует
            ->assertSee('35', false);
    }

    public function test_done_shows_summary_and_export(): void
    {
        $user = User::factory()->create();
        $lecture = Lecture::factory()->for($user)->create([
            'status' => LectureStatus::Done, 'progress' => 100,
            'title' => 'Нейросети',
            'summary_markdown' => "# Нейросети\n\n## Введение\nтекст",
            'summary_json' => ['title' => 'Нейросети', 'summary' => 'Резюме',
                'sections' => [['heading' => 'Введение', 'content_markdown' => 'текст', 'terms' => [], 'diagram_svg' => null]],
                'key_terms' => ['нейрон'], 'takeaways' => ['вывод']],
        ]);

        $this->actingAs($user)->get("/lectures/{$lecture->id}")
            ->assertOk()
            ->assertSee('Нейросети')
            ->assertSee('Введение')
            ->assertSee('PDF');
    }

    public function test_status_endpoint_returns_json(): void
    {
        $user = User::factory()->create();
        $lecture = Lecture::factory()->for($user)->create([
            'status' => LectureStatus::Summarizing, 'progress' => 60,
        ]);

        $this->actingAs($user)->getJson("/lectures/{$lecture->id}/status")
            ->assertOk()
            ->assertJson(['status' => 'summarizing', 'progress' => 60]);
    }
}
