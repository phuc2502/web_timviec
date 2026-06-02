<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add search-related columns to the listings table.
 *
 * New columns (Req 3, 7, 8, 14.4):
 *   - work_mode:             onsite / remote / hybrid
 *   - experience_years_min:  TINYINT UNSIGNED NULL
 *   - experience_years_max:  TINYINT UNSIGNED NULL
 *   - job_level:             intern / fresher / junior / middle / senior / lead / manager
 *
 * Note: Columns use string() instead of enum() for SQLite test compatibility.
 *       On MySQL the ENUM constraints are enforced at the application layer
 *       (SearchFilterRequest validation) and via the job_type migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // work_mode: 'onsite' | 'remote' | 'hybrid'
            $table->string('work_mode', 20)
                  ->default('onsite')
                  ->after('job_type');

            $table->tinyInteger('experience_years_min')
                  ->unsigned()
                  ->nullable()
                  ->after('work_mode');

            $table->tinyInteger('experience_years_max')
                  ->unsigned()
                  ->nullable()
                  ->after('experience_years_min');

            // job_level: 'intern' | 'fresher' | 'junior' | 'middle' | 'senior' | 'lead' | 'manager'
            $table->string('job_level', 20)
                  ->nullable()
                  ->after('experience_years_max');

            $table->index('work_mode',            'idx_listings_work_mode');
            $table->index('experience_years_min', 'idx_listings_exp_min');
            $table->index('experience_years_max', 'idx_listings_exp_max');
            $table->index('job_level',            'idx_listings_job_level');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('idx_listings_work_mode');
            $table->dropIndex('idx_listings_exp_min');
            $table->dropIndex('idx_listings_exp_max');
            $table->dropIndex('idx_listings_job_level');

            $table->dropColumn(['work_mode', 'experience_years_min', 'experience_years_max', 'job_level']);
        });
    }
};
