<?php

namespace NinjaPortal\Admin\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;
use NinjaPortal\Portal\Utils;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $guard = (string) config('portal-admin.panel.rbac_guard', 'admin');

        return $schema->components([
            Section::make(__('Role'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255)
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule) => $rule->where('guard_name', $guard),
                        ),
                    Select::make('permission_ids')
                        ->label(__('Permissions'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(fn () => (Utils::getPermissionModel() ?: Permission::class)::query()->where('guard_name', $guard)->orderBy('name')->pluck('name', 'id')->all()),
                ]),
        ]);
    }
}
