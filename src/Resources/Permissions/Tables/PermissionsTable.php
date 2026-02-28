<?php

namespace NinjaPortal\Admin\Resources\Permissions\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table, string $resource): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('guard_name')
                    ->label(__('Guard')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn ($record) => $resource::deleteUsingService($record)),
            ]);
    }
}
