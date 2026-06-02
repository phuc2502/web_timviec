<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the listings table with all columns needed for the Job Search Filter module.
 *
 * Note: ENUM columns are represented as string() for SQLite test compatibility.
 * On MySQL production, the search migration will enforce ENUM constraints.
 *
 * Indexes created here:
 *   - idx_listings_status       (Req 14.4)
 *   - idx_listings_job_type     (Req 14.4)
 *   - idx_listings_salary       (Req 14.4)
 *   - idx_listings_close_date   (Req 14.4)
 *   - ft_listings_title_desc    (Req 14.1) — skipped on SQLite, created on MySQL only
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('predes')->nullable();
            $table->longText('description')->nullable();
            $table->json('requirements')->nullable();
            $table->json('benefits')->nullable();

            // job_type: 'full-time', 'part-time', 'freelance', 'internship'
            // stored as string for SQLite compatibility; MySQL migration enforces ENUM
            $table->string('job_type', 30)->default('full-time');

            // work_mode and other new search columns added by the search migration
            // (2025_01_01_000001_add_search_columns_to_listings_table)

            $table->string('address')->nullable();
            $table->unsignedInteger('salary')->default(0); // 0 = negotiable ("thỏa thuận")

            $table->string('feature_image')->nullable();
            $table->date('application_close_date');

            // status: 'open', 'hidden', 'closed'
            $table->string('status', 20)->default('open');

            $table->timestamps();

            // Indexes required by Req 14.4
            $table->index('status',                   'idx_listings_status');
            $table->index('job_type',                 'idx_listings_job_type');
            $table->index('salary',                   'idx_listings_salary');
            $table->index('application_close_date',   'idx_listings_close_date');
        });

        // FULLTEXT index on MySQL only (SQLite does not support FULLTEXT)
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement(
                'ALTER TABLE listings ADD FULLTEXT ft_listings_title_desc (title, predes)'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
