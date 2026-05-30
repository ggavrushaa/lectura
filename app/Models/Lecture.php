<?php

namespace App\Models;

use App\Enums\DetailLevel;
use App\Enums\LectureStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lecture extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => LectureStatus::class,
            'detail_level' => DetailLevel::class,
            'summary_json' => 'array',
            'with_diagrams' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
