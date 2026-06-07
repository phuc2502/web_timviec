<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Bổ sung các cột profile/CV vào bảng users để đồng bộ với database_schema.sql.
     * Không dùng ->after() để tương thích SQLite.
     * Tất cả cột nullable hoặc có default value.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type', 20)->default('employee');
            $table->text('about')->nullable();
            $table->string('profile_pic', 255)->nullable();
            $table->string('resume', 255)->nullable();

            // Employee extended
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->unsignedInteger('desired_salary')->nullable();
            $table->string('location', 255)->nullable();

            // Employer extended
            $table->string('company_name', 255)->nullable();
            $table->string('company_logo', 255)->nullable();
            $table->string('company_website', 255)->nullable();
            $table->string('company_size', 20)->nullable();

            // Subscription
            $table->timestamp('user_trial')->nullable();
            $table->string('plan', 20)->nullable();
            $table->date('billing_ends')->nullable();

            // Admin / Banned
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_banned')->default(false);
            $table->timestamp('banned_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_type', 'about', 'profile_pic', 'resume',
                'experience_years', 'desired_salary', 'location',
                'company_name', 'company_logo', 'company_website', 'company_size',
                'user_trial', 'plan', 'billing_ends',
                'is_admin', 'is_banned', 'banned_at',
            ]);
        });
    }
};
