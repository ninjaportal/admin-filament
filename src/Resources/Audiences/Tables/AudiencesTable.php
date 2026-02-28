<?php

namespace NinjaPortal\Admin\Resources\Audiences\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AudiencesTable
{
    public static function configure(Table $table, string $resource): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('Users')),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label(__('Products')),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn ($record) => $resource::deleteUsingService($record)),
            ]);
    }
}
