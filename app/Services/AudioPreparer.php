<?php

namespace App\Services;

use App\Exceptions\AudioException;
use Symfony\Component\Process\Process;

class AudioPreparer
{
    private string $ffmpeg;
    private string $ffprobe;
    private int $segmentSeconds;
    private int $overlap;

    public function __construct()
    {
        $this->ffmpeg = config('lectura.ffmpeg');
        $this->ffprobe = config('lectura.ffprobe');
        // ~600с сегмент держит размер 16kHz mono mp3 под лимит Groq
        $this->segmentSeconds = 600;
        $this->overlap = (int) config('lectura.segment_overlap_seconds', 2);
    }

    /** @return list<string> */
    public function probeCommand(string $path): array
    {
        return [
            $this->ffprobe, '-v', 'error',
            '-show_entries', 'format=duration',
            '-of', 'default=noprint_wrappers=1:nokey=1',
            $path,
        ];
    }

    /** @return list<string> */
    public function normalizeCommand(string $in, string $out): array
    {
        return [
            $this->ffmpeg, '-y', '-i', $in,
            '-ar', '16000', '-ac', '1', '-c:a', 'libmp3lame', '-b:a', '64k',
            $out,
        ];
    }

    public function segmentCount(int $durationSeconds, int $segmentSeconds): int
    {
        return max(1, (int) ceil($durationSeconds / $segmentSeconds));
    }

    /** @return array{duration:int,valid:bool} */
    public function probe(string $absPath): array
    {
        $process = new Process($this->probeCommand($absPath));
        $process->run();

        if (! $process->isSuccessful()) {
            return ['duration' => 0, 'valid' => false];
        }

        $duration = (int) round((float) trim($process->getOutput()));

        return ['duration' => $duration, 'valid' => $duration > 0];
    }

    /**
     * Нормализует и при необходимости режет на сегменты по времени с перекрытием.
     * @return list<string> абсолютные пути сегментов (mp3)
     */
    public function prepare(string $absPath, string $workDir): array
    {
        if (! is_dir($workDir) && ! mkdir($workDir, 0775, true) && ! is_dir($workDir)) {
            throw new AudioException("Не удалось создать каталог: {$workDir}");
        }

        $normalized = $workDir.'/normalized.mp3';
        $this->runOrFail($this->normalizeCommand($absPath, $normalized), 'normalize');

        $duration = $this->probe($normalized)['duration'];
        if ($duration <= $this->segmentSeconds) {
            return [$normalized];
        }

        $segments = [];
        $count = $this->segmentCount($duration, $this->segmentSeconds);
        for ($i = 0; $i < $count; $i++) {
            $start = max(0, $i * $this->segmentSeconds - ($i > 0 ? $this->overlap : 0));
            $out = sprintf('%s/seg_%03d.mp3', $workDir, $i);
            $cmd = [
                $this->ffmpeg, '-y', '-ss', (string) $start,
                '-t', (string) ($this->segmentSeconds + $this->overlap),
                '-i', $normalized, '-c', 'copy', $out,
            ];
            $this->runOrFail($cmd, "segment {$i}");
            $segments[] = $out;
        }

        return $segments;
    }

    private function runOrFail(array $cmd, string $stage): void
    {
        $process = new Process($cmd);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new AudioException("ffmpeg ({$stage}) failed: ".$process->getErrorOutput());
        }
    }
}
