<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('document_signature_blocks', 'slot_key')) {
            return;
        }

        Schema::table(
            'document_signature_blocks',
            function (Blueprint $table): void {
                $table->string('slot_key', 100)
                    ->nullable()
                    ->after('field_name');
                $table->index(
                    ['template_key', 'slot_key'],
                    'document_signature_blocks_template_slot_index'
                );
            }
        );
    }

    public function down(): void
    {
        if (!Schema::hasColumn('document_signature_blocks', 'slot_key')) {
            return;
        }

        Schema::table(
            'document_signature_blocks',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'document_signature_blocks_template_slot_index'
                );
                $table->dropColumn('slot_key');
            }
        );
    }
};