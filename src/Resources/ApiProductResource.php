<?php

namespace NinjaPortal\Admin\Resources;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use LaraApigee;
use NinjaPortal\Admin\Concerns\HasNinjaService;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\ApiProductResource\Pages;
use NinjaPortal\FilamentTranslations\Resources\Concerns\Translatable;
use NinjaPortal\Portal\Models\ApiProduct;
use NinjaPortal\Portal\Models\Audience;
use NinjaPortal\Portal\Models\Category;
use NinjaPortal\Portal\Services\ApiProductService;
use NinjaPortal\Portal\Contracts\Services\ServiceInterface;

class ApiProductResource extends Resource
{
    use Translatable, HasNinjaService;

    protected static ?string $model = ApiProduct::class;

    protected static ?string $slug = 'api-products';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($get, $set) => $set('slug', str()->slug($get('name'))))
                        ->required(),
                    TextInput::make('slug')
                        ->disabled(fn($record) => $record && $record->exists)
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
                        ->options(fn() => collect((new ApiProductService())->apigeeProducts())
                            ->mapWithKeys(fn($p) => [$p->getName() => $p->getName()]))
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
                        ->getSearchResultsUsing(fn($search) => Category::where("slug", "like", "%$search%")->get()
                            ->map(fn($category) => [
                                $category->id => $category->name ?? $category->slug,
                            ]))
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name ?? $record->slug)
                        ->required(),
                    Select::make('audiences')
                        ->label(__('Audiences'))
                        ->relationship(titleAttribute: 'name')
                        ->searchable()
                        ->getSearchResultsUsing(fn($search) => Audience::where("name", "like", "%$search%")->get()
                            ->pluck('name', 'id'))
                        ->multiple(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('audiences.name'),
                TextColumn::make('categories.name'),
                TextColumn::make('visibility'),
            ])
            ->filters([

            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiProducts::route('/'),
            'create' => Pages\CreateApiProduct::route('/create'),
            'edit' => Pages\EditApiProduct::route('/{record}/edit'),
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
        return __("ninjaadmin::ninjaadmin.navigation_groups.".Constants::NAVIGATION_GROUPS['CONTENT']);
    }
}
