<?php

namespace App\Policies;

use App\Models\Lecture;
use App\Models\User;

class LecturePolicy
{
    public function view(User $user, Lecture $lecture): bool
    {
        return $lecture->user_id === $user->id;
    }

    public function delete(User $user, Lecture $lecture): bool
    {
        return $lecture->user_id === $user->id;
    }
}
