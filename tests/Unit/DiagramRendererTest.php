<?php

namespace Tests\Unit;

use App\Services\DiagramRenderer;
use Tests\TestCase;

class DiagramRendererTest extends TestCase
{
    public function test_returns_null_for_blank_input(): void
    {
        $r = new DiagramRenderer();
        $this->assertNull($r->render('   ', sys_get_temp_dir(), 'd0'));
    }

    public function test_command_includes_input_output_and_format(): void
    {
        $r = new DiagramRenderer();
        $cmd = $r->renderCommand('/in.mmd', '/out.svg');

        $this->assertContains('-i', $cmd);
        $this->assertContains('/in.mmd', $cmd);
        $this->assertContains('-o', $cmd);
        $this->assertContains('/out.svg', $cmd);
    }
}
