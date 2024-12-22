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
        \DB::table('contacts')->whereNotNull('phone')
            ->update([
                'phone' => \DB::raw("CAST(REGEXP_REPLACE(phone, '[^0-9]', '') AS UNSIGNED)")
            ]);

        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('phone')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('phone')->change();
        });
    }
};
