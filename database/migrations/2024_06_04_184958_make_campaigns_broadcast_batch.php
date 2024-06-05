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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('recipients_list_id');
            $table->unsignedBigInteger('message_id')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('total_recipients_sent_to')->default(0);
            $table->integer('total_recipients_click_thru')->default(0);
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['message_id']);
            $table->dropForeign(['recipients_list_id']);
            $table->dropColumn('recipients_list_id');
            $table->dropColumn('message_id');
            $table->dropColumn('total_recipients');
            $table->dropColumn('total_recipients_sent_to');
            $table->dropColumn('total_recipients_click_thru');
        });
    }
};
