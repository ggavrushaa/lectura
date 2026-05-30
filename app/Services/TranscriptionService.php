<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TranscriptionService
{
    public function transcribe(array $segmentPaths, ?string $language = 'ru'): string
    {
        $parts = [];
        foreach ($segmentPaths as $path) {
            $parts[] = trim($this->transcribeSegment($path, $language));
        }

        return trim(implode(' ', array_filter($parts)));
    }

    private function transcribeSegment(string $path, ?string $language): string
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
                    'response_format' => 'json',
                ]));
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $response->throw();

        return (string) ($response->json('text') ?? '');
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
