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
        $this->record->syncRoles($this->data['roles'] ?? []);
        $this->record->syncPermissions($this->data['permissions'] ?? []);
    }
}
