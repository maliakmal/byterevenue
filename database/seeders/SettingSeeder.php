<?php

namespace Database\Seeders;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Setting::insert([
             'name' =>'ips-sim-vms',
             'value' =>"[]",
             'label' =>"IPs of Sim Stations",
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now(),
        ]);
    }
}
