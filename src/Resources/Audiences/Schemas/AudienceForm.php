<?php

namespace NinjaPortal\Admin\Resources\Audiences\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use NinjaPortal\Portal\Models\ApiProduct;
use NinjaPortal\Portal\Models\User;
use NinjaPortal\Portal\Utils;

class AudienceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Audience'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Select::make('user_ids')
                        ->label(__('Users'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(fn () => (Utils::getUserModel() ?: User::class)::query()->orderBy('email')->pluck('email', 'id')->all()),
                    Select::make('api_product_ids')
                        ->label(__('API products'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(fn () => (Utils::getApiProductModel() ?: ApiProduct::class)::query()->orderBy('slug')->pluck('slug', 'id')->all()),
                ]),
        ]);
    }
}
