<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingTest extends TestCase
{
    public function test_landing_is_public_and_shows_cta(): void
    {
        $this->get('/')->assertOk()->assertSee('конспект');
    }
}
