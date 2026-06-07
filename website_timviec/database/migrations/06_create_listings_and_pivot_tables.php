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
        // 1. Bảng tin tuyển dụng
        if (!Schema::hasTable('listings')) {
            Schema::create('listings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Employer
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->text('roles')->nullable();
                $table->text('predes')->nullable();
                $table->unsignedBigInteger('salary')->nullable();
                $table->string('address')->nullable();
                $table->string('job_type')->nullable();
                $table->string('feature_image')->nullable();
                $table->timestamp('application_close_date')->nullable();
                $table->timestamps();
            });
        }

        // 2. Bảng trung gian ứng tuyển (listing_user)
        if (!Schema::hasTable('listing_user')) {
            Schema::create('listing_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Employee applicant
                $table->boolean('shortlisted')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_user');
        Schema::dropIfExists('listings');
    }
};
