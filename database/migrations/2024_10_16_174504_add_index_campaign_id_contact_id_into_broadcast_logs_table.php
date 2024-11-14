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
        // Redundant index needed for $contacts->withCount('campaigns')
        // in app/Http/Controllers/ContactController.php line 33
        // Requires creation of a Broadcast_logs->campaigns unique name|count for Contact pivot table.

        Schema::table('broadcast_logs', function (Blueprint $table) {
            $table->index(['campaign_id', 'contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_logs', function (Blueprint $table) {
            $table->dropIndex(['campaign_id', 'contact_id']);
        });
    }
};
