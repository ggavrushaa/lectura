<?php

namespace Tests\Feature;

use App\Enums\LectureStatus;
use App\Models\Lecture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkdownExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_downloads_markdown(): void
    {
        $user = User::factory()->create();
        $lecture = Lecture::factory()->for($user)->create([
            'status' => LectureStatus::Done,
            'summary_markdown' => "# Заголовок\n\nтекст",
        ]);

        $response = $this->actingAs($user)->get("/lectures/{$lecture->id}/export/md");
        $response->assertOk();
        $this->assertStringContainsString('text/markdown', $response->headers->get('content-type'));
        $this->assertStringContainsString('# Заголовок', $response->streamedContent());
    }

    public function test_others_cannot_export(): void
    {
        $user = User::factory()->create();
        $lecture = Lecture::factory()->for(User::factory())->create(['status' => LectureStatus::Done]);
        $this->actingAs($user)->get("/lectures/{$lecture->id}/export/md")->assertForbidden();
    }
}
