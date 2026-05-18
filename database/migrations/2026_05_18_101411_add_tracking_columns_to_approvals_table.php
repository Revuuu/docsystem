<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('approvals', function (Blueprint $table) {

            $table->timestamp('received_at')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->bigInteger('duration_seconds')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {

            $table->dropColumn([
                'received_at',
                'completed_at',
                'duration_seconds',
            ]);

        });
    }
};
