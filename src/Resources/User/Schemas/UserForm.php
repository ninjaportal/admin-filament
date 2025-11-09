<?php

namespace NinjaPortal\Admin\Resources\User\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use NinjaPortal\Portal\Models\User;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('first_name')
                        ->label(__('First Name'))
                        ->columnSpan(1)
                        ->required(),
                    TextInput::make('last_name')
                        ->label(__('Last Name'))
                        ->columnSpan(1)
                        ->required(),
                    TextInput::make('email')
                        ->label(__('Email'))
                        ->columnSpan(2)
                        ->unique(ignoreRecord: true)
                        ->required(),
                    TextInput::make('password')
                        ->label(__('Password'))
                        ->password()
                        ->revealable()
                        ->dehydrated(fn ($state) => filled($state))
                        ->formatStateUsing(fn () => null)
                        ->required(fn (string $context): bool => $context === 'create'),
                    Group::make()->schema([
                        ToggleButtons::make('status')
                            ->inline()
                            ->options([
                                User::$ACTIVE_STATUS => __('Active'),
                                User::$INACTIVE_STATUS => __('Inactive'),
                            ])
                            ->colors([
                                User::$ACTIVE_STATUS => 'success',
                                User::$INACTIVE_STATUS => 'danger',
                            ])->columnSpan(1),
                        ToggleButtons::make('sync_with_apigee')
                            ->label(__('Is Synced with Apigee'))
                            ->inline()
                            ->disabled()
                            ->options([
                                1 => __('Yes'),
                                null => __('No'),
                            ])->colors([
                                1 => 'success',
                                0 => 'danger',
                            ])->columnSpan(1),
                    ])->columns(2)
                ])->columns(2),
                Section::make()->schema([
                    Select::make('audiences')
                        ->multiple()
                        ->preload()
                        ->relationship('audiences', 'name')
                        ->searchable(['name']),
                    KeyValue::make('custom_attributes')
                        ->label(__('Custom Attributes'))
                        ->columnSpan(2),
                ])
            ]);
    }
}
