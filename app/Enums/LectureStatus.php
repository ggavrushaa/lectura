<?php

namespace App\Enums;

enum LectureStatus: string
{
    case Pending = 'pending';
    case Transcribing = 'transcribing';
    case Summarizing = 'summarizing';
    case Rendering = 'rendering';
    case Done = 'done';
    case Failed = 'failed';

    public function isTerminal(): bool
    {
        return in_array($this, [self::Done, self::Failed], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'В очереди',
            self::Transcribing => 'Расшифровка речи',
            self::Summarizing => 'Составление конспекта',
            self::Rendering => 'Генерация схем',
            self::Done => 'Готово',
            self::Failed => 'Ошибка',
        };
    }
}
