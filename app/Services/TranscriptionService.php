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
        $response = $this->client()
            ->attach('file', file_get_contents($path), basename($path))
            ->post('/audio/transcriptions', array_filter([
                'model' => config('services.groq.model'),
                'language' => $language,
                'response_format' => 'json',
            ]));

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
