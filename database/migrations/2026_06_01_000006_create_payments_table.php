<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['token', 'subscription']);
            $table->unsignedBigInteger('amount');          // VNĐ
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('vnpay_txn_ref')->unique()->nullable();
            $table->json('vnpay_response')->nullable();     // lưu toàn bộ callback
            $table->unsignedInteger('token_amount')->nullable(); // số lượt mua (nếu type=token)
            $table->string('plan')->nullable();             // gói (nếu type=subscription)
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('payments'); }
};
