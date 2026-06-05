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
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type', 20)->default('employee');
            }
            if (!Schema::hasColumn('users', 'about')) {
                $table->text('about')->nullable();
            }
            if (!Schema::hasColumn('users', 'profile_pic')) {
                $table->string('profile_pic', 255)->nullable();
            }
            if (!Schema::hasColumn('users', 'resume')) {
                $table->string('resume', 255)->nullable();
            }

            // Employee extended
            if (!Schema::hasColumn('users', 'experience_years')) {
                $table->unsignedTinyInteger('experience_years')->nullable();
            }
            if (!Schema::hasColumn('users', 'desired_salary')) {
                $table->unsignedInteger('desired_salary')->nullable();
            }
            if (!Schema::hasColumn('users', 'location')) {
                $table->string('location', 255)->nullable();
            }

            // Employer extended
            if (!Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name', 255)->nullable();
            }
            if (!Schema::hasColumn('users', 'company_logo')) {
                $table->string('company_logo', 255)->nullable();
            }
            if (!Schema::hasColumn('users', 'company_website')) {
                $table->string('company_website', 255)->nullable();
            }
            if (!Schema::hasColumn('users', 'company_size')) {
                $table->string('company_size', 20)->nullable();
            }

            // Subscription
            if (!Schema::hasColumn('users', 'user_trial')) {
                $table->timestamp('user_trial')->nullable();
            }
            if (!Schema::hasColumn('users', 'plan')) {
                $table->string('plan', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'billing_ends')) {
                $table->date('billing_ends')->nullable();
            }

            // Admin / Banned
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false);
            }
            if (!Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false);
            }
            if (!Schema::hasColumn('users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = [];
            foreach ([
                'user_type', 'about', 'profile_pic', 'resume',
                'experience_years', 'desired_salary', 'location',
                'company_name', 'company_logo', 'company_website', 'company_size',
                'user_trial', 'plan', 'billing_ends',
                'is_admin', 'is_banned', 'banned_at',
            ] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $cols[] = $col;
                }
            }
            if (count($cols) > 0) {
                $table->dropColumn($cols);
            }
        });
    }
};
