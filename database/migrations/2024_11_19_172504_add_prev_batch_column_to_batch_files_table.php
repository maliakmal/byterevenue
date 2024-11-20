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
        Schema::table('batch_files', function (Blueprint $table) {
            $table->unsignedBigInteger('prev_batch_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_files', function (Blueprint $table) {
            $table->dropColumn('prev_batch_id');
        });
    }
};
