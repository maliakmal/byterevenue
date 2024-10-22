<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Determine if we can connect to the storage database.
     *
     * @return bool
     */
    protected function canConnectToStorageDatabase()
    {
        try {
            \DB::connection('storage_mysql')->getPdo();
        } catch (\Exception $e) {
            Artisan::call('database:manage', ['name' => 'storage_database', '--create' => true]);
        }

        return true;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!$this->canConnectToStorageDatabase()) {
            $this->down();

            return;
        }

        Schema::connection('storage_mysql')->create('broadcast_storage_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->unsignedBigInteger('phone');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('campaign_id');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('click_at')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::connection('storage_mysql')->dropIfExists('broadcast_storage_master');
        } catch (\Exception $e) {
            // do nothing
        }
    }
};
