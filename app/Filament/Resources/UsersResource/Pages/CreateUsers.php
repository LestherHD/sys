<?php

namespace App\Filament\Resources\UsersResource\Pages;

use App\Filament\Resources\UsersResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUsers extends CreateRecord
{
    protected static string $resource = UsersResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remover roles y permissions del array de datos
        unset($data['roles'], $data['permissions']);
        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles($this->data['roles'] ?? []);
        $this->record->syncPermissions($this->data['permissions'] ?? []);
    }
}
