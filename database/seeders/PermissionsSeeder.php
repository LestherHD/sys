<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Deshabilitar llaves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpiar tablas sin TRUNCATE
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();

        // Reiniciar IDs
        DB::statement('ALTER TABLE role_has_permissions AUTO_INCREMENT = 1;');
        DB::statement('ALTER TABLE permissions AUTO_INCREMENT = 1;');
        DB::statement('ALTER TABLE roles AUTO_INCREMENT = 1;');

        // Rehabilitar llaves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Lista de permisos base
        $permissions = [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'permissions.view', 'permissions.create', 'permissions.update', 'permissions.delete',
        ];

        // Crear permisos
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Crear rol admin
        $admin = Role::firstOrCreate([
            'name'       => 'Administrador',
            'guard_name' => 'web',
        ]);

        // Asignar todos los permisos
        $admin->givePermissionTo(Permission::all());

        $this->command->info("✔️ Seeder de permisos ejecutado correctamente.");
    }
}
