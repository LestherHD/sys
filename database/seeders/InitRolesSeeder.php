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
        // Crear rol administrador con guard_name
        $adminRole = Role::firstOrCreate(
            ['name' => 'Administrador'],
            ['guard_name' => 'web']
        );

        // Crear usuario admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
            ]
        );

        // Sincronizar roles (elimina roles previos y asigna el nuevo)
        $admin->syncRoles([$adminRole]);

        $this->command->info("✔️ Usuario administrador creado con email: admin@admin.com y contraseña: admin123");
    }
}
