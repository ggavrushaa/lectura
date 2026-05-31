<?php

namespace Tests\Feature;

use App\Services\SummaryService;
use App\Support\SummaryOptions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SummaryServiceTest extends TestCase
{
    private function payload(array $json): array
    {
        return ['choices' => [['message' => ['content' => json_encode($json, JSON_UNESCAPED_UNICODE)]]]];
    }

    public function test_parses_valid_json(): void
    {
        config(['services.openrouter.key' => 'k']);

        Http::fake(['openrouter.ai/*' => Http::response($this->payload([
            'title' => 'Лекция', 'summary' => 'Кратко', 'reading_time_min' => 5,
            'sections' => [['heading' => 'A', 'content_markdown' => 'B', 'terms' => [], 'diagram_mermaid' => null]],
            'key_terms' => ['x'], 'takeaways' => ['y'],
        ]))]);

        $summary = app(SummaryService::class)->summarize('текст', new SummaryOptions());

        $this->assertSame('Лекция', $summary->title);
        $this->assertCount(1, $summary->sections);
    }

    public function test_sends_detailed_max_tokens(): void
    {
        config(['services.openrouter.key' => 'k']);
        config(['services.openrouter.max_tokens' => ['short' => 2500, 'medium' => 4500, 'detailed' => 9000]]);

        Http::fake(['openrouter.ai/*' => Http::response($this->payload([
            'title' => 'T', 'summary' => 'S', 'reading_time_min' => 1,
            'sections' => [], 'key_terms' => [], 'takeaways' => [],
        ]))]);

        app(SummaryService::class)->summarize('текст', new SummaryOptions(
            detailLevel: \App\Enums\DetailLevel::Detailed,
        ));

        Http::assertSent(fn ($req) => ($req->data()['max_tokens'] ?? null) === 9000);
    }

    public function test_repairs_once_when_first_response_is_garbage(): void
    {
        config(['services.openrouter.key' => 'k']);

        Http::fake(['openrouter.ai/*' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => 'Вот ваш ответ: не json']]]])
            ->push($this->payload([
                'title' => 'OK', 'summary' => 'S', 'reading_time_min' => 1,
                'sections' => [], 'key_terms' => [], 'takeaways' => [],
            ])),
        ]);

        $summary = app(SummaryService::class)->summarize('текст', new SummaryOptions());

        $this->assertSame('OK', $summary->title);
        Http::assertSentCount(2);
    }
}
