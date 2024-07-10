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
        Schema::table('url_shorteners', function (Blueprint $table) {
            $table->bigInteger('asset_id')->after('endpoint');
            $table->text('response')->after('endpoint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('url_shorteners', function (Blueprint $table) {
            //
        });
    }
};
