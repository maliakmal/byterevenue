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
            $table->string('campaign_alias')->nullable();
            $table->unsignedBigInteger('keitaro_campaign_id')->nullable();
            $table->text('keitaro_campaign_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_short_urls', function (Blueprint $table) {
            //
        });
    }
};
