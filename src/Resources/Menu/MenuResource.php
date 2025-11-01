<?php

namespace NinjaPortal\Admin\Resources\Menu;

use Filament\Schemas\Schema;
use NinjaPortal\Admin\Resources\Menu\Pages\ListMenus;
use NinjaPortal\Admin\Resources\Menu\Pages\CreateMenu;
use NinjaPortal\Admin\Resources\Menu\Pages\EditMenu;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\Menu\Schemas\MenuForm;
use NinjaPortal\Admin\Resources\Menu\Tables\MenusTable;
use NinjaPortal\Admin\Resources\Menu\Pages;
use NinjaPortal\Admin\Resources\Menu\RelationManagers\MenuItemsRelationManager;
use NinjaPortal\FilamentTranslations\Resources\Concerns\Translatable;
use NinjaPortal\Portal\Models\Menu;

class MenuResource extends Resource
{
    use Translatable;

    protected static ?string $model = Menu::class;

    protected static ?string $slug = 'menus';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bars-4';

    public static function form(Schema $schema): Schema
    {
        return MenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenusTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenus::route('/'),
            'create' => CreateMenu::route('/create'),
            'edit' => EditMenu::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            MenuItemsRelationManager::make()
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['slug'];
    }


    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups.".Constants::NAVIGATION_GROUPS['CONTENT']);
    }

}
