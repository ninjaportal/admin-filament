<?php

namespace NinjaPortal\Admin\Resources\Users;

use Filament\Actions\Action;
use Filament\Resources\Pages\PageRegistration;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Admin\Resources\Users\Pages\CreateUser;
use NinjaPortal\Admin\Resources\Users\Pages\EditUser;
use NinjaPortal\Admin\Resources\Users\Pages\ListUsers;
use NinjaPortal\Admin\Resources\Users\Pages\ManageUserApps;
use NinjaPortal\Admin\Resources\Users\Schemas\UserForm;
use NinjaPortal\Admin\Resources\Users\Tables\UsersTable;
use NinjaPortal\Portal\Contracts\Services\UserServiceInterface;
use NinjaPortal\Portal\Models\User;
use NinjaPortal\Portal\Utils;

class UserResource extends PortalResource
{
    public static function getModel(): string
    {
        return Utils::getUserModel() ?: User::class;
    }

    public static function getResourceKey(): string
    {
        return 'users';
    }

    public static function service(): UserServiceInterface
    {
        return app(UserServiceInterface::class);
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table, static::class);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => static::page('index', ListUsers::class)::route('/'),
            'create' => static::page('create', CreateUser::class)::route('/create'),
            'edit' => static::page('edit', EditUser::class)::route('/{record}/edit'),
            'apps' => static::page('apps', ManageUserApps::class)::route('/{record}/apps'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return (string) config('portal-admin.panel.navigation.users', __('portal-admin::portal-admin.navigation.users'));
    }

    public static function mutateFormDataBeforeFill(array $data, ?Model $record = null): array
    {
        if ($record instanceof User) {
            $record->loadMissing('audiences');
            $data['audience_ids'] = $record->audiences->pluck('id')->all();
        }

        return $data;
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (! filled($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }

    public static function mutateFormDataBeforeUpdate(Model $record, array $data): array
    {
        if (! filled($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }

    public static function createUsingService(array $data): Model
    {
        $audienceIds = Arr::pull($data, 'audience_ids', []);
        /** @var User $user */
        $user = parent::createUsingService($data);

        if (is_array($audienceIds)) {
            $user = static::service()->syncAudiences($user, $audienceIds);
        }

        $user->loadMissing('audiences');

        return $user;
    }

    public static function updateUsingService(Model $record, array $data): Model
    {
        $audienceIds = Arr::pull($data, 'audience_ids', null);
        /** @var User $user */
        $user = parent::updateUsingService($record, $data);

        if (is_array($audienceIds)) {
            $user = static::service()->syncAudiences($user, $audienceIds);
        }

        $user->loadMissing('audiences');

        return $user;
    }

    public static function appsAction(Model $record): Action
    {
        return Action::make('apps')
            ->label(__('portal-admin::portal-admin.actions.manage_apps'))
            ->icon('heroicon-o-key')
            ->url(static::getUrl('apps', ['record' => $record]));
    }
}
