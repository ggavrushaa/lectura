<?php

namespace Tests\Feature;

use App\Models\Lecture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_others_cannot(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $lecture = Lecture::factory()->for($owner)->create();

        $this->assertTrue($owner->can('view', $lecture));
        $this->assertFalse($other->can('view', $lecture));
        $this->assertTrue($owner->can('delete', $lecture));
        $this->assertFalse($other->can('delete', $lecture));
    }
}
