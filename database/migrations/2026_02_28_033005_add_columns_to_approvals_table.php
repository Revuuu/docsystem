<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->unsignedBigInteger('document_id')->after('id');
            $table->unsignedBigInteger('user_id')->after('document_id');
            $table->string('status')->default('pending')->after('user_id');
            $table->timestamp('signed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropColumn(['document_id', 'user_id', 'status', 'signed_at']);
        });
    }
};