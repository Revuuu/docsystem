<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDFtk executable
    |--------------------------------------------------------------------------
    |
    | Windows:
    | C:\Program Files (x86)\PDFtk Server\bin\pdftk.exe
    |
    | Ubuntu:
    | /usr/bin/pdftk
    |
    */

    'binary' => env(
        'PDFTK_BINARY',
        'pdftk'
    ),

    /*
    |--------------------------------------------------------------------------
    | Windows execution workaround
    |--------------------------------------------------------------------------
    |
    | Enable this locally on Windows.
    | Disable it on Ubuntu.
    |
    */

    'use_exec' => filter_var(
        env('PDFTK_USE_EXEC', false),
        FILTER_VALIDATE_BOOL
    ),
];