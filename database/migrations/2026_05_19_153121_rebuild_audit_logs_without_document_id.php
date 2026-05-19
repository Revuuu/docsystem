<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::statement('
            CREATE TABLE audit_logs_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                table_name VARCHAR NOT NULL,
                table_id INTEGER NOT NULL,
                action VARCHAR NOT NULL,
                old_values TEXT NULL,
                new_values TEXT NULL,
                ip_address VARCHAR NULL,
                user_agent TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY(user_id)
                    REFERENCES users(id)
                    ON DELETE SET NULL
            )
        ');

        DB::statement('
    INSERT INTO audit_logs_new (
        id,
        user_id,
        table_name,
        table_id,
        action,
        old_values,
        new_values,
        ip_address,
        user_agent,
        created_at,
        updated_at
    )
    SELECT
        id,
        user_id,

        COALESCE(table_name, "documents"),

        COALESCE(table_id, 0),

        action,
        old_values,
        new_values,
        ip_address,
        user_agent,
        created_at,
        updated_at

    FROM audit_logs
');

        DB::statement('DROP TABLE audit_logs');

        DB::statement('ALTER TABLE audit_logs_new RENAME TO audit_logs');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        //
    }
};