<?php

namespace Tests\Unit;

use Tests\TestCase;

class LecturaConfigTest extends TestCase
{
    public function test_lectura_config_has_required_keys(): void
    {
        $this->assertIsInt(config('lectura.max_file_mb'));
        $this->assertIsInt(config('lectura.max_duration_seconds'));
        $this->assertIsInt(config('lectura.segment_max_mb'));
        $this->assertIsInt(config('lectura.max_active_per_user'));
        $this->assertIsBool(config('lectura.keep_audio'));
        $this->assertArrayHasKey('chrome_path', config('lectura'));
        $this->assertNotEmpty(config('services.groq.model'));
        $this->assertNotEmpty(config('services.openrouter.model'));
    }
}
