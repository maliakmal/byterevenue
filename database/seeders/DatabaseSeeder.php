<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        Role::create(['name' => 'admin']);

        $admin->assignRole('admin');

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
        ]);

        $this->call(SettingSeeder::class);
    }
}
