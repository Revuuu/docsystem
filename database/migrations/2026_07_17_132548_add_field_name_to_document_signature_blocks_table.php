<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'document_signature_blocks',
            function (Blueprint $table): void {
                $table
                    ->string('field_name', 100)
                    ->nullable()
                    ->after('label');

                $table->unique(
                    ['document_id', 'field_name'],
                    'document_signature_blocks_document_field_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'document_signature_blocks',
            function (Blueprint $table): void {
                $table->dropUnique(
                    'document_signature_blocks_document_field_unique'
                );

                $table->dropColumn('field_name');
            }
        );
    }
};