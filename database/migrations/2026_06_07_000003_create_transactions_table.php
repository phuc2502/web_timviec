<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng transactions (dùng để phân biệt với payments table đã có)
     * AdminController dùng Transaction model -> bảng transactions
     * Bảng payments dùng cho Payment model (token purchase, subscription)
     */
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('vnp_txn_ref')->nullable()->unique();
                $table->string('vnp_transaction_no')->nullable();
                $table->bigInteger('amount')->default(0);
                $table->string('plan')->nullable(); // monthly, yearly
                $table->string('status')->default('pending'); // pending, paid, failed, refunded
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
