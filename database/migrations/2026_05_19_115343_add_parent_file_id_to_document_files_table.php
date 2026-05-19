<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_files', function (Blueprint $table) {

            $table->foreignId('parent_file_id')
                ->nullable()
                ->after('document_id')
                ->constrained('document_files')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('document_files', function (Blueprint $table) {

            $table->dropConstrainedForeignId('parent_file_id');

        });
    }
};