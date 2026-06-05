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
        Schema::create('banned_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->boolean('is_active')->default(true);
            $table->string('severity', 20)->default('high');
            $table->timestamps();
            
            $table->index(['keyword', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banned_keywords');
    }
};
