<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories');
            
            // Core fields
            $table->string('title', 255);
            $table->string('slug')->unique();
            $table->text('predes')->nullable();
            $table->text('description');
            $table->json('requirements')->nullable();
            $table->json('benefits')->nullable();
            $table->string('address');
            if (DB::getDriverName() === 'sqlite') {
                $table->string('job_type', 30)->default('full-time');
                $table->string('level', 30)->nullable();
            } else {
                $table->enum('job_type', ['full_time', 'part_time', 'contract', 'internship', 'freelance']);
                $table->enum('level', ['intern', 'junior', 'middle', 'senior', 'manager', 'director'])->nullable();
            }
            
            // Salary fields
            $table->unsignedBigInteger('salary')->default(0);
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->boolean('is_negotiable')->default(false);
            $table->boolean('hide_salary')->default(false);
            
            // Application fields
            $table->date('application_close_date');
            $table->date('start_date')->nullable();
            $table->integer('vacancy_count')->default(1);
            
            // Contact fields
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();
            
            // File upload
            $table->string('jd_file_path')->nullable();
            $table->string('feature_image')->nullable();
            
            // Publishing mode & state
            if (DB::getDriverName() === 'sqlite') {
                $table->string('publish_mode', 30)->default('draft');
                $table->timestamp('scheduled_at')->nullable();
                $table->string('status', 30)->default('draft');
            } else {
                $table->enum('publish_mode', ['immediate', 'scheduled', 'draft'])->default('draft');
                $table->timestamp('scheduled_at')->nullable();
                $table->enum('status', [
                    'draft', 'pending_review', 'scheduled', 'active', 
                    'paused', 'closed', 'rejected', 'expired', 'archived'
                ])->default('draft');
            }
            
            // Moderation fields
            $table->text('rejection_reason')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('archived_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'application_close_date']);
            $table->index(['scheduled_at']);
            
            if (DB::getDriverName() !== 'sqlite') {
                $table->fullText(['title', 'description'], 'listing_search_fulltext');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
