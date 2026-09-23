<?php

namespace Tests\Feature\Mcp;

use App\Enums\LectureStatus;
use App\Mcp\Servers\LecturaServer;
use App\Mcp\Tools\SearchLecturesTool;
use App\Models\Lecture;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchLecturesToolTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_a_processed_lecture_by_a_word_in_its_notes(): void
    {
        Lecture::factory()->create([
            'title' => 'Distributed systems, week 4',
            'status' => LectureStatus::Done,
            'summary_markdown' => 'We covered eventual consistency and why quorum reads matter under partition.',
        ]);

        $response = LecturaServer::tool(SearchLecturesTool::class, [
            'query' => 'eventual consistency',
        ]);

        $response->assertOk()
            ->assertSee('Distributed systems, week 4')
            ->assertSee('quorum reads');
    }

    public function test_it_ignores_lectures_that_are_still_processing(): void
    {
        Lecture::factory()->create([
            'title' => 'Distributed systems, week 5',
            'status' => LectureStatus::Summarizing,
            'summary_markdown' => 'Eventual consistency, continued.',
        ]);

        $response = LecturaServer::tool(SearchLecturesTool::class, [
            'query' => 'eventual consistency',
        ]);

        $response->assertOk()->assertSee('No processed lecture mentions');
    }
}
