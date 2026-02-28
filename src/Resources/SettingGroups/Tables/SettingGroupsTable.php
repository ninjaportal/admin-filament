<?php

namespace NinjaPortal\Admin\Resources\SettingGroups\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use NinjaPortal\Admin\Resources\Settings\SettingResource;
use NinjaPortal\Admin\Support\Settings\SettingUi;

class SettingGroupsTable
{
    public static function configure(Table $table, string $resource): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Group'))
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('settings_count')
                    ->counts('settings')
                    ->label(__('Settings'))
                    ->badge()
                    ->color('primary'),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime(),
            ])
            ->defaultSort('name')
            ->recordActions([
                Action::make('viewSettings')
                    ->label(__('View settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn ($record): string => SettingResource::getUrl('index', [
                        'tab' => SettingUi::groupTabKey($record->getKey()),
                    ])),
                Action::make('createSetting')
                    ->label(__('Add setting'))
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->url(fn ($record): string => SettingResource::getUrl('create', [
                        'setting_group_id' => $record->getKey(),
                    ])),
                EditAction::make(),
                DeleteAction::make()->using(fn ($record) => $resource::deleteUsingService($record)),
            ]);
    }
}
