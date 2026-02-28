<?php

namespace NinjaPortal\Admin\Resources\ApiProducts\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApiProductsTable
{
    public static function configure(Table $table, string $resource): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable(),
                TextColumn::make('visibility')
                    ->label(__('Visibility'))
                    ->badge(),
                TextColumn::make('apigee_product_id')
                    ->label(__('Apigee product')),
                TextColumn::make('categories_count')
                    ->counts('categories')
                    ->label(__('Categories')),
                TextColumn::make('audiences_count')
                    ->counts('audiences')
                    ->label(__('Audiences')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->using(fn ($record) => $resource::deleteUsingService($record)),
            ]);
    }
}
