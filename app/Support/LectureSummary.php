<?php

namespace App\Support;

class LectureSummary
{
    /**
     * @param SummarySection[] $sections
     * @param array<int,array{term:string,definition:string}> $glossary
     * @param string[] $keyTerms
     * @param array<int,array{question:string,answer:string}> $quiz
     * @param string[] $takeaways
     */
    public function __construct(
        public string $title,
        public string $summary,
        public int $readingTimeMin,
        public array $sections,
        public array $keyTerms,
        public array $takeaways,
        public ?string $subject = null,
        public ?string $level = null,
        public array $glossary = [],
        public array $quiz = [],
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

        // Глоссарий: нормализуем к [{term, definition}], отбрасываем пустые.
        $glossary = array_values(array_filter(array_map(function ($g) {
            if (! is_array($g) || empty($g['term'])) {
                return null;
            }
            return ['term' => (string) $g['term'], 'definition' => (string) ($g['definition'] ?? '')];
        }, $data['glossary'] ?? []), fn ($g) => $g !== null));

        // Квиз: нормализуем к [{question, answer}], отбрасываем пустые.
        $quiz = array_values(array_filter(array_map(function ($q) {
            if (! is_array($q) || empty($q['question'])) {
                return null;
            }
            return ['question' => (string) $q['question'], 'answer' => (string) ($q['answer'] ?? '')];
        }, $data['quiz'] ?? []), fn ($q) => $q !== null));

        $level = $data['level'] ?? null;
        $level = in_array($level, ['intro', 'intermediate', 'advanced'], true) ? $level : null;

        return new self(
            title: $data['title'] ?? 'Без названия',
            summary: $data['summary'] ?? '',
            readingTimeMin: (int) ($data['reading_time_min'] ?? 0),
            sections: $sections,
            keyTerms: $data['key_terms'] ?? [],
            takeaways: $data['takeaways'] ?? [],
            subject: ! empty($data['subject']) ? (string) $data['subject'] : null,
            level: $level,
            glossary: $glossary,
            quiz: $quiz,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'subject' => $this->subject,
            'level' => $this->level,
            'summary' => $this->summary,
            'reading_time_min' => $this->readingTimeMin,
            'sections' => array_map(fn (SummarySection $s) => $s->toArray(), $this->sections),
            'glossary' => $this->glossary,
            'key_terms' => $this->keyTerms,
            'quiz' => $this->quiz,
            'takeaways' => $this->takeaways,
        ];
    }
}
