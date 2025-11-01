<?php

namespace NinjaPortal\Admin\Resources\Audience\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AudienceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->unique()
                        ->regex('/^[a-zA-Z0-9_]+$/')
                        ->required(),
                ])
            ]);
    }
}
