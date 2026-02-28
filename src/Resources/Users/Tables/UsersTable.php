<?php

namespace NinjaPortal\Admin\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table, string $resource): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('Name'))
                    ->searchable(query: fn ($query, string $search) => $query
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('sync_with_apigee')
                    ->label(__('Apigee sync'))
                    ->badge()
                    ->formatStateUsing(fn (?bool $state): string => $state ? __('Synced') : __('Pending'))
                    ->color(fn (?bool $state): string => $state ? 'success' : 'warning'),
                TextColumn::make('audiences_count')
                    ->counts('audiences')
                    ->label(__('Audiences')),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime(),
            ])
            ->recordActions([
                Action::make('apps')
                    ->label(__('portal-admin::portal-admin.actions.manage_apps'))
                    ->icon('heroicon-o-key')
                    ->url(fn ($record) => $resource::getUrl('apps', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make()->using(fn ($record) => $resource::deleteUsingService($record)),
            ]);
    }
}
