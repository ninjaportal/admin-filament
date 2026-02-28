<?php

namespace NinjaPortal\Admin\Resources\SettingGroups;

use Filament\Resources\Pages\PageRegistration;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Admin\Resources\SettingGroups\Pages\CreateSettingGroup;
use NinjaPortal\Admin\Resources\SettingGroups\Pages\EditSettingGroup;
use NinjaPortal\Admin\Resources\SettingGroups\Pages\ListSettingGroups;
use NinjaPortal\Admin\Resources\SettingGroups\Schemas\SettingGroupForm;
use NinjaPortal\Admin\Resources\SettingGroups\Tables\SettingGroupsTable;
use NinjaPortal\Portal\Contracts\Services\SettingGroupServiceInterface;
use NinjaPortal\Portal\Models\SettingGroup;
use NinjaPortal\Portal\Utils;

class SettingGroupResource extends PortalResource
{
    public static function getModel(): string
    {
        return Utils::getSettingGroupModel() ?: SettingGroup::class;
    }

    public static function getResourceKey(): string
    {
        return 'setting_groups';
    }

    public static function service(): SettingGroupServiceInterface
    {
        return app(SettingGroupServiceInterface::class);
    }

    public static function form(Schema $schema): Schema
    {
        return SettingGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SettingGroupsTable::configure($table, static::class);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => static::page('index', ListSettingGroups::class)::route('/'),
            'create' => static::page('create', CreateSettingGroup::class)::route('/create'),
            'edit' => static::page('edit', EditSettingGroup::class)::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return (string) config('portal-admin.panel.navigation.system', __('portal-admin::portal-admin.navigation.system'));
    }
}
