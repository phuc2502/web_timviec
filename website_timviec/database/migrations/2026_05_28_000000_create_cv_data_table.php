<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tạo bảng cv_data lưu trữ nội dung CV online của ứng viên.
     * UNIQUE KEY trên user_id: 1 user chỉ có 1 bản CV online (MVP).
     * Nếu cần multi-CV sau này → drop unique constraint, thêm cột is_primary.
     */
    public function up(): void
    {
        Schema::create('cv_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->unique('uq_cv_data_user')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('full_name', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('photo_path', 255)->nullable();

            $table->text('objective')->nullable();
            $table->json('education')->nullable();
            $table->json('experience')->nullable();
            $table->json('projects')->nullable();
            $table->json('certifications')->nullable();
            $table->text('skills_text')->nullable();
            $table->json('languages')->nullable();

            $table->string('template', 50)->default('default');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cv_data');
    }
};
