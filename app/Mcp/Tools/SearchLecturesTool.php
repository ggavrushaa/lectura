<?php

namespace App\Mcp\Tools;

use App\Enums\LectureStatus;
use App\Models\Lecture;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('search_lectures')]
#[IsReadOnly(true)]
#[IsIdempotent(true)]
#[Description('
    Searches processed lecture notes by keyword and
    returns every matching lecture with the passage where the keyword appears.
    Use it to answer questions about what a lecture actually covered.
')]

class SearchLecturesTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:5'],
        ]);

        $needle = $validated['query'];

        $lectures = Lecture::query()
            ->where('status', LectureStatus::Done)
            ->whereNotNull('summary_markdown')
            ->where('summary_markdown', 'like', '%'.$needle.'%')
            ->latest()
            ->limit($validated['limit'] ?? 3)
            ->get(['id', 'title', 'summary_markdown', 'created_at']);

        if ($lectures->isEmpty()) {
            return Response::text("No processed lecture mentions \"{$needle}\".");
        }

        return Response::structured([
            'query' => $needle,
            'matches' => $lectures->count(),
            'lectures' => $lectures->map(fn (Lecture $lecture): array => [
                'id' => $lecture->id,
                'title' => $lecture->title,
                'recorded_on' => $lecture->created_at->toDateString(),
                'passage' => $this->passageAround($lecture->summary_markdown, $needle),
            ])->all(),
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Keyword or phrase to look for inside the lecture notes.')
                ->required(),
            'limit' => $schema->integer()
                ->description('How many lectures to return at most. Defaults to 3, never more than 5.'),
        ];
    }

    private function passageAround(string $markdown, string $needle, int $radius = 55): string
    {
        $plain = trim(preg_replace(['/[#>*`_]+/u', '/\s+/u'], ['', ' '], $markdown));
        $position = mb_stripos($plain, $needle);

        if ($position === false) {
            return mb_substr($plain, 0, $radius * 2).'…';
        }

        $start = max(0, $position - $radius);
        $passage = mb_substr($plain, $start, $radius * 2 + mb_strlen($needle));

        return trim(($start > 0 ? '…' : '').$passage.'…');
    }
}
