<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create campaign',
            'edit campaign',
            'delete campaign',
            'view campaign',
            'process campaign',
            'process queue',
            'create short url',
            'edit short url',
            'delete short url',
            'view short url',
            'process tokens',
            'create user',
            'edit user',
            'delete user',
            'view user',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }


                // Create admin role and assign all permissions
                $adminRole = Role::firstOrCreate(['name' => 'admin']);
                $adminRole->givePermissionTo($permissions);
        
                // Create user role with limited permissions
                $userRole = Role::firstOrCreate(['name' => 'user']);
                $user_permissions = [
                    'create campaign',
                    'edit campaign',
                    'delete campaign',
                    'view campaign',
                    'process campaign'
                ];

                $userRole->givePermissionTo($user_permissions);
        
                // Assign admin role to a user (adjust user ID accordingly)
                $adminUser = \App\Models\User::find(1);
                if ($adminUser) {
                    $adminUser->assignRole('admin');
                }
        



    }
}
