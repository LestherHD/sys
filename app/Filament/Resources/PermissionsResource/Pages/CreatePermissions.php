<?php

namespace App\Filament\Resources\PermissionsResource\Pages;

use App\Filament\Resources\PermissionsResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePermissions extends CreateRecord
{
    protected static string $resource = PermissionsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remover roles del array de datos
        unset($data['roles']);
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles($this->data['roles'] ?? []);
    }
}
