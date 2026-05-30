<?php

namespace App\Support;

class SummarySection
{
    /** @param string[] $terms */
    public function __construct(
        public string $heading,
        public string $contentMarkdown,
        public array $terms = [],
        public ?string $diagramMermaid = null,
        public ?string $diagramSvgPath = null,
        public ?string $diagramPngPath = null,
    ) {}

    public function toArray(): array
    {
        return [
            'heading' => $this->heading,
            'content_markdown' => $this->contentMarkdown,
            'terms' => $this->terms,
            'diagram_mermaid' => $this->diagramMermaid,
            'diagram_svg' => $this->diagramSvgPath,
            'diagram_png' => $this->diagramPngPath,
        ];
    }
}
