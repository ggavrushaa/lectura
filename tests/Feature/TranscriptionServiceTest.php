<?php

namespace Tests\Feature;

use App\Services\TranscriptionService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TranscriptionServiceTest extends TestCase
{
    private function segments(int $count = 2): array
    {
        $paths = [];
        for ($i = 0; $i < $count; $i++) {
            $path = tempnam(sys_get_temp_dir(), 'seg');
            file_put_contents($path, 'x');
            $paths[] = $path;
        }

        return $paths;
    }

    public function test_concatenates_segment_texts(): void
    {
        config(['services.groq.key' => 'test-key']);

        Http::fake([
            'api.groq.com/*' => Http::sequence()
                ->push(['text' => 'Первая часть.', 'language' => 'russian'])
                ->push(['text' => 'Вторая часть.', 'language' => 'russian']),
        ]);

        $result = app(TranscriptionService::class)->transcribe($this->segments());

        $this->assertSame('Первая часть. Вторая часть.', $result->text);
        Http::assertSentCount(2);
    }

    public function test_detects_language_from_audio(): void
    {
        config(['services.groq.key' => 'test-key']);

        Http::fake([
            'api.groq.com/*' => Http::sequence()
                ->push(['text' => 'First part.', 'language' => 'english'])
                ->push(['text' => 'Second part.', 'language' => 'english']),
        ]);

        $result = app(TranscriptionService::class)->transcribe($this->segments());

        $this->assertSame('en', $result->language);
        Http::assertSent(fn ($req) => ! collect($req->data())->contains(fn ($p) => ($p['name'] ?? null) === 'language'));
    }

    public function test_takes_majority_language_across_segments(): void
    {
        config(['services.groq.key' => 'test-key']);

        Http::fake([
            'api.groq.com/*' => Http::sequence()
                ->push(['text' => '...', 'language' => 'russian'])
                ->push(['text' => 'First.', 'language' => 'english'])
                ->push(['text' => 'Second.', 'language' => 'english']),
        ]);

        $result = app(TranscriptionService::class)->transcribe($this->segments(3));

        $this->assertSame('en', $result->language);
    }

    public function test_explicit_language_overrides_detection(): void
    {
        config(['services.groq.key' => 'test-key']);

        Http::fake(['api.groq.com/*' => Http::response(['text' => 'Текст.', 'language' => 'english'])]);

        $result = app(TranscriptionService::class)->transcribe($this->segments(1), 'ru');

        $this->assertSame('ru', $result->language);
    }
}
