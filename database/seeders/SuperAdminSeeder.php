<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Create Super Admin role if not exists
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // Create a new Super Admin user
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('12345678'), // Change to a secure password
        ]);

        // Assign role to the user
        $user->assignRole($superAdminRole);

        echo "Super Admin created successfully! \n";
    }
}
