<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaMenuResource\Pages;
use App\Models\CategoriaMenu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriaMenuResource extends Resource
{
    protected static ?string $model = CategoriaMenu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Toggle::make('visible_publico')
                    ->label('Visible Publico')
                    ->default(true),
                Forms\Components\Toggle::make('activo')
                    ->label('Activo')
                    ->default(true),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable()->weight('bold'),
                Tables\Columns\IconColumn::make('visible_publico')->boolean(),
                Tables\Columns\IconColumn::make('activo')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('delete')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation(false)
                    ->action(function (CategoriaMenu $record, Tables\Actions\Action $action) {
                        $livewire = $action->getLivewire();
                        $livewire->deleteRecord($record->id);
                    })
                    ->hidden(fn (CategoriaMenu $record): bool => $record->trashed()),
                Tables\Actions\Action::make('restore')
                    ->label('Restaurar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation(false)
                    ->action(function (CategoriaMenu $record, Tables\Actions\Action $action) {
                        $livewire = $action->getLivewire();
                        $livewire->restoreRecord($record->id);
                    })
                    ->visible(fn (CategoriaMenu $record): bool => $record->trashed()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaMenus::route('/'),
            'create' => Pages\CreateCategoriaMenu::route('/create'),
            'view' => Pages\ViewCategoriaMenu::route('/{record}'),
            'edit' => Pages\EditCategoriaMenu::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

