<?php

namespace NinjaPortal\Admin\Resources\SettingGroup;

use Filament\Schemas\Schema;
use NinjaPortal\Admin\Resources\SettingGroup\Pages\ListSettingGroups;
use NinjaPortal\Admin\Resources\SettingGroup\Pages\CreateSettingGroup;
use NinjaPortal\Admin\Resources\SettingGroup\Pages\EditSettingGroup;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\SettingGroup\Schemas\SettingGroupForm;
use NinjaPortal\Admin\Resources\SettingGroup\Tables\SettingGroupsTable;
use NinjaPortal\Portal\Models\SettingGroup;
use NinjaPortal\Admin\Resources\SettingGroup\Pages;
use NinjaPortal\Admin\Resources\SettingGroup\RelationManagers\SettingsRelationManager;

class SettingGroupResource extends Resource
{
    protected static ?string $model = SettingGroup::class;

    protected static ?string $slug = 'settings';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog';

    public static function form(Schema $schema): Schema
    {
        return SettingGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SettingGroupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSettingGroups::route('/'),
            'create' => CreateSettingGroup::route('/create'),
            'edit' => EditSettingGroup::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Settings');
    }


    public static function getRelations(): array
    {
        return [
            SettingsRelationManager::make()
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups.".Constants::NAVIGATION_GROUPS['ADMIN']);
    }

}
