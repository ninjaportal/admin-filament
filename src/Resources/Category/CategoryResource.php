<?php

namespace NinjaPortal\Admin\Resources\Category;

use Filament\Schemas\Schema;
use NinjaPortal\Admin\Resources\Category\Pages\ListCategories;
use NinjaPortal\Admin\Resources\Category\Pages\CreateCategory;
use NinjaPortal\Admin\Resources\Category\Pages\EditCategory;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use NinjaPortal\Admin\Concerns\HasNinjaService;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\Category\Schemas\CategoryForm;
use NinjaPortal\Admin\Resources\Category\Tables\CategoriesTable;
use NinjaPortal\Admin\Resources\Category\Pages;
use NinjaPortal\FilamentTranslations\Resources\Concerns\Translatable;
use NinjaPortal\Portal\Contracts\Services\CategoryServiceInterface;
use NinjaPortal\Portal\Models\Category;
use NinjaPortal\Portal\Services\CategoryService;
use NinjaPortal\Portal\Contracts\Services\ServiceInterface;

class CategoryResource extends Resource
{
    use HasNinjaService;
    use Translatable;


    protected static ?string $model = Category::class;

    protected static ?string $slug = 'categories';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['slug'];
    }

    public static function getLabel(): ?string
    {
        return __("Category");
    }

    public static function getPluralLabel(): ?string
    {
        return __("Categories");
    }

    public static function service(): ServiceInterface
    {
        return app(CategoryServiceInterface::class);
    }

    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups.".Constants::NAVIGATION_GROUPS['CONTENT']);
    }
}
