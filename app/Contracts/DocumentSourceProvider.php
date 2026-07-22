<?php

namespace App\Contracts;

use App\Data\RetrievedDocument;

interface DocumentSourceProvider
{
    /**
     * Retrieve one document using its configured document-type key.
     *
     * Example:
     * purchase_order
     */
    public function retrieve(
        string $documentType
    ): RetrievedDocument;
}