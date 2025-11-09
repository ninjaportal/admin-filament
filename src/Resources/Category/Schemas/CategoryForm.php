<?php

namespace NinjaPortal\Admin\Resources\Category\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($get, $set) => $set('slug', str()->slug($get('name'))))
                    ->required(),
                TextInput::make('slug')
                    ->disabled(fn ($record) => $record && $record->exists)
                    ->label(__('Slug'))
                    ->required(),
                Textarea::make('short_description')
                    ->label(__('Short Description'))
                    ->rows(3),
                RichEditor::make('description')
                    ->label(__('Description'))
            ])->columnSpan(2),
            Section::make()->schema([
                FileUpload::make('thumbnail')
                    ->label(__('Thumbnail'))
                    ->image(),
            ]),
        ])->columns(3);
    }
}
