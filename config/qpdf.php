<?php

return [

    /*
    |--------------------------------------------------------------------------
    | QPDF executable
    |--------------------------------------------------------------------------
    |
    | Use an absolute executable path when available so Apache, queues,
    | scheduled tasks, and CLI commands do not depend on the Windows PATH.
    |
    */

    'binary' => env(
        'QPDF_BINARY',
        'qpdf'
    ),

];