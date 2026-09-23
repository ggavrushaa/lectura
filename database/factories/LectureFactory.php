<?php

namespace Database\Factories;

use App\Enums\DetailLevel;
use App\Enums\LectureStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LectureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'original_filename' => 'lecture.mp3',
            'audio_path' => 'lectures/test/audio.mp3',
            'duration_seconds' => 2880,
            'language' => null,
            'status' => LectureStatus::Pending,
            'progress' => 0,
            'with_diagrams' => true,
            'detail_level' => DetailLevel::Medium,
        ];
    }
}
