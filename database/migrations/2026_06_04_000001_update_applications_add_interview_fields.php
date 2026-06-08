<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Thêm cột ngày giờ phỏng vấn (chỉ có khi status = interviewing)
            if (!Schema::hasColumn('applications', 'interview_scheduled_at')) {
                $table->timestamp('interview_scheduled_at')->nullable()->after('status_updated_at');
            }
        });

        // MySQL modify enum - add 'approved' status
        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            \DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
                'submitted','viewed','approved','interviewing','rejected'
            ) NOT NULL DEFAULT 'submitted'");
        }
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'interview_scheduled_at')) {
                $table->dropColumn('interview_scheduled_at');
            }
        });
        if (Schema::connection(null)->getConnection()->getDriverName() === 'mysql') {
            \DB::statement("ALTER TABLE applications MODIFY COLUMN status ENUM(
                'submitted','viewed','interviewing','accepted','rejected'
            ) NOT NULL DEFAULT 'submitted'");
        }
    }
};
