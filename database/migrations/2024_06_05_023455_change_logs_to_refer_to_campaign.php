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
        Schema::table('broadcast_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            //$table->dropForeign('broadcast_logs_broadcast_batch_id');

        });
        Schema::table('batch_files', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id');
            //$table->dropForeign('batch_files_broadcast_batch_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('campaign_id');
        });
    }
};
