<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the listing_skill pivot table (Req 4.1–4.6).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_skill', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id');
            $table->unsignedBigInteger('skill_id');

            $table->primary(['listing_id', 'skill_id']);

            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_skill');
    }
};
