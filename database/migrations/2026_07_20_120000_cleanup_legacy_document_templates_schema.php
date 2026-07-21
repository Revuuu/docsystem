<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Do nothing when the current document_templates
         * schema is already installed.
         */
        if (
            Schema::hasTable('document_templates') &&
            Schema::hasColumn(
                'document_templates',
                'template_key'
            )
        ) {
            return;
        }

        /*
         * Remove the obsolete document_files relationship
         * before dropping the legacy template table.
         */
        if (
            Schema::hasTable('document_files') &&
            Schema::hasColumn(
                'document_files',
                'document_template_id'
            )
        ) {
            Schema::table(
                'document_files',
                function (Blueprint $table): void {
                    $table->dropConstrainedForeignId(
                        'document_template_id'
                    );
                }
            );
        }

        /*
         * Remove the legacy table whose columns were:
         *
         * id
         * name
         * type
         * file_path
         * updated_by
         * created_at
         * updated_at
         */
        if (
            Schema::hasTable('document_templates') &&
            !Schema::hasColumn(
                'document_templates',
                'template_key'
            )
        ) {
            Schema::drop('document_templates');
        }
    }

    public function down(): void
    {
        /*
         * The obsolete template schema is intentionally
         * not restored.
         */
    }
};