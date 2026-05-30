<?php

namespace Tests\Feature;

use App\Enums\LectureStatus;
use App\Jobs\ProcessLectureJob;
use App\Models\Lecture;
use App\Services\AudioPreparer;
use App\Services\DiagramRenderer;
use App\Services\SummaryService;
use App\Services\TranscriptionService;
use App\Support\LectureSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class ProcessLectureJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_path_sets_done_and_fills_summary(): void
    {
        Storage::fake('local');
        $lecture = Lecture::factory()->create([
            'status' => LectureStatus::Pending,
            'audio_path' => 'lectures/1/source.mp3',
        ]);
        Storage::disk('local')->put($lecture->audio_path, 'fakeaudio');

        $this->mock(AudioPreparer::class, fn ($m) => $m
            ->shouldReceive('prepare')->andReturn(['/tmp/seg0.mp3']));
        $this->mock(TranscriptionService::class, fn ($m) => $m
            ->shouldReceive('transcribe')->andReturn('расшифровка'));
        $this->mock(SummaryService::class, fn ($m) => $m
            ->shouldReceive('summarize')->andReturn(LectureSummary::fromArray([
                'title' => 'Готовая лекция', 'summary' => 'S', 'reading_time_min' => 4,
                'sections' => [['heading' => 'H', 'content_markdown' => 'C',
                                'terms' => [], 'diagram_mermaid' => 'graph LR; A-->B']],
                'key_terms' => ['t'], 'takeaways' => ['w'],
            ])));
        $this->mock(DiagramRenderer::class, fn ($m) => $m
            ->shouldReceive('render')->andReturn(['svg' => '/tmp/d.svg', 'png' => '/tmp/d.png']));

        (new ProcessLectureJob($lecture->id))->handle(
            app(AudioPreparer::class), app(TranscriptionService::class),
            app(SummaryService::class), app(DiagramRenderer::class),
        );

        $lecture->refresh();
        $this->assertSame(LectureStatus::Done, $lecture->status);
        $this->assertSame(100, $lecture->progress);
        $this->assertSame('Готовая лекция', $lecture->title);
        $this->assertStringContainsString('## H', $lecture->summary_markdown);
        $this->assertNotEmpty($lecture->summary_json['sections'][0]['diagram_svg']);
    }

    public function test_failure_sets_failed_status(): void
    {
        Storage::fake('local');
        $lecture = Lecture::factory()->create([
            'status' => LectureStatus::Pending,
            'audio_path' => 'lectures/1/source.mp3',
        ]);
        Storage::disk('local')->put($lecture->audio_path, 'fakeaudio');

        $this->mock(AudioPreparer::class, fn ($m) => $m
            ->shouldReceive('prepare')->andThrow(new \RuntimeException('boom')));

        try {
            (new ProcessLectureJob($lecture->id))->handle(
                app(AudioPreparer::class), app(TranscriptionService::class),
                app(SummaryService::class), app(DiagramRenderer::class),
            );
        } catch (\Throwable) {
            // job пробрасывает для ретраев — это ок
        }

        $lecture->refresh();
        $this->assertSame(LectureStatus::Failed, $lecture->status);
        $this->assertStringContainsString('boom', $lecture->error_message);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
