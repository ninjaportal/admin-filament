<?php

namespace NinjaPortal\Admin\Resources\Menu\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable()
                    ->label(__('Name'))
                    ->sortable(),
                TextColumn::make('items')
                    ->label(__('Items'))
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->items->count()),
            ])
            ->filters([
                
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
