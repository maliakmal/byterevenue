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
            $table->index(['is_sent', 'campaign_id'], 'is_sent_campaign_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_logs', function (Blueprint $table) {
            //
            $table->dropIndex('is_sent_campaign_id_index');

        });
    }
};
