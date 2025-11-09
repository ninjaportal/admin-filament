<?php

namespace NinjaPortal\Admin\Resources\Menu\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use NinjaPortal\Portal\Models\Menu;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('slug')
                        ->required()
                        ->label(__('Slug'))
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                        ->unique(Menu::class, 'slug', fn ($record) => $record),
                ])
            ]);
    }
}
