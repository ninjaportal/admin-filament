<?php

namespace NinjaPortal\Admin\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use NinjaPortal\Admin\Support\Settings\SettingUi;
use NinjaPortal\Portal\Models\SettingGroup;
use NinjaPortal\Portal\Utils;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Setting details'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('key')
                            ->label(__('Key'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder('portal.name')
                            ->helperText(__('Use a stable dotted key so your application can reference this setting confidently.'))
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        TextInput::make('label')
                            ->label(__('Display label'))
                            ->maxLength(255)
                            ->placeholder(__('Portal name')),
                        Select::make('setting_group_id')
                            ->label(__('Setting group'))
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder(__('Choose a group'))
                            ->options(fn () => (Utils::getSettingGroupModel() ?: SettingGroup::class)::query()->orderBy('name')->pluck('name', 'id')->all()),
                    ]),
                ]),
            Section::make(__('Value'))
                ->schema([
                    ToggleButtons::make('type')
                        ->label(__('Value type'))
                        ->inline()
                        ->live()
                        ->required()
                        ->options(SettingUi::typeOptions())
                        ->icons([
                            'string' => SettingUi::iconForType('string'),
                            'integer' => SettingUi::iconForType('integer'),
                            'boolean' => SettingUi::iconForType('boolean'),
                            'float' => SettingUi::iconForType('float'),
                            'json' => SettingUi::iconForType('json'),
                        ])
                        ->colors([
                            'string' => SettingUi::colorForType('string'),
                            'integer' => SettingUi::colorForType('integer'),
                            'boolean' => SettingUi::colorForType('boolean'),
                            'float' => SettingUi::colorForType('float'),
                            'json' => SettingUi::colorForType('json'),
                        ])
                        ->default('string'),
                    Textarea::make('value_string')
                        ->label(__('Text value'))
                        ->rows(3)
                        ->placeholder(__('Enter the setting value'))
                        ->helperText(__('Best for plain text, URLs, color codes, comma-separated lists, and labels.'))
                        ->visible(fn (Get $get): bool => $get('type') === 'string'),
                    TextInput::make('value_integer')
                        ->label(__('Number value'))
                        ->numeric()
                        ->step(1)
                        ->inputMode('numeric')
                        ->placeholder('0')
                        ->helperText(__('Use this for counts, limits, or numeric IDs.'))
                        ->visible(fn (Get $get): bool => $get('type') === 'integer'),
                    ToggleButtons::make('value_boolean')
                        ->label(__('Toggle state'))
                        ->inline()
                        ->boolean()
                        ->default(false)
                        ->visible(fn (Get $get): bool => $get('type') === 'boolean'),
                    TextInput::make('value_float')
                        ->label(__('Decimal value'))
                        ->numeric()
                        ->step('any')
                        ->inputMode('decimal')
                        ->placeholder('0.0')
                        ->helperText(__('Use decimals for percentages, ratios, and precise thresholds.'))
                        ->visible(fn (Get $get): bool => $get('type') === 'float'),
                    Textarea::make('value_json')
                        ->label(__('JSON value'))
                        ->rows(12)
                        ->placeholder("{\n  \"key\": \"value\"\n}")
                        ->helperText(__('Stored as structured JSON. Useful for grouped options or advanced configuration payloads.'))
                        ->rules(['nullable', 'json'])
                        ->visible(fn (Get $get): bool => $get('type') === 'json'),
                ])
                ->columns(1),
        ]);
    }
}
