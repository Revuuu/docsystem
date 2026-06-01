<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'event')) {
                $table->string('event')->nullable()->after('user_id');
            }

            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable()->after('action');
            }

            if (!Schema::hasColumn('audit_logs', 'metadata')) {
                $table->text('metadata')->nullable()->after('new_values');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'event')) {
                $table->dropColumn('event');
            }

            if (Schema::hasColumn('audit_logs', 'description')) {
                $table->dropColumn('description');
            }

            if (Schema::hasColumn('audit_logs', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};