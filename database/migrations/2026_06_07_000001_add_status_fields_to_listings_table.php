<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'status')) {
                $table->enum('status', ['open', 'hidden', 'closed'])->default('open')->after('job_type');
            }
            if (!Schema::hasColumn('listings', 'requirements')) {
                $table->text('requirements')->nullable()->after('description');
            }
            if (!Schema::hasColumn('listings', 'benefits')) {
                $table->text('benefits')->nullable()->after('requirements');
            }
        });

        // Đặt tất cả listing hiện tại là 'open'
        DB::table('listings')->whereNull('status')->update(['status' => 'open']);
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['status', 'requirements', 'benefits']);
        });
    }
};
