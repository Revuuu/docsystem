<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Purchase Order
    |--------------------------------------------------------------------------
    |
    | Each template is identified using the SHA-256 hash of the original,
    | unmodified PDF file.
    |
    | Coordinates are normalized:
    |
    | 0.0 = beginning of the page
    | 1.0 = end of the page
    |
    */

    'purchase_order' => [
        'key' => 'purchase_order',

        'name' => 'Purchase Order',

        /*
         * Optional identifying code printed on the document.
         */
        'document_code' => 'FM-PUR-004-1/5',

        /*
         * A template may support more than one valid PDF hash.
         *
         * The environment variable will be populated in the
         * next implementation step.
         */
        'hashes' => [
            env('PHMC_PO_TEMPLATE_SHA256'),
        ],

        /*
         * Signature blocks must follow the same sequence as
         * the generated approval records.
         */
        'blocks' => [
            [
                'sequence' => 1,
                'role' => 'staff',
                'label' => 'Staff',
                'field_name' => 'step_1',
                'page_number' => 1,

                'x' => 0.070261,
                'y' => 0.781566,
                'width' => 0.179739,
                'height' => 0.031566,
            ],

            [
                'sequence' => 2,
                'role' => 'supervisor',
                'label' => 'Supervisor',
                'field_name' => 'step_2',
                'page_number' => 1,

                'x' => 0.334967,
                'y' => 0.780303,
                'width' => 0.179739,
                'height' => 0.031566,
            ],

            [
                'sequence' => 3,
                'role' => 'depthead',
                'label' => 'Department Head',
                'field_name' => 'step_3a',
                'page_number' => 1,

                'x' => 0.584967,
                'y' => 0.777778,
                'width' => 0.158497,
                'height' => 0.031566,
            ],

            [
                'sequence' => 4,
                'role' => 'division',
                'label' => 'Division Head',
                'field_name' => 'step_3b',
                'page_number' => 1,

                'x' => 0.758170,
                'y' => 0.776515,
                'width' => 0.158497,
                'height' => 0.031566,
            ],

            [
                'sequence' => 5,
                'role' => 'executive',
                'label' => 'Executive',
                'field_name' => 'step_4',
                'page_number' => 1,

                'x' => 0.756536,
                'y' => 0.827020,
                'width' => 0.158497,
                'height' => 0.031566,
            ],
        ],
    ],

];