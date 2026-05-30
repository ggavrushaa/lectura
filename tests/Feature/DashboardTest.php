<?php

namespace Tests\Feature;

use App\Enums\LectureStatus;
use App\Models\Lecture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_only_own_lectures(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Lecture::factory()->for($user)->create(['title' => 'Моя лекция', 'status' => LectureStatus::Done]);
        Lecture::factory()->for($other)->create(['title' => 'Чужая лекция']);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Моя лекция')
            ->assertDontSee('Чужая лекция');
    }
}
