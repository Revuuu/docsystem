<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Temporary document sources
    |--------------------------------------------------------------------------
    |
    | Each entry represents a document type that can be imported
    | automatically.
    |
    | The array key must match a template key inside:
    |
    | config/signature_templates.php
    |
    */

    'document_types' => [

        'purchase_order' => [

            /*
             * Must match:
             *
             * config/signature_templates.purchase_order
             */
            'template_key' => 'purchase_order',

            /*
             * Default document title used when the PDF is imported.
             */
            'title' => 'Purchase Order',

            /*
             * Temporary incoming PDF.
             *
             * This is the file that will become the actual document
             * and receive signatures.
             */
            'source_path' => resource_path(
                'templates/temp_file/po_temp.pdf'
            ),

            /*
             * Reference PDF containing the Sejda signature fields.
             *
             * This file is used only for validation. It will not be
             * visually merged over the incoming PDF.
             */
            'reference_template_path' => resource_path(
                'templates/overlays/po_overlay.pdf'
            ),

            /*
             * Private storage directory for imported documents.
             *
             * This is relative to storage/app/private.
             */
            'storage_directory' => 'document',

        ],

    ],

];