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
        Schema::create('update_sent_messages', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->integer('status')->default(0);
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_sent_messages');
    }
};
