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
        Schema::table('recipients_lists', function (Blueprint $table) {
            $table->integer('campaigns_count')->default(0);
            $table->integer('campaigns_processed_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipients_lists', function (Blueprint $table) {
            $table->dropColumn('campaigns_count');
            $table->dropColumn('campaigns_processed_count');
        });
    }
};
