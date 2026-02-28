<?php

namespace NinjaPortal\Admin\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use NinjaPortal\Admin\Support\TranslatableTabs;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        $defaultLocale = array_key_first(config('ninjaportal.locales', ['en' => 'English'])) ?: 'en';

        return $schema->components([
            Section::make(__('Category'))
                ->schema([
                    TextInput::make('slug')
                        ->label(__('Slug'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
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
                                ->disk('public')
                                ->directory("portal-admin/categories/{$locale}"),
                        ];
                    }, __('Translations')),
                ]),
        ]);
    }
}
