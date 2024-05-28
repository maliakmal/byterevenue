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
        Schema::create('broadcast_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('recipients_list_id');
            $table->unsignedBigInteger('message_id');
            $table->integer('total_recipients')->default(0);
            $table->integer('total_recipients_sent_to')->default(0);
            $table->integer('total_recipients_click_thru')->default(0);
            $table->integer('status')->default(0); // 0 - draft | 1 - pending | 2 - sent
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('recipients_list_id')->references('id')->on('recipients_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_batches');
    }
};
