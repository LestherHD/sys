<?php

namespace App\Filament\Resources\UsersResource\Pages;

use App\Filament\Resources\UsersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUsers extends EditRecord
{
    protected static string $resource = UsersResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles->pluck('id')->toArray();
        $data['permissions'] = $this->record->permissions->pluck('id')->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remover roles y permissions del array de datos para evitar errores de columnas
        unset($data['roles'], $data['permissions']);
        return $data;
    }

    protected function afterSave(): void
    {
        // Obtener roles
        $roles = $this->data['roles'] ?? [];
        if (is_string($roles)) {
            $roles = json_decode($roles, true) ?? [];
        }
        $roles = array_map('intval', $roles);

        // Obtener permissions
        $permissions = $this->data['permissions'] ?? [];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }
        $permissions = array_map('intval', $permissions);

        // Sincronizar
        $this->record->syncRoles($roles);
        $this->record->syncPermissions($permissions);
    }

}
