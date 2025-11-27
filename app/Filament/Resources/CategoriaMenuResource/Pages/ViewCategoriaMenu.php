<?php

namespace App\Filament\Resources\CategoriaMenuResource\Pages;

use App\Filament\Resources\CategoriaMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategoriaMenu extends ViewRecord
{
    protected static string $resource = CategoriaMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

