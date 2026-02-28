<?php

namespace NinjaPortal\Admin\Resources\ApiProducts;

use Filament\Resources\Pages\PageRegistration;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use NinjaPortal\Admin\Resources\ApiProducts\Pages\CreateApiProduct;
use NinjaPortal\Admin\Resources\ApiProducts\Pages\EditApiProduct;
use NinjaPortal\Admin\Resources\ApiProducts\Pages\ListApiProducts;
use NinjaPortal\Admin\Resources\ApiProducts\Schemas\ApiProductForm;
use NinjaPortal\Admin\Resources\ApiProducts\Tables\ApiProductsTable;
use NinjaPortal\Admin\Resources\Concerns\InteractsWithTranslatableData;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Portal\Contracts\Services\ApiProductServiceInterface;
use NinjaPortal\Portal\Models\ApiProduct;
use NinjaPortal\Portal\Utils;

class ApiProductResource extends PortalResource
{
    use InteractsWithTranslatableData;

    public static function getModel(): string
    {
        return Utils::getApiProductModel() ?: ApiProduct::class;
    }

    public static function getResourceKey(): string
    {
        return 'api_products';
    }

    public static function service(): ApiProductServiceInterface
    {
        return app(ApiProductServiceInterface::class);
    }

    public static function form(Schema $schema): Schema
    {
        return ApiProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApiProductsTable::configure($table, static::class);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => static::page('index', ListApiProducts::class)::route('/'),
            'create' => static::page('create', CreateApiProduct::class)::route('/create'),
            'edit' => static::page('edit', EditApiProduct::class)::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return (string) config('portal-admin.panel.navigation.catalog', __('portal-admin::portal-admin.navigation.catalog'));
    }

    public static function mutateFormDataBeforeFill(array $data, ?Model $record = null): array
    {
        $data = static::withTranslatableFormData($data, $record);

        if ($record instanceof ApiProduct) {
            $record->loadMissing(['categories', 'audiences']);
            $data['category_ids'] = $record->categories->pluck('id')->all();
            $data['audience_ids'] = $record->audiences->pluck('id')->all();
        }

        return $data;
    }

    public static function createUsingService(array $data): Model
    {
        $categoryIds = Arr::pull($data, 'category_ids', []);
        $audienceIds = Arr::pull($data, 'audience_ids', []);

        /** @var ApiProduct $apiProduct */
        $apiProduct = parent::createUsingService($data);

        if (is_array($categoryIds)) {
            $apiProduct = static::service()->syncCategories($apiProduct, $categoryIds);
        }

        if (is_array($audienceIds)) {
            $apiProduct = static::service()->syncAudiences($apiProduct, $audienceIds);
        }

        $apiProduct->loadMissing(['translations', 'categories', 'audiences']);

        return $apiProduct;
    }

    public static function updateUsingService(Model $record, array $data): Model
    {
        $categoryIds = Arr::pull($data, 'category_ids', null);
        $audienceIds = Arr::pull($data, 'audience_ids', null);

        /** @var ApiProduct $apiProduct */
        $apiProduct = parent::updateUsingService($record, $data);

        if (is_array($categoryIds)) {
            $apiProduct = static::service()->syncCategories($apiProduct, $categoryIds);
        }

        if (is_array($audienceIds)) {
            $apiProduct = static::service()->syncAudiences($apiProduct, $audienceIds);
        }

        $apiProduct->loadMissing(['translations', 'categories', 'audiences']);

        return $apiProduct;
    }
}
