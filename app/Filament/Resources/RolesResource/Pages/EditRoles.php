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
        $this->record->syncPermissions($this->data['permissions'] ?? []);
    }
}
