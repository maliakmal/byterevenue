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
        \DB::table('broadcast_logs')->truncate();

        Schema::table('broadcast_logs', function (Blueprint $table) {
            $table->char('slug', 8)->after('id');
        });

        Schema::table('broadcast_logs', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_logs', function (Blueprint $table) {
            $table->dropUnique('broadcast_logs_slug_unique');
        });

        Schema::table('broadcast_logs', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
