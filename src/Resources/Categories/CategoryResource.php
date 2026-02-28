<?php

namespace NinjaPortal\Admin\Resources\Categories;

use Filament\Resources\Pages\PageRegistration;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use NinjaPortal\Admin\Resources\Categories\Pages\CreateCategory;
use NinjaPortal\Admin\Resources\Categories\Pages\EditCategory;
use NinjaPortal\Admin\Resources\Categories\Pages\ListCategories;
use NinjaPortal\Admin\Resources\Categories\Schemas\CategoryForm;
use NinjaPortal\Admin\Resources\Categories\Tables\CategoriesTable;
use NinjaPortal\Admin\Resources\Concerns\InteractsWithTranslatableData;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Portal\Contracts\Services\CategoryServiceInterface;
use NinjaPortal\Portal\Models\Category;
use NinjaPortal\Portal\Utils;

class CategoryResource extends PortalResource
{
    use InteractsWithTranslatableData;

    public static function getModel(): string
    {
        return Utils::getCategoryModel() ?: Category::class;
    }

    public static function getResourceKey(): string
    {
        return 'categories';
    }

    public static function service(): CategoryServiceInterface
    {
        return app(CategoryServiceInterface::class);
    }

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table, static::class);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => static::page('index', ListCategories::class)::route('/'),
            'create' => static::page('create', CreateCategory::class)::route('/create'),
            'edit' => static::page('edit', EditCategory::class)::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return (string) config('portal-admin.panel.navigation.catalog', __('portal-admin::portal-admin.navigation.catalog'));
    }

    public static function mutateFormDataBeforeFill(array $data, ?Model $record = null): array
    {
        return static::withTranslatableFormData($data, $record);
    }
}
