<?php

namespace NinjaPortal\Admin\Resources\User\Pages\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use NinjaPortal\Admin\Resources\User\Pages\UserAppsPage;

class ManageUserAppSchema
{
    public static function make(bool $isCreate = false): array
    {
        return [
            Section::make(__('App Details'))
                ->icon('heroicon-o-information-circle')
//                ->headerActions([
//                    Action::make('toggle-status')
//                        ->icon(fn ($get) => $get('status') === UserAppsPage::APPROVED_STATUS ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
//                        ->color(fn ($get) => $get('status') === UserAppsPage::APPROVED_STATUS ? 'danger' : 'success')
//                        ->label(fn ($get) => $get('status') === UserAppsPage::APPROVED_STATUS ? __('Revoke') : __('Approve'))
//                        ->action(function (Action $action, callable $set, callable $get): void {
//                            $targetStatus = $get('status') === UserAppsPage::APPROVED_STATUS
//                                ? UserAppsPage::REVOKED_STATUS
//                                : UserAppsPage::APPROVED_STATUS;
//
//                            $credentials = collect($get('credentials') ?? [])
//                                ->map(function (array $credential) use ($targetStatus): array {
//                                    $credential['status'] = $targetStatus;
//                                    $credential['apiProducts'] = collect($credential['apiProducts'] ?? [])
//                                        ->map(function (array $product) use ($targetStatus): array {
//                                            $product['status'] = $targetStatus;
//
//                                            return $product;
//                                        })
//                                        ->toArray();
//
//                                    return $credential;
//                                })
//                                ->toArray();
//
//                            $set('status', $targetStatus);
//                            $set('credentials', $credentials);
//                        }),
//                ])
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->disabled(fn ($get) => $get('name') !== null)
                        ->dehydrated()
                        ->hint(__('Name/ID of the app'))
                        ->required(),
                    TextInput::make('displayName')
                        ->label(__('Display Name'))
                        ->formatStateUsing(fn ($get) => $get('displayName') ?? $get('name'))
                        ->hint(fn ($get) => ! $get('displayName') ? $get('name') : null)
                        ->required(),
                    TextInput::make('callbackUrl')
                        ->label(__('Callback URL'))
                        ->hint(__('A callback URL is required only for 3-legged OAuth.'))
                        ->url(),
                    Textarea::make('description')
                        ->label(__('Description')),
                    Select::make('apiProducts')
                        ->label(__('API Products'))
                        ->multiple()
                        ->searchable()
                        ->visible($isCreate)
                        ->options(fn (UserAppsPage $livewire) => $livewire->apis)
                        ->helperText(__('Select the API products to grant on creation.')),
                    ToggleButtons::make('status')
                        ->label(__('App Status'))
                        ->inline()
                        ->options([
                            UserAppsPage::APPROVED_STATUS => __('Approved'),
                            UserAppsPage::REVOKED_STATUS => __('Revoked'),
                        ])
                        ->colors([
                            UserAppsPage::APPROVED_STATUS => 'success',
                            UserAppsPage::REVOKED_STATUS => 'danger',
                        ])
                        ->default(UserAppsPage::APPROVED_STATUS)
                        ->dehydrated(),
                ])
                ->columns(1),
            Section::make(__('Credentials'))
                ->icon('heroicon-o-key')
                ->visible(! $isCreate)
                ->schema([
                    Repeater::make('credentials')
                        ->label(__('Credentials'))
                        ->collapsible()
                        ->grid(1)
                        ->default([])
                        ->createItemButtonLabel(__('Add Credential'))
                        ->itemLabel(fn (array $state): ?string => $state['consumerKey'] ?? __('New Credential'))
                        ->schema([
                            TextInput::make('consumerKey')
                                ->label(__('Consumer Key'))
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('consumerSecret')
                                ->label(__('Consumer Secret'))
                                ->disabled()
                                ->dehydrated(),
                            ToggleButtons::make('status')
                                ->label(__('Credential Status'))
                                ->inline()
                                ->options([
                                    UserAppsPage::APPROVED_STATUS => __('Approved'),
                                    UserAppsPage::REVOKED_STATUS => __('Revoked'),
                                ])
                                ->colors([
                                    UserAppsPage::APPROVED_STATUS => 'success',
                                    UserAppsPage::REVOKED_STATUS => 'danger',
                                ])
                                ->default(UserAppsPage::APPROVED_STATUS)
                                ->dehydrated(),
                            Repeater::make('apiProducts')
                                ->label(__('API Products'))
                                ->collapsible()
                                ->default([])
                                ->defaultItems(1)
                                ->createItemButtonLabel(__('Add API Product'))
                                ->itemLabel(fn (array $state): ?string => $state['apiproduct'] ?? __('New Product'))
                                ->schema([
                                    Select::make('apiproduct')
                                        ->label(__('Product'))
                                        ->options(fn (UserAppsPage $livewire) => $livewire->apis)
                                        ->searchable()
                                        ->required(),
                                    ToggleButtons::make('status')
                                        ->label(__('Status'))
                                        ->inline()
                                        ->options([
                                            UserAppsPage::APPROVED_STATUS => __('Approved'),
                                            UserAppsPage::REVOKED_STATUS => __('Revoked'),
                                        ])
                                        ->default(UserAppsPage::APPROVED_STATUS)
                                        ->dehydrated(),
                                ])
                                ->grid(1),
                        ]),
                ])
                ->columns(1),
        ];
    }
}
