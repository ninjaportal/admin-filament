<?php

namespace NinjaPortal\Admin\Resources\Admin\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required(),
                    TextInput::make('email')
                        ->label(__('Email'))
                        ->required(),
                    TextInput::make('password')
                        ->label(__('Password'))
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->formatStateUsing(fn () => null)
                        ->required(fn (string $context): bool => $context === 'create'),
                    Select::make('roles')
                        ->relationship('roles', 'name')
                        ->preload()
                        ->multiple()
                        ->searchable()
                ])->columns(2)
            ]);
    }
}
