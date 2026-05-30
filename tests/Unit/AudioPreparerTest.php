<?php

namespace Tests\Unit;

use App\Services\AudioPreparer;
use Tests\TestCase;

class AudioPreparerTest extends TestCase
{
    public function test_probe_command_targets_file(): void
    {
        $p = new AudioPreparer();
        $cmd = $p->probeCommand('/tmp/a.mp3');

        $this->assertContains('-show_entries', $cmd);
        $this->assertContains('format=duration', $cmd);
        $this->assertContains('/tmp/a.mp3', $cmd);
    }

    public function test_normalize_command_downmixes_to_16k_mono(): void
    {
        $p = new AudioPreparer();
        $cmd = $p->normalizeCommand('/in.m4a', '/out.mp3');

        $this->assertContains('-ar', $cmd);
        $this->assertContains('16000', $cmd);
        $this->assertContains('-ac', $cmd);
        $this->assertContains('1', $cmd);
        $this->assertContains('/out.mp3', $cmd);
    }

    public function test_segment_count_for_duration(): void
    {
        $p = new AudioPreparer();
        // 3600с, сегмент по 600с, перекрытие 2с → 6 сегментов
        $this->assertSame(6, $p->segmentCount(3600, 600));
        $this->assertSame(1, $p->segmentCount(500, 600));
    }
}
