<?php

namespace App\Filament\Resources\RolesResource\Pages;

use App\Filament\Resources\RolesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoles extends CreateRecord
{
    protected static string $resource = RolesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remover permissions del array de datos
        unset($data['permissions']);
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncPermissions($this->data['permissions'] ?? []);
    }
}
