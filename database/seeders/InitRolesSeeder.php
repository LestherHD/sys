<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class InitRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Crear rol administrador
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);

        // Crear usuario admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
            ]
        );

        // Asignar rol
        $admin->assignRole($adminRole);
    }
}
