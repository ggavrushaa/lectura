<?php

namespace Tests\Unit;

use App\Enums\DetailLevel;
use App\Enums\LectureStatus;
use Tests\TestCase;

class LectureStatusTest extends TestCase
{
    public function test_statuses_exist_and_terminal_flags(): void
    {
        $this->assertSame('pending', LectureStatus::Pending->value);
        $this->assertSame('done', LectureStatus::Done->value);
        $this->assertTrue(LectureStatus::Done->isTerminal());
        $this->assertTrue(LectureStatus::Failed->isTerminal());
        $this->assertFalse(LectureStatus::Transcribing->isTerminal());
    }

    public function test_detail_levels(): void
    {
        $this->assertSame(['short', 'medium', 'detailed'],
            array_map(fn ($c) => $c->value, DetailLevel::cases()));
    }
}
