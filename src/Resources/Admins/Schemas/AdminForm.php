<?php

namespace NinjaPortal\Admin\Resources\Admins\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use NinjaPortal\Portal\Utils;
use Spatie\Permission\Models\Role;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        $guard = (string) config('portal-admin.panel.rbac_guard', 'admin');

        return $schema->components([
            Section::make(__('Admin account'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label(__('Email'))
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('password')
                        ->label(__('Password'))
                        ->password()
                        ->revealable()
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create'),
                    Select::make('role_ids')
                        ->label(__('Roles'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(fn () => (Utils::getRoleModel() ?: Role::class)::query()->where('guard_name', $guard)->orderBy('name')->pluck('name', 'id')->all()),
                ]),
        ]);
    }
}
