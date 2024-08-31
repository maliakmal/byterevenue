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
        Schema::create('area_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index();
            $table->string('city_name');
            $table->string('province');
            $table->string('country_code', 3);
            $table->decimal('lat', 10, 8);
            $table->string('lng', 11,8);
            $table->timestamps();
        });
        $file_address = resource_path('data/us-area-code-cities.csv');
        $file = fopen($file_address, 'r');

        $insert_data = [];
        while(!feof($file))
        {
            $row = fgetcsv($file);
            if(empty($row)) continue;
            $insert_data[] = [
                'code'         => $row[0],
                'city_name'    => $row[1],
                'province'     => $row[2],
                'country_code' => $row[3],
                'lat'          => $row[4],
                'lng'          => $row[5],
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
            ];
        }
        fclose($file);
        \App\Models\AreaCode::insert($insert_data);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_codes');
    }
};
