<?php

namespace App\Jobs;

use App\Enums\LectureStatus;
use App\Models\Lecture;
use App\Services\AudioPreparer;
use App\Services\DiagramRenderer;
use App\Services\SummaryMarkdownBuilder;
use App\Services\SummaryService;
use App\Services\TranscriptionService;
use App\Support\SummaryOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessLectureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 1800;

    public array $backoff = [30, 120];

    public function __construct(public int $lectureId)
    {
        $this->onQueue('transcribe');
    }

    public function handle(
        AudioPreparer $audio,
        TranscriptionService $stt,
        SummaryService $summarizer,
        DiagramRenderer $diagrams,
    ): void {
        // Обработка аудио/транскриптов прожорлива по памяти — поднимаем лимит.
        @ini_set('memory_limit', '512M');

        $lecture = Lecture::findOrFail($this->lectureId);

        try {
            $disk = Storage::disk('local');
            $absSource = $disk->path($lecture->audio_path);
            $workDir = $disk->path("lectures/{$lecture->id}/work");

            $this->update($lecture, LectureStatus::Transcribing, 10);
            $segments = $audio->prepare($absSource, $workDir);

            $this->update($lecture, LectureStatus::Transcribing, 35);
            $transcription = $stt->transcribe($segments, $lecture->language);
            $lecture->update([
                'transcript_text' => $transcription->text,
                'language' => $transcription->language ?? $lecture->language,
            ]);

            $this->update($lecture, LectureStatus::Summarizing, 60);
            $summary = $summarizer->summarize($transcription->text, new SummaryOptions(
                detailLevel: $lecture->detail_level,
                withDiagrams: $lecture->with_diagrams,
                language: $lecture->language,
            ));

            $this->update($lecture, LectureStatus::Rendering, 80);
            if ($lecture->with_diagrams) {
                $dir = $disk->path("lectures/{$lecture->id}/diagrams");
                foreach ($summary->sections as $i => $section) {
                    if (! $section->diagramMermaid) {
                        continue;
                    }
                    $paths = $diagrams->render($section->diagramMermaid, $dir, "d{$i}");
                    if ($paths) {
                        $section->diagramSvgPath = $paths['svg'];
                        $section->diagramPngPath = $paths['png'];
                    } else {
                        $section->diagramMermaid = null; // битую — выкидываем
                    }
                }
            }

            $markdown = app(SummaryMarkdownBuilder::class)->build($summary);

            $lecture->update([
                'title' => $summary->title ?: $lecture->title,
                'summary_json' => $summary->toArray(),
                'summary_markdown' => $markdown,
                'status' => LectureStatus::Done,
                'progress' => 100,
                'error_message' => null,
            ]);

            if (! config('lectura.keep_audio')) {
                $disk->delete($lecture->audio_path);
                $disk->deleteDirectory("lectures/{$lecture->id}/work");
            }
        } catch (Throwable $e) {
            // Технические детали — в лог; пользователю — понятное сообщение по этапу.
            Log::error('Lecture processing failed', [
                'lecture_id' => $lecture->id,
                'stage' => $lecture->status->value,
                'error' => $e->getMessage(),
            ]);
            $lecture->update([
                'status' => LectureStatus::Failed,
                'error_message' => $this->friendlyError($lecture->status),
            ]);
            throw $e; // пусть Horizon ретраит
        }
    }

    private function update(Lecture $lecture, LectureStatus $status, int $progress): void
    {
        $lecture->update(['status' => $status, 'progress' => $progress]);
    }

    /**
     * Человеко-понятное сообщение в зависимости от этапа, на котором упала обработка.
     */
    private function friendlyError(LectureStatus $stage): string
    {
        return match ($stage) {
            LectureStatus::Transcribing => 'Не удалось распознать речь в записи. Проверьте, что это аудиофайл с разборчивым голосом, и попробуйте снова.',
            LectureStatus::Summarizing => 'Не удалось составить конспект по расшифровке. Попробуйте повторить через минуту.',
            LectureStatus::Rendering => 'Конспект готов, но не удалось оформить схемы. Попробуйте повторить.',
            default => 'Не удалось обработать запись. Попробуйте повторить, а если ошибка повторится — загрузите файл заново.',
        };
    }

    /**
     * Вызывается Horizon при окончательном провале (включая таймаут и фатальные
     * ошибки, которые не ловит try/catch внутри handle). Гарантирует, что лекция
     * не зависнет в промежуточном статусе.
     */
    public function failed(?Throwable $e): void
    {
        $lecture = Lecture::find($this->lectureId);
        if ($lecture && ! $lecture->status->isTerminal()) {
            Log::error('Lecture job failed permanently', [
                'lecture_id' => $this->lectureId,
                'error' => $e?->getMessage(),
            ]);
            $lecture->update([
                'status' => LectureStatus::Failed,
                'error_message' => $this->friendlyError($lecture->status),
            ]);
        }
    }
}
