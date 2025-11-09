<?php

namespace NinjaPortal\Admin\Resources\SettingGroup\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(__('Group Name'))
                        ->regex('/^[a-zA-Z0-9_]+$/')
                        ->required(),
                ])
            ]);
    }
}
