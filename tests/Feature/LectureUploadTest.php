<?php

namespace Tests\Feature;

use App\Jobs\ProcessLectureJob;
use App\Models\Lecture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LectureUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_creates_lecture_and_dispatches_job(): void
    {
        Storage::fake('local');
        Queue::fake();
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('lecture.mp3', 1024, 'audio/mpeg');

        $response = $this->actingAs($user)->post('/lectures', [
            'audio' => $file,
            'detail_level' => 'medium',
            'with_diagrams' => '1',
        ]);

        $lecture = Lecture::first();
        $this->assertNotNull($lecture);
        $this->assertSame($user->id, $lecture->user_id);
        $response->assertRedirect("/lectures/{$lecture->id}");
        Storage::disk('local')->assertExists($lecture->audio_path);
        Queue::assertPushed(ProcessLectureJob::class);
    }

    public function test_accepts_m4a_despite_video_mime(): void
    {
        // .m4a контейнеры finfo детектит как video/3gpp или video/mp4 —
        // валидация по расширению должна их пропускать.
        Storage::fake('local');
        Queue::fake();
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('Бриф.m4a', 2048, 'video/mp4');

        $this->actingAs($user)->post('/lectures', ['audio' => $file])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, Lecture::count());
        Queue::assertPushed(ProcessLectureJob::class);
    }

    public function test_rejects_non_audio(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream');

        $this->actingAs($user)->post('/lectures', ['audio' => $file])
            ->assertSessionHasErrors('audio');
        $this->assertSame(0, Lecture::count());
    }

    public function test_guest_cannot_upload(): void
    {
        $this->post('/lectures', [])->assertRedirect('/login');
    }
}
