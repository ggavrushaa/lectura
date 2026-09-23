<?php

namespace App\Support;

use App\Enums\DetailLevel;

class SummaryOptions
{
    public function __construct(
        public DetailLevel $detailLevel = DetailLevel::Medium,
        public bool $withDiagrams = true,
        public ?string $language = null,
    ) {}
}
