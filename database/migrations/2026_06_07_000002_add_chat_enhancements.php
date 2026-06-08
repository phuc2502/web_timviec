<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Thêm cột đính kèm và đánh dấu thông báo email vào bảng messages
        Schema::table('messages', function (Blueprint $table) {
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->boolean('email_notified')->default(false);
        });

        // 2. Tạo bảng quản lý thư mời phỏng vấn
        Schema::create('interview_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('location');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, declined
            $table->timestamps();
        });

        // 3. Tạo bảng quản lý tin nhắn mẫu nhanh
        Schema::create('quick_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });

        // 4. Thêm cột last_seen_at vào bảng users
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_seen_at');
        });

        Schema::dropIfExists('quick_replies');
        Schema::dropIfExists('interview_invitations');

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name', 'email_notified']);
        });
    }
};
