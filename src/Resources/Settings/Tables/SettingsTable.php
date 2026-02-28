<?php

namespace NinjaPortal\Admin\Resources\Settings\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use NinjaPortal\Admin\Resources\SettingGroups\SettingGroupResource;
use NinjaPortal\Admin\Support\Settings\SettingUi;
use NinjaPortal\Portal\Models\SettingGroup;
use NinjaPortal\Portal\Utils;

class SettingsTable
{
    public static function configure(Table $table, string $resource): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('Setting'))
                    ->searchable(['key', 'label'])
                    ->copyable()
                    ->weight('medium')
                    ->description(fn ($record): string => $record->label ?: __('No display label')),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->formatStateUsing(fn (?string $state): string => SettingUi::labelForType($state))
                    ->icon(fn (?string $state): string => SettingUi::iconForType($state))
                    ->color(fn (?string $state): string => SettingUi::colorForType($state))
                    ->badge(),
                TextColumn::make('value')
                    ->label(__('Current value'))
                    ->formatStateUsing(fn (?string $state, $record): string => SettingUi::preview($state, $record->type))
                    ->placeholder(__('Not set'))
                    ->wrap(),
                TextColumn::make('group.name')
                    ->label(__('Group'))
                    ->default(__('Ungrouped'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('updated_at')
                    ->label(__('Updated'))
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Value type'))
                    ->options(SettingUi::typeOptions()),
                SelectFilter::make('setting_group_id')
                    ->label(__('Setting group'))
                    ->options(fn (): array => (Utils::getSettingGroupModel() ?: SettingGroup::class)::query()->orderBy('name')->pluck('name', 'id')->all()),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                Action::make('manageGroup')
                    ->label(__('Open group'))
                    ->icon('heroicon-o-folder-open')
                    ->color('gray')
                    ->visible(fn ($record): bool => filled($record->setting_group_id))
                    ->url(fn ($record): string => SettingGroupResource::getUrl('edit', ['record' => $record->setting_group_id])),
                EditAction::make(),
                DeleteAction::make()->using(fn ($record) => $resource::deleteUsingService($record)),
            ]);
    }
}
