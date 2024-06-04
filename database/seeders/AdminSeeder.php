<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $user = User::firstOrCreate(
            ['email' => 'admin@email.com'],
            ['password' => bcrypt('admin123'), 'name'=>"Administrator"] // Change 'your-password' to the desired password
        );

        // Check if the role already exists, if not create it
        $role = Role::firstOrCreate(['name' => 'admin']);

        // Assign the role to the user
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
