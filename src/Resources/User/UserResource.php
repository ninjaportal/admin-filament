<?php

namespace NinjaPortal\Admin\Resources\User;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use NinjaPortal\Admin\Concerns\HasNinjaService;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\User\Schemas\UserForm;
use NinjaPortal\Admin\Resources\User\Tables\UsersTable;
use NinjaPortal\Admin\Resources\User\Pages\CreateUser;
use NinjaPortal\Admin\Resources\User\Pages\EditUser;
use NinjaPortal\Admin\Resources\User\Pages\ListUsers;
use NinjaPortal\Admin\Resources\User\Pages\UserAppsPage;
use NinjaPortal\Portal\Contracts\Services\ServiceInterface;
use NinjaPortal\Portal\Contracts\Services\UserServiceInterface;
use NinjaPortal\Portal\Models\User;
use NinjaPortal\Portal\Services\UserService;

class UserResource extends Resource
{

    use HasNinjaService;

    protected static ?string $model = User::class;

    protected static ?string $slug = 'users';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'apps' => UserAppsPage::route('/{record}/apps'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['email'];
    }

    public static function service(): ServiceInterface
    {
        return app(UserServiceInterface::class);
    }

    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups." . Constants::NAVIGATION_GROUPS['USER']);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }
}
