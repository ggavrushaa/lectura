<?php

namespace Tests\Unit;

use App\Support\ConspectusRenderer;
use Tests\TestCase;

class ConspectusRendererTest extends TestCase
{
    public function test_renders_callout(): void
    {
        $html = ConspectusRenderer::html("> [!important] Это важно\n\nОбычный текст.");
        $this->assertStringContainsString('callout-important', $html);
        $this->assertStringContainsString('Это важно', $html);
        $this->assertStringContainsString('Обычный текст', $html);
    }

    public function test_strips_raw_html(): void
    {
        $html = ConspectusRenderer::html('Текст <script>alert(1)</script> ещё');
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_plain_markdown_still_works(): void
    {
        $html = ConspectusRenderer::html('**жирный** текст');
        $this->assertStringContainsString('<strong>жирный</strong>', $html);
    }
}
