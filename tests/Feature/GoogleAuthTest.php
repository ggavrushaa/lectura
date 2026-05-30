<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_routes_404_when_disabled(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get('/auth/google/redirect')->assertNotFound();
    }

    public function test_register_hides_google_button_when_disabled(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get('/register')->assertOk()->assertDontSee('Зарегистрироваться с Google');
    }

    public function test_register_shows_google_button_when_enabled(): void
    {
        config([
            'services.google.client_id' => 'demo.apps.googleusercontent.com',
            'services.google.client_secret' => 'demo-secret',
        ]);

        $this->get('/register')->assertOk()->assertSee('Зарегистрироваться с Google');
    }
}
