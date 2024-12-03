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
            $table->integer('generated_count')->default(0)->after('path');
            $table->integer('request_count')->default(0);
            $table->longText('campaign_ids')->nullable();
            $table->boolean('has_errors')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_files', function (Blueprint $table) {
            $table->dropColumn('generated_count');
            $table->dropColumn('request_count');
            $table->dropColumn('campaign_ids');
            $table->dropColumn('has_errors');
        });
    }
};
