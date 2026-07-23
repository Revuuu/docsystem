<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chrome / Chromium executable
    |--------------------------------------------------------------------------
    |
    | Set CHROME_BINARY explicitly in production. A command name such as
    | google-chrome or chromium is accepted when it is available on PATH.
    |
    */
    'chrome_binary' => env('CHROME_BINARY'),

    'timeout_seconds' => (int) env(
        'PDF_RENDER_TIMEOUT',
        90
    ),

    'temporary_directory' => env(
        'PDF_RENDER_TEMP_DIRECTORY',
        storage_path('app/private/tmp/po-render')
    ),
];