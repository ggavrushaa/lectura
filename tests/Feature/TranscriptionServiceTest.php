<?php

namespace Tests\Feature;

use App\Services\TranscriptionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TranscriptionServiceTest extends TestCase
{
    public function test_concatenates_segment_texts(): void
    {
        config(['services.groq.key' => 'test-key']);

        Http::fake([
            'api.groq.com/*' => Http::sequence()
                ->push(['text' => 'Первая часть.'])
                ->push(['text' => 'Вторая часть.']),
        ]);

        // два временных файла-сегмента
        $a = tempnam(sys_get_temp_dir(), 'seg');
        $b = tempnam(sys_get_temp_dir(), 'seg');
        file_put_contents($a, 'x');
        file_put_contents($b, 'y');

        $text = app(TranscriptionService::class)->transcribe([$a, $b], 'ru');

        $this->assertSame('Первая часть. Вторая часть.', $text);
        Http::assertSentCount(2);
    }
}
