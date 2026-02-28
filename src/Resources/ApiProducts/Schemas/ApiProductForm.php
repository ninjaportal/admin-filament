<?php

namespace NinjaPortal\Admin\Resources\ApiProducts\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use NinjaPortal\Admin\Support\TranslatableTabs;
use NinjaPortal\Portal\Contracts\Services\ApiProductServiceInterface;
use NinjaPortal\Portal\Models\ApiProduct;
use NinjaPortal\Portal\Models\Audience;
use NinjaPortal\Portal\Models\Category;
use NinjaPortal\Portal\Utils;

class ApiProductForm
{
    public static function configure(Schema $schema): Schema
    {
        $defaultLocale = array_key_first(config('ninjaportal.locales', ['en' => 'English'])) ?: 'en';

        return $schema->components([
            Section::make(__('Details'))
                ->schema([
                    TextInput::make('slug')
                        ->label(__('Slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Select::make('apigee_product_id')
                        ->label(__('Apigee product'))
                        ->searchable()
                        ->options(function (): array {
                            return collect(app(ApiProductServiceInterface::class)->apigeeProducts())
                                ->mapWithKeys(fn ($product) => [$product->getName() => $product->getName()])
                                ->all();
                        }),
                    Select::make('visibility')
                        ->label(__('Visibility'))
                        ->required()
                        ->options(ApiProduct::$VISIBILITY),
                    Select::make('category_ids')
                        ->label(__('Categories'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(fn () => (Utils::getCategoryModel() ?: Category::class)::query()->orderBy('slug')->pluck('slug', 'id')->all()),
                    Select::make('audience_ids')
                        ->label(__('Audiences'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(fn () => (Utils::getAudienceModel() ?: Audience::class)::query()->orderBy('name')->pluck('name', 'id')->all()),
                    FileUpload::make('swagger_url')
                        ->label(__('Swagger file'))
                        ->disk(ApiProduct::$STORAGE_DISK)
                        ->directory('portal-admin/api-products/swagger'),
                    FileUpload::make('integration_file')
                        ->label(__('Integration file'))
                        ->disk(ApiProduct::$STORAGE_DISK)
                        ->directory('portal-admin/api-products/integrations'),
                    TagsInput::make('tags')
                        ->label(__('Tags')),
                    KeyValue::make('custom_attributes')
                        ->label(__('Custom attributes')),
                ]),
            Section::make(__('Translations'))
                ->schema([
                    TranslatableTabs::make(function (string $locale) use ($defaultLocale): array {
                        $isDefault = $locale === $defaultLocale;

                        return [
                            TextInput::make("{$locale}.name")
                                ->label(__('Name'))
                                ->required($isDefault)
                                ->dehydratedWhenHidden()
                                ->maxLength(255),
                            Textarea::make("{$locale}.short_description")
                                ->label(__('Short description'))
                                ->dehydratedWhenHidden()
                                ->rows(3),
                            RichEditor::make("{$locale}.description")
                                ->label(__('Description'))
                                ->dehydratedWhenHidden(),
                            FileUpload::make("{$locale}.thumbnail")
                                ->label(__('Thumbnail'))
                                ->image()
                                ->dehydratedWhenHidden()
                                ->disk(ApiProduct::$STORAGE_DISK)
                                ->directory("portal-admin/api-products/thumbnails/{$locale}"),
                        ];
                    }, __('Translations')),
                ]),
        ]);
    }
}
