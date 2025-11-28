<?php

namespace App\Filament\Resources\CategoriaMenuResource\Pages;

use App\Filament\Resources\CategoriaMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaMenus extends ListRecords
{
    protected static string $resource = CategoriaMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function deleteRecord($recordId): void
    {
        $record = \App\Models\CategoriaMenu::find($recordId);

        if ($record) {
            $record->delete();

            \Filament\Notifications\Notification::make()
                ->title('Categoría eliminada correctamente')
                ->success()
                ->send();

            $this->dispatch('$refresh');
        }
    }

    public function restoreRecord($recordId): void
    {
        $record = \App\Models\CategoriaMenu::withTrashed()->find($recordId);

        if ($record) {
            $record->restore();

            \Filament\Notifications\Notification::make()
                ->title('Categoría restaurada correctamente')
                ->success()
                ->send();

            $this->dispatch('$refresh');
        }
    }
}
