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
        Schema::table('campaign_short_urls', function (Blueprint $table) {
            $table->index('url_shortener_id');
        });

        Schema::table('url_shorteners', function (Blueprint $table) {
            $table->index('is_registered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_short_urls', function (Blueprint $table) {
            $table->dropIndex('url_shortener_id');
        });

        Schema::table('url_shorteners', function (Blueprint $table) {
            $table->dropIndex('is_registered');
        });
    }
};
