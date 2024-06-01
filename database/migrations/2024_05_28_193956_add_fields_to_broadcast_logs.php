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
            $table->text('message_body');
            $table->string('recipient_phone');
            $table->integer('is_downloaded_as_csv')->default(0);
            $table->unsignedBigInteger('broadcast_batch_id');
            $table->foreign('broadcast_batch_id')->references('id')->on('broadcast_batches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_logs', function (Blueprint $table) {
            //
        });
    }
};
