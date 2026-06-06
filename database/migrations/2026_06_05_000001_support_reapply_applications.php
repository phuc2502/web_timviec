<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Hỗ trợ ứng tuyển lại (tối đa 3 lần/job):
 *  - Bỏ unique constraint (user_id, listing_id) để cho phép nhiều bản ghi
 *  - Thêm apply_round: số thứ tự lần ứng tuyển (1, 2, 3)
 *  - Thêm parent_application_id: trỏ về đơn đầu tiên
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Bước 1: Tìm và drop các foreign key đang dùng index unique ──
        // MySQL không cho drop unique index nếu có FK tham chiếu vào nó.
        // Cần drop FK trước, drop index, rồi tạo lại FK theo index mới (PK).
        $fksToDrop = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'applications'
              AND REFERENCED_TABLE_NAME IS NOT NULL
              AND COLUMN_NAME IN ('user_id', 'listing_id')
        ");

        foreach ($fksToDrop as $fk) {
            DB::statement("ALTER TABLE `applications` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // ── Bước 2: Drop unique constraint ───────────────────────────────
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'listing_id']);
        });

        // ── Bước 3: Thêm cột mới ─────────────────────────────────────────
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('apply_round')->default(1)->after('status');
            $table->foreignId('parent_application_id')
                  ->nullable()
                  ->after('apply_round')
                  ->constrained('applications')
                  ->nullOnDelete();
        });

        // ── Bước 4: Tạo lại foreign keys user_id & listing_id ───────────
        // (chúng đã bị drop ở bước 1, tạo lại dựa trên PK)
        Schema::table('applications', function (Blueprint $table) {
            // Chỉ tạo lại nếu chưa tồn tại (tránh duplicate)
            $existing = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'applications'
                  AND COLUMN_NAME = 'user_id'
                  AND REFERENCED_TABLE_NAME = 'users'
            ");
            if (empty($existing)) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            $existing = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'applications'
                  AND COLUMN_NAME = 'listing_id'
                  AND REFERENCED_TABLE_NAME = 'listings'
            ");
            if (empty($existing)) {
                $table->foreign('listing_id')->references('id')->on('listings')->cascadeOnDelete();
            }
        });

        // ── Bước 5: Index tìm kiếm nhanh ─────────────────────────────────
        Schema::table('applications', function (Blueprint $table) {
            $table->index(['user_id', 'listing_id', 'apply_round'], 'idx_user_listing_round');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['parent_application_id']);
            $table->dropIndex('idx_user_listing_round');
            $table->dropColumn(['apply_round', 'parent_application_id']);
            $table->unique(['user_id', 'listing_id']);
        });
    }
};
