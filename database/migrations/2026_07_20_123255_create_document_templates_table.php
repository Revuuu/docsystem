<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();

            /*
             * Logical document type.
             *
             * Example:
             * purchase_order
             */
            $table->string('template_key', 100);

            $table->string('name', 150);

            /*
             * Version number for this template_key.
             *
             * Examples:
             * purchase_order version 1
             * purchase_order version 2
             */
            $table->unsignedInteger('version');

            /*
             * Relative path under:
             * storage/app/private/
             *
             * Example:
             * templates/purchase_order/v1.pdf
             */
            $table->string('file_path');

            /*
             * Only one version per template_key may be active.
             */
            $table->boolean('is_active')
                ->default(false);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
             * Prevent duplicate version numbers under
             * the same template type.
             */
            $table->unique(
                ['template_key', 'version'],
                'document_templates_key_version_unique'
            );

            $table->index([
                'template_key',
                'is_active',
            ]);
        });

        /*
         * PostgreSQL protection:
         * Only one active row is allowed for each template_key.
         */
        DB::statement(
            'CREATE UNIQUE INDEX document_templates_one_active_per_key
             ON document_templates (template_key)
             WHERE is_active = true'
        );
    }

    public function down(): void
    {
        DB::statement(
            'DROP INDEX IF EXISTS document_templates_one_active_per_key'
        );

        Schema::dropIfExists('document_templates');
    }
};