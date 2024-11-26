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
        Schema::table('batch_file_campaign', function (Blueprint $table) {
            $table->index('campaign_id');
            $table->index('batch_file_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_file_campaign', function (Blueprint $table) {
            $table->dropIndex('campaign_id');
            $table->dropIndex('batch_file_id');
        });
    }
};
