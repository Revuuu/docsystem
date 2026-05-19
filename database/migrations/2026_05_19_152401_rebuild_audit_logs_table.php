<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            $table->string('table_name')
                ->nullable()
                ->after('document_id');

            $table->unsignedBigInteger('table_id')
                ->nullable()
                ->after('table_name');

            $table->json('old_values')
                ->nullable()
                ->after('action');

            $table->json('new_values')
                ->nullable()
                ->after('old_values');

            $table->text('user_agent')
                ->nullable()
                ->after('ip_address');

        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {

            $table->dropColumn([
                'table_name',
                'table_id',
                'old_values',
                'new_values',
                'user_agent',
            ]);

        });
    }
};