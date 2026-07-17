<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_files', function (Blueprint $table) {
            $table->string('template_key', 100)
                ->nullable()
                ->after('is_current');

            $table->string('template_hash', 64)
                ->nullable()
                ->after('template_key');

            $table->index(
                'template_key',
                'document_files_template_key_index'
            );

            $table->index(
                'template_hash',
                'document_files_template_hash_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('document_files', function (Blueprint $table) {
            $table->dropIndex(
                'document_files_template_key_index'
            );

            $table->dropIndex(
                'document_files_template_hash_index'
            );

            $table->dropColumn([
                'template_key',
                'template_hash',
            ]);
        });
    }
};