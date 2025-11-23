<?php

namespace App\Filament\Resources\PermissionsResource\Pages;

use App\Filament\Resources\PermissionsResource;
use Filament\Resources\Pages\EditRecord;

class EditPermissions extends EditRecord
{
    protected static string $resource = PermissionsResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['roles'] = $this->record->roles->pluck('id')->toArray();
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remover roles del array de datos
        unset($data['roles']);
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncRoles($this->data['roles'] ?? []);
    }
}
