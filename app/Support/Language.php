<?php

namespace App\Support;

final class Language
{
    /** Whisper возвращает язык полным английским названием, а не кодом ISO. */
    private const CODES = [
        'english' => 'en', 'russian' => 'ru', 'ukrainian' => 'uk', 'german' => 'de',
        'french' => 'fr', 'spanish' => 'es', 'italian' => 'it', 'polish' => 'pl',
        'portuguese' => 'pt', 'dutch' => 'nl', 'turkish' => 'tr', 'chinese' => 'zh',
        'japanese' => 'ja', 'korean' => 'ko', 'arabic' => 'ar', 'hebrew' => 'he',
        'czech' => 'cs', 'kazakh' => 'kk',
    ];

    private const NAMES = [
        'en' => ['английском', 'English'],
        'ru' => ['русском', 'Russian'],
        'uk' => ['украинском', 'Ukrainian'],
        'de' => ['немецком', 'German'],
        'fr' => ['французском', 'French'],
        'es' => ['испанском', 'Spanish'],
        'it' => ['итальянском', 'Italian'],
        'pl' => ['польском', 'Polish'],
        'pt' => ['португальском', 'Portuguese'],
        'nl' => ['нидерландском', 'Dutch'],
        'tr' => ['турецком', 'Turkish'],
        'zh' => ['китайском', 'Chinese'],
        'ja' => ['японском', 'Japanese'],
        'ko' => ['корейском', 'Korean'],
        'ar' => ['арабском', 'Arabic'],
        'he' => ['иврите', 'Hebrew'],
        'cs' => ['чешском', 'Czech'],
        'kk' => ['казахском', 'Kazakh'],
    ];

    public static function normalize(?string $raw): ?string
    {
        $raw = strtolower(trim((string) $raw));

        if ($raw === '') {
            return null;
        }

        if (isset(self::CODES[$raw])) {
            return self::CODES[$raw];
        }

        return preg_match('/^[a-z]{2}$/', $raw) === 1 ? $raw : null;
    }

    public static function instruction(?string $code): string
    {
        $code = self::normalize($code);

        if ($code === null || ! isset(self::NAMES[$code])) {
            return 'Пиши конспект на том же языке, на котором говорят в расшифровке. '
                .'Не переводи материал на другой язык.';
        }

        [$case, $english] = self::NAMES[$code];

        return 'Пиши ВЕСЬ конспект — заголовки, текст разделов, глоссарий, квиз, выводы — '
            ."на {$case} языке ({$english}). Не переводи материал на другой язык.";
    }
}
