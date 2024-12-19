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
        Schema::create('extra_broadcast_logs', function (Blueprint $table) {
            $table->char('id', 26)->primary();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('recipients_list_id');
            $table->unsignedBigInteger('message_id');
            $table->text('message_body');
            $table->string('recipient_phone');
            $table->bigInteger('contact_id');
            $table->bigInteger('campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_broadcast_logs');
    }
};
