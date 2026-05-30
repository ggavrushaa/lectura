<?php

return [
    'max_file_mb' => env('LECTURA_MAX_FILE_MB', 200),
    'max_duration_seconds' => env('LECTURA_MAX_DURATION', 7200), // 2 часа
    'segment_max_mb' => env('LECTURA_SEGMENT_MAX_MB', 20),       // под лимит Groq
    'segment_overlap_seconds' => env('LECTURA_SEGMENT_OVERLAP', 2),
    'max_active_per_user' => env('LECTURA_MAX_ACTIVE', 3),
    'keep_audio' => env('LECTURA_KEEP_AUDIO', false),

    // Бинарники
    'ffmpeg' => env('LECTURA_FFMPEG', 'ffmpeg'),
    'ffprobe' => env('LECTURA_FFPROBE', 'ffprobe'),
    'mmdc' => env('LECTURA_MMDC', base_path('node_modules/.bin/mmdc')),
    'chrome_path' => env('LECTURA_CHROME', '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'),
    'node_path' => env('LECTURA_NODE', '/opt/homebrew/bin/node'),
    'npm_path' => env('LECTURA_NPM', '/opt/homebrew/bin/npm'),
];
