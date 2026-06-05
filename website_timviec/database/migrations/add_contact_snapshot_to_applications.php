<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Snapshot thông tin liên hệ tại thời điểm nộp đơn (SSOT cho cả 2 phía)
            $table->string('applicant_name',  100)->nullable()->after('cover_letter');
            $table->string('applicant_phone', 20)->nullable()->after('applicant_name');
            $table->string('applicant_email', 255)->nullable()->after('applicant_phone');

            // Lịch phỏng vấn (dùng khi status = interviewing)
            if (!Schema::hasColumn('applications', 'interview_scheduled_at')) {
                $table->timestamp('interview_scheduled_at')->nullable()->after('applicant_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['applicant_name', 'applicant_phone', 'applicant_email']);
        });
    }
};
