<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME =
        'approval_workflow_steps_role_unique';

    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement(
                'DROP INDEX IF EXISTS ' . self::INDEX_NAME
            );

            return;
        }

        Schema::table(
            'document_approval_workflow_steps',
            function (Blueprint $table): void {
                $table->dropUnique(self::INDEX_NAME);
            }
        );
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement(sprintf(
                'CREATE UNIQUE INDEX %s ON document_approval_workflow_steps (document_approval_workflow_id, role)',
                self::INDEX_NAME
            ));

            return;
        }

        Schema::table(
            'document_approval_workflow_steps',
            function (Blueprint $table): void {
                $table->unique(
                    ['document_approval_workflow_id', 'role'],
                    self::INDEX_NAME
                );
            }
        );
    }
};