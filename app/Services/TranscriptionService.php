<?php

namespace App\Services;

use App\Support\Language;
use App\Support\TranscriptionResult;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TranscriptionService
{
    public function transcribe(array $segmentPaths, ?string $language = null): TranscriptionResult
    {
        $parts = [];
        $detected = [];

        foreach ($segmentPaths as $path) {
            $segment = $this->transcribeSegment($path, $language);
            $parts[] = trim($segment->text);

            if ($segment->language !== null) {
                $detected[$segment->language] = ($detected[$segment->language] ?? 0) + 1;
            }
        }

        return new TranscriptionResult(
            trim(implode(' ', array_filter($parts))),
            Language::normalize($language) ?? $this->majority($detected),
        );
    }

    /** Отдельный сегмент может быть тишиной или музыкой — берём язык большинства. */
    private function majority(array $counts): ?string
    {
        if ($counts === []) {
            return null;
        }

        arsort($counts);

        return array_key_first($counts);
    }

    private function transcribeSegment(string $path, ?string $language): TranscriptionResult
    {
        // Передаём файл потоком (ресурс fopen), а не file_get_contents:
        // загрузка сегмента целиком в строку + копия в multipart переполняет
        // память PHP на длинных лекциях (множество сегментов).
        $stream = fopen($path, 'r');
        if ($stream === false) {
            throw new RuntimeException("Не удалось открыть сегмент: {$path}");
        }

        try {
            $response = $this->client()
                ->attach('file', $stream, basename($path))
                ->post('/audio/transcriptions', array_filter([
                    'model' => config('services.groq.model'),
                    'language' => $language,
                    'response_format' => 'verbose_json',
                ]));
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $response->throw();

        return new TranscriptionResult(
            (string) ($response->json('text') ?? ''),
            Language::normalize($response->json('language')),
        );
    }

    private function client(): PendingRequest
    {
        $key = config('services.groq.key');
        if (empty($key)) {
            throw new RuntimeException('GROQ_API_KEY не задан');
        }

        return Http::baseUrl(config('services.groq.base'))
            ->withToken($key)
            ->timeout(300)
            ->retry(3, 2000, throw: false);
    }
}
