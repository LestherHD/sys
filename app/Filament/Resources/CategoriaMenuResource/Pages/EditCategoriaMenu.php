<?php

namespace App\Filament\Resources\CategoriaMenuResource\Pages;

use App\Filament\Resources\CategoriaMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaMenu extends EditRecord
{
    protected static string $resource = CategoriaMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver')
                ->url(fn () => CategoriaMenuResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
