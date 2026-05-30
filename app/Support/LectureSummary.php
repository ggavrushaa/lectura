<?php

namespace App\Support;

class LectureSummary
{
    /**
     * @param SummarySection[] $sections
     * @param string[] $keyTerms
     * @param string[] $takeaways
     */
    public function __construct(
        public string $title,
        public string $summary,
        public int $readingTimeMin,
        public array $sections,
        public array $keyTerms,
        public array $takeaways,
    ) {}

    public static function fromArray(array $data): self
    {
        $sections = array_map(fn (array $s) => new SummarySection(
            heading: $s['heading'] ?? '',
            contentMarkdown: $s['content_markdown'] ?? '',
            terms: $s['terms'] ?? [],
            diagramMermaid: $s['diagram_mermaid'] ?? null,
            diagramSvgPath: $s['diagram_svg'] ?? null,
            diagramPngPath: $s['diagram_png'] ?? null,
        ), $data['sections'] ?? []);

        return new self(
            title: $data['title'] ?? 'Без названия',
            summary: $data['summary'] ?? '',
            readingTimeMin: (int) ($data['reading_time_min'] ?? 0),
            sections: $sections,
            keyTerms: $data['key_terms'] ?? [],
            takeaways: $data['takeaways'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'summary' => $this->summary,
            'reading_time_min' => $this->readingTimeMin,
            'sections' => array_map(fn (SummarySection $s) => $s->toArray(), $this->sections),
            'key_terms' => $this->keyTerms,
            'takeaways' => $this->takeaways,
        ];
    }
}
