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
            $table->index(['is_sent', 'sent_at']);
            $table->index(['is_click', 'clicked_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_logs', function (Blueprint $table) {
            $table->dropIndex(['is_sent', 'sent_at']);
            $table->dropIndex(['is_click', 'clicked_at']);
            $table->dropIndex(['created_at']);
        });
    }
};
