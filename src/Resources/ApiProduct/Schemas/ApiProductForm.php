<?php

namespace NinjaPortal\Admin\Resources\ApiProduct\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use NinjaPortal\Portal\Models\ApiProduct;
use NinjaPortal\Portal\Models\Audience;
use NinjaPortal\Portal\Models\Category;
use NinjaPortal\Portal\Services\ApiProductService;

class ApiProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($get, $set) => $set('slug', str()->slug($get('name'))))
                        ->required(),
                    TextInput::make('slug')
                        ->disabled(fn ($record) => $record && $record->exists)
                        ->label(__('Slug'))
                        ->regex('/^[a-z0-9-]+$/')
                        ->required(),
                    Textarea::make('short_description')
                        ->label(__('Short Description'))
                        ->rows(3),
                    RichEditor::make('description')
                        ->label(__('Description')),
                    FileUpload::make('thumbnail')
                        ->label(__('Thumbnail'))
                        ->image(),
                    FileUpload::make('swagger_url')
                        ->label(__('Swagger URL'))
                        ->disk(ApiProduct::$STORAGE_DISK)
                        ->acceptedFileTypes([
                            'application/json',
                            'application/yaml',
                        ]),
                ]),
                Section::make()->schema([
                    Select::make('apigee_product_id')
                        ->label(__('Apigee Product'))
                        ->lazy()
                        ->options(function () {
                            $products = cache()->remember('apigee:products', now()->addMinutes(5), function () {
                                return (new ApiProductService())->apigeeProducts();
                            });
                            return collect($products)->mapWithKeys(fn ($p) => [$p->getName() => $p->getName()]);
                        })
                        ->searchable(),
                    Select::make('visibility')
                        ->label(__('Visibility'))
                        ->options([
                            'public' => __('Public'),
                            'private' => __('Private'),
                        ])
                        ->required(),
                    Select::make('category_id')
                        ->label(__('Categories'))
                        ->relationship('categories', 'id')
                        ->searchable()
                        ->multiple()
                        ->getSearchResultsUsing(fn ($search) => Category::where('slug', 'like', "%$search%")
                            ->get()
                            ->map(fn ($category) => [
                                $category->id => $category->name ?? $category->slug,
                            ]))
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? $record->slug)
                        ->required(),
                    Select::make('audiences')
                        ->label(__('Audiences'))
                        ->relationship(titleAttribute: 'name')
                        ->searchable()
                        ->getSearchResultsUsing(fn ($search) => Audience::where('name', 'like', "%$search%")
                            ->get()
                            ->pluck('name', 'id'))
                        ->multiple(),
                ])
            ]);
    }
}
