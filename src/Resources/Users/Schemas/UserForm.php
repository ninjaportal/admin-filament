<?php

namespace NinjaPortal\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use NinjaPortal\Portal\Models\User;
use NinjaPortal\Portal\Utils;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Account'))
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('first_name')
                            ->label(__('First name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('Last name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),
                        ToggleButtons::make('status')
                            ->label(__('Status'))
                            ->inline()
                            ->options(User::$USER_STATUS)
                            ->colors([
                                User::$ACTIVE_STATUS => 'success',
                                User::$INACTIVE_STATUS => 'danger',
                                User::$DEFAULT_STATUS => 'warning',
                            ])
                            ->default(User::$DEFAULT_STATUS),
                        ToggleButtons::make('sync_with_apigee')
                            ->label(__('Sync with Apigee'))
                            ->inline()
                            ->boolean(),
                    ]),
                ]),
            Section::make(__('Relationships'))
                ->schema([
                    Select::make('audience_ids')
                        ->label(__('Audiences'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(fn () => (Utils::getAudienceModel())::query()->orderBy('name')->pluck('name', 'id')->all()),
                    KeyValue::make('custom_attributes')
                        ->label(__('Custom attributes')),
                ]),
        ]);
    }
}
