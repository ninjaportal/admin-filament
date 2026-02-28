<?php

namespace NinjaPortal\Admin\Resources\SettingGroups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Setting group'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('Branding'))
                        ->helperText(__('Use clear group names so administrators can quickly find related settings together.'))
                        ->unique(ignoreRecord: true),
                ]),
        ]);
    }
}
