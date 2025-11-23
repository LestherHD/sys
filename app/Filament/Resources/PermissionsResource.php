<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionsResource\Pages;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use App\Forms\Components\DualListBox;
use Filament\Tables;
use Filament\Tables\Table;

class PermissionsResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\TextInput::make('name')
                ->required(),

            Forms\Components\Hidden::make('guard_name')
                ->default('web')
                ->dehydrated(true),

            DualListBox::make('roles')
                ->label('Roles que tienen este permiso')
                ->options(Role::pluck('name', 'id')->toArray()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name'),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermissions::route('/create'),
            'edit' => Pages\EditPermissions::route('/{record}/edit'),
        ];
    }
}
