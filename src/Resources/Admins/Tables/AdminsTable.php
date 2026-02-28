<?php

namespace NinjaPortal\Admin\Resources\Admins\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminsTable
{
    public static function configure(Table $table, string $resource): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->hidden(fn ($record) => (int) (auth()->id() ?? 0) === (int) $record->getKey())
                    ->using(fn ($record) => $resource::deleteUsingService($record)),
            ]);
    }
}
