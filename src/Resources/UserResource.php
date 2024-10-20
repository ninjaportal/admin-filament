<?php

namespace NinjaPortal\Admin\Resources;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use NinjaPortal\Admin\Concerns\HasNinjaService;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\UserResource\Pages;
use NinjaPortal\Portal\Models\User;
use NinjaPortal\Portal\Contracts\Services\ServiceInterface;
use NinjaPortal\Portal\Services\UserService;

class UserResource extends Resource
{

    use HasNinjaService;

    protected static ?string $model = User::class;

    protected static ?string $slug = 'users';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        ->dehydrated(fn($state) => filled($state))
                        ->formatStateUsing(fn() => null)
                        ->required(fn(string $context): bool => $context === 'create'),
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
                                true => __('Yes'),
                                false => __('No'),
                            ])
                            ->colors([
                                true => 'success',
                                false => 'danger',
                            ])->columnSpan(1),
                    ])->columns(2)
                ])->columns(2),
                Section::make()->schema([
                    Select::make('audiences')
                        ->relationship('audiences', 'name')
                        ->searchable()
                        ->preload(),
                    KeyValue::make('custom_attributes')
                        ->label(__('Custom Attributes'))
                        ->columnSpan(2),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('Full Name')),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sync_with_apigee')
                    ->label(__('Synced with Apigee'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->colors([
                        true => 'success',
                        false => 'danger',
                    ]),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::$ACTIVE_STATUS => 'success',
                        User::$INACTIVE_STATUS => 'danger',
                        default => 'gray',
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('apps')
                    ->color('info')
                    ->icon('heroicon-o-cube')
                    ->url(fn(Model $record) => Pages\UserAppsPage::getUrl(['record' => $record->getKey()]))
                    ->label(__('Apps')),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'apps' => Pages\UserAppsPage::route('/{record}/apps'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['email'];
    }

    public static function service(): ServiceInterface
    {
        return new UserService();
    }

    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups.".Constants::NAVIGATION_GROUPS['USER']);
    }
}
