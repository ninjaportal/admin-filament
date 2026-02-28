<?php

namespace NinjaPortal\Admin\Resources\Users\Pages\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;

class UserAppForm
{
    /**
     * @param  array<string, string>  $apiProducts
     * @return array<int, mixed>
     */
    public static function make(array $apiProducts, bool $isCreate = false): array
    {
        $apiProductOptions = static fn (): array => $apiProducts;
        $apiProductsHelperText = $apiProducts === []
            ? __('No Apigee API products are currently available.')
            : __('These products are loaded live from Apigee.');

        return [
            Section::make(__('App details'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->readOnly(fn ($get) => filled($get('name')) && ! $isCreate)
                        ->dehydrated(),
                    TextInput::make('displayName')
                        ->label(__('Display name'))
                        ->required(),
                    TextInput::make('callbackUrl')
                        ->label(__('Callback URL'))
                        ->url(),
                    Textarea::make('description')
                        ->label(__('Description')),
                    Select::make('apiProducts')
                        ->label(__('API products'))
                        ->helperText($apiProductsHelperText)
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options($apiProductOptions)
                        ->visible($isCreate),
                    ToggleButtons::make('status')
                        ->label(__('Status'))
                        ->inline()
                        ->options([
                            'approved' => __('Approved'),
                            'revoked' => __('Revoked'),
                        ])
                        ->default('approved'),
                ]),
            Section::make(__('Credentials'))
                ->visible(! $isCreate)
                ->schema([
                    Repeater::make('credentials')
                        ->label(__('Credentials'))
                        ->default([])
                        ->collapsible()
                        ->addActionLabel(__('Add credential'))
                        ->itemLabel(fn (array $state): ?string => $state['consumerKey'] ?? __('New credential'))
                        ->schema([
                            TextInput::make('consumerKey')
                                ->label(__('Consumer key'))
                                ->readOnly()
                                ->dehydrated(),
                            TextInput::make('consumerSecret')
                                ->label(__('Consumer secret'))
                                ->readOnly()
                                ->dehydrated(),
                            ToggleButtons::make('status')
                                ->label(__('Status'))
                                ->inline()
                                ->options([
                                    'approved' => __('Approved'),
                                    'revoked' => __('Revoked'),
                                ])
                                ->default('approved'),
                            Repeater::make('apiProducts')
                                ->label(__('API products'))
                                ->default([])
                                ->collapsible()
                                ->addActionLabel(__('Add API product'))
                                ->itemLabel(fn (array $state): ?string => $state['apiproduct'] ?? __('New product'))
                                ->schema([
                                    Select::make('apiproduct')
                                        ->label(__('Product'))
                                        ->required()
                                        ->helperText($apiProductsHelperText)
                                        ->searchable()
                                        ->preload()
                                        ->options($apiProductOptions),
                                    ToggleButtons::make('status')
                                        ->label(__('Status'))
                                        ->inline()
                                        ->options([
                                            'approved' => __('Approved'),
                                            'revoked' => __('Revoked'),
                                        ])
                                        ->default('approved'),
                                ]),
                        ]),
                ]),
        ];
    }
}
