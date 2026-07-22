<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'document_approval_workflows',
            function (Blueprint $table): void {
                $table->id();

                /*
                 * Matches a key from:
                 * config/signature_templates.php
                 *
                 * Examples:
                 * purchase_order
                 * mrf
                 */
                $table->string(
                    'template_key',
                    100
                )->unique();

                $table->string(
                    'name',
                    150
                );

                /*
                 * Allows the admin to temporarily disable
                 * automatic assignment for a document type.
                 */
                $table->boolean(
                    'is_active'
                )->default(true);

                $table->foreignId(
                    'created_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'updated_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->index([
                    'template_key',
                    'is_active',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'document_approval_workflows'
        );
    }
};