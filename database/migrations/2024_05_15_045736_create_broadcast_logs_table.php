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
        Schema::create('broadcast_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('recipients_list_id');
            $table->unsignedBigInteger('message_id');
            $table->datetime('sent_at')->nullable();
            $table->datetime('clicked_at')->nullable();
            $table->integer('total_recipients_click_thru')->default(0);
            $table->integer('status')->default(0); // 0 - unsent | 1 - send | 2 - clicked
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_logs');
    }
};
