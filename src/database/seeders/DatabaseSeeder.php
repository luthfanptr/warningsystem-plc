<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([ // ini acc default, jadi dia bakal dapet role super admin kalau udah login
        //     'name' => 'Super Admin',
        //     'email' => 'admin@admin.com',
        //     'password' => bcrypt('password'),
        // ]);

        // buat role admin
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']); // nama role

        // buat user dgn akun dan password ini 
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin', // nama akun
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole($superAdminRole);

    }
}
