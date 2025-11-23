<?php

namespace App\Filament\Resources\RolesResource\Pages;

use App\Filament\Resources\RolesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoles extends EditRecord
{
    protected static string $resource = RolesResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['permissions'] = $this->record->permissions->pluck('id')->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remover permissions del array de datos
        unset($data['permissions']);
        return $data;
    }

    protected function afterSave(): void
    {
        $permissions = $this->data['permissions'] ?? [];

        // Si viene como JSON → convertir a array
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        // Convertir valores a enteros
        $permissions = array_map('intval', $permissions);

        $this->record->syncPermissions($permissions);
    }

}
