<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm các cột tìm kiếm nâng cao vào bảng listings:
 * - work_mode:            onsite / remote / hybrid
 * - experience_years_min: số năm KN tối thiểu
 * - experience_years_max: số năm KN tối đa
 * - job_level:            intern / fresher / junior / middle / senior / lead / manager
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('work_mode', 20)->default('onsite')->after('job_type');
            $table->tinyInteger('experience_years_min')->unsigned()->nullable()->after('work_mode');
            $table->tinyInteger('experience_years_max')->unsigned()->nullable()->after('experience_years_min');
            $table->string('job_level', 20)->nullable()->after('experience_years_max');

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