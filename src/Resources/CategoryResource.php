<?php

namespace NinjaPortal\Admin\Resources;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
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
use NinjaPortal\Admin\Concerns\HasNinjaService;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\CategoryResource\Pages;
use NinjaPortal\FilamentTranslations\Resources\Concerns\Translatable;
use NinjaPortal\Portal\Models\Category;
use NinjaPortal\Portal\Services\CategoryService;
use NinjaPortal\Portal\Services\IService;

class CategoryResource extends Resource
{
    use HasNinjaService;
    use Translatable;


    protected static ?string $model = Category::class;

    protected static ?string $slug = 'categories';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make()->schema([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($get, $set) => $set('slug', str()->slug($get('name'))))
                    ->required(),
                TextInput::make('slug')
                    ->disabled(fn($record) => $record && $record->exists)
                    ->label(__('Slug'))
                    ->required(),
                Textarea::make('short_description')
                    ->label(__('Short Description'))
                    ->rows(3),
                RichEditor::make('description')
                    ->label(__('Description'))
            ]),
            Section::make()->schema([
                FileUpload::make('thumbnail')
                    ->label(__('Thumbnail'))
                    ->image(),
            ])
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
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

    public static function service(): IService
    {
        return new CategoryService();
    }

    public static function getNavigationGroup(): ?string
    {
        return __(Constants::NAVIGATION_GROUPS['CONTENT']);
    }
}
