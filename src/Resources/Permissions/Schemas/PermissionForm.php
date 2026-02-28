<?php

namespace NinjaPortal\Admin\Resources\Permissions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        $guard = (string) config('portal-admin.panel.rbac_guard', 'admin');

        return $schema->components([
            Section::make(__('Permission'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255)
                        ->unique(
                            ignoreRecord: true,
                            modifyRuleUsing: fn (Unique $rule) => $rule->where('guard_name', $guard),
                        ),
                ]),
        ]);
    }
}
