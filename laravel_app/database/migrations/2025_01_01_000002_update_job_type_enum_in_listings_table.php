<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Update job_type ENUM on MySQL to remove 'remote' and 'hybrid' values.
 * These work modes are now handled by the work_mode column.
 *
 * This migration is a no-op on SQLite (used for testing) because:
 *   1. SQLite does not support ALTER COLUMN / MODIFY COLUMN syntax.
 *   2. String validation is enforced by SearchFilterRequest anyway.
 *
 * ENUM new values: 'full-time', 'part-time', 'freelance', 'internship' (Req 2.2).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement(
                "ALTER TABLE listings MODIFY job_type ENUM('full-time','part-time','freelance','internship') NOT NULL DEFAULT 'full-time'"
            );
        }
        // SQLite: no-op — string column with validation at application layer
    }

    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            DB::statement(
                "ALTER TABLE listings MODIFY job_type ENUM('full-time','part-time','freelance','internship','remote','hybrid') NOT NULL DEFAULT 'full-time'"
            );
        }
    }
};
