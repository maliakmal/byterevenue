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
            $table->boolean('deleted_on_keitaro', false);

            $table->index(['created_at', 'deleted_on_keitaro']);
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
