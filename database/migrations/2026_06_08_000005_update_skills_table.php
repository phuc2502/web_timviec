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
        Schema::table('skills', function (Blueprint $table) {
            if (!Schema::hasColumn('skills', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }
            if (!Schema::hasColumn('skills', 'usage_count')) {
                $table->integer('usage_count')->default(0);
            }
        });
        
        // Index for autocomplete search
        Schema::table('skills', function (Blueprint $table) {
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            if (Schema::hasColumn('skills', 'slug')) {
                // For SQLite: dropUnique might fail or need to be guarded
                try {
                    $table->dropUnique(['slug']);
                } catch (\Exception $e) {}
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('skills', 'usage_count')) {
                $table->dropColumn('usage_count');
            }
            try {
                $table->dropIndex(['name']);
            } catch (\Exception $e) {}
        });
    }
};
