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
        Schema::create('batch_files', function (Blueprint $table) {
            $table->id();

            $table->string('filename');
            $table->string('path');
            $table->integer('number_of_entries')->default(0);
            $table->unsignedBigInteger('broadcast_batch_id');
            $table->foreign('broadcast_batch_id')->references('id')->on('broadcast_batches')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_files');
    }
};
