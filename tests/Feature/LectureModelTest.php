<?php

namespace Tests\Feature;

use App\Enums\DetailLevel;
use App\Enums\LectureStatus;
use App\Models\Lecture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LectureModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecture_casts_and_relation(): void
    {
        $user = User::factory()->create();
        $lecture = Lecture::factory()->for($user)->create([
            'status' => LectureStatus::Summarizing,
            'detail_level' => DetailLevel::Medium,
            'summary_json' => ['title' => 'X', 'sections' => []],
        ]);

        $fresh = $lecture->fresh();
        $this->assertInstanceOf(LectureStatus::class, $fresh->status);
        $this->assertInstanceOf(DetailLevel::class, $fresh->detail_level);
        $this->assertIsArray($fresh->summary_json);
        $this->assertSame('X', $fresh->summary_json['title']);
        $this->assertTrue($fresh->user->is($user));
    }
}
