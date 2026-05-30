<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_theme_toggle(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('data-theme-toggle', false);
    }
}
