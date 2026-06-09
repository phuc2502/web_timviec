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
            // Map work_mode for remote/hybrid and set job_type to a valid default enum
            DB::table('listings')->whereIn(DB::raw('LOWER(job_type)'), ['remote', 'hybrid'])->get()->each(function ($listing) {
                $mode = strtolower($listing->job_type);
                DB::table('listings')->where('id', $listing->id)->update([
                    'work_mode' => $mode,
                    'job_type' => 'full-time'
                ]);
            });

            // Normalize other values to lowercase to match the new enum values
            DB::table('listings')->where('job_type', 'Full-time')->update(['job_type' => 'full-time']);
            DB::table('listings')->where('job_type', 'Part-time')->update(['job_type' => 'part-time']);
            DB::table('listings')->where('job_type', 'Freelance')->update(['job_type' => 'freelance']);
            DB::table('listings')->where('job_type', 'Internship')->update(['job_type' => 'internship']);

            // Safely set any remaining unrecognized value to 'full-time' to avoid mysql truncation error
            $validEnums = ['full-time', 'part-time', 'freelance', 'internship'];
            DB::table('listings')->whereNotIn('job_type', $validEnums)->orWhereNull('job_type')->update(['job_type' => 'full-time']);

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
