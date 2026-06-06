<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm user_type = 'admin' support (hiện tại DB chỉ có employee/employer)
        // Đảm bảo cột user_type có thể chứa giá trị 'admin'
        // MariaDB: varchar nên không cần alter enum
        // Chỉ cần đảm bảo is_admin và user_type được đồng bộ

        // Thêm cột nếu chưa có
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->tinyInteger('is_admin')->default(0)->after('billing_ends');
            }
            if (!Schema::hasColumn('users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable()->after('is_banned');
            }
        });
    }

    public function down(): void
    {
        // Không rollback để tránh mất dữ liệu
    }
};
