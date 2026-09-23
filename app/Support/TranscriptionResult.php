<?php

namespace App\Support;

class TranscriptionResult
{
    public function __construct(
        public string $text,
        public ?string $language = null,
    ) {}
}
