<?php

namespace NinjaPortal\Admin\Resources\Admin;

use Filament\Schemas\Schema;
use NinjaPortal\Admin\Resources\Admin\Pages\ListAdmins;
use NinjaPortal\Admin\Resources\Admin\Pages\CreateAdmin;
use NinjaPortal\Admin\Resources\Admin\Pages\EditAdmin;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\Admin\Schemas\AdminForm;
use NinjaPortal\Admin\Resources\Admin\Tables\AdminsTable;
use NinjaPortal\Portal\Models\Admin;
use NinjaPortal\Admin\Resources\Admin\Pages;

class AdminResource extends Resource
{
    protected static ?string $model = Admin::class;

    protected static ?string $slug = 'admins';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    public static function form(Schema $schema): Schema
    {
        return AdminForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdmins::route('/'),
            'create' => CreateAdmin::route('/create'),
            'edit' => EditAdmin::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function getLabel(): ?string
    {
        return __('ninjaadmin::ninjaadmin.admins');
    }

    public static function getPluralLabel(): ?string
    {
        return __('ninjaadmin::ninjaadmin.admins');
    }

    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups.".Constants::NAVIGATION_GROUPS['ADMIN']);
    }

}
