<?php

namespace NinjaPortal\Admin\Resources\ApiProduct;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use NinjaPortal\Admin\Concerns\HasNinjaService;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\ApiProduct\Pages;
use NinjaPortal\Admin\Resources\ApiProduct\Pages\CreateApiProduct;
use NinjaPortal\Admin\Resources\ApiProduct\Pages\EditApiProduct;
use NinjaPortal\Admin\Resources\ApiProduct\Pages\ListApiProducts;
use NinjaPortal\Admin\Resources\ApiProduct\Schemas\ApiProductForm;
use NinjaPortal\Admin\Resources\ApiProduct\Tables\ApiProductsTable;
use NinjaPortal\FilamentTranslations\Resources\Concerns\Translatable;
use NinjaPortal\Portal\Contracts\Services\ServiceInterface;
use NinjaPortal\Portal\Models\ApiProduct;
use NinjaPortal\Portal\Services\ApiProductService;

class ApiProductResource extends Resource
{
    use Translatable;
    use HasNinjaService;

    protected static ?string $model = ApiProduct::class;

    protected static ?string $slug = 'api-products';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return ApiProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApiProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiProducts::route('/'),
            'create' => CreateApiProduct::route('/create'),
            'edit' => EditApiProduct::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['slug'];
    }

    public static function getLabel(): ?string
    {
        return __('Api Products');
    }

    public static function getSingularLabel(): ?string
    {
        return __('Api Product');
    }

    public static function service(): ServiceInterface
    {
        return new ApiProductService();
    }

    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups." . Constants::NAVIGATION_GROUPS['CONTENT']);
    }
}
