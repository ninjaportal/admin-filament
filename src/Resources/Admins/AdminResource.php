<?php

namespace NinjaPortal\Admin\Resources\Admins;

use Filament\Resources\Pages\PageRegistration;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use NinjaPortal\Admin\Resources\Admins\Pages\CreateAdmin;
use NinjaPortal\Admin\Resources\Admins\Pages\EditAdmin;
use NinjaPortal\Admin\Resources\Admins\Pages\ListAdmins;
use NinjaPortal\Admin\Resources\Admins\Schemas\AdminForm;
use NinjaPortal\Admin\Resources\Admins\Tables\AdminsTable;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Portal\Contracts\Services\AdminServiceInterface;
use NinjaPortal\Portal\Models\Admin;
use NinjaPortal\Portal\Utils;

class AdminResource extends PortalResource
{
    public static function getModel(): string
    {
        return Utils::getAdminModel() ?: Admin::class;
    }

    public static function getResourceKey(): string
    {
        return 'admins';
    }

    public static function service(): AdminServiceInterface
    {
        return app(AdminServiceInterface::class);
    }

    public static function form(Schema $schema): Schema
    {
        return AdminForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminsTable::configure($table, static::class);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => static::page('index', ListAdmins::class)::route('/'),
            'create' => static::page('create', CreateAdmin::class)::route('/create'),
            'edit' => static::page('edit', EditAdmin::class)::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return (string) config('portal-admin.panel.navigation.access', __('portal-admin::portal-admin.navigation.access'));
    }

    public static function mutateFormDataBeforeFill(array $data, ?Model $record = null): array
    {
        if ($record instanceof Admin) {
            $record->loadMissing('roles');
            $data['role_ids'] = $record->roles->pluck('id')->all();
        }

        return $data;
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make((string) $data['password']);
        }

        return $data;
    }

    public static function mutateFormDataBeforeUpdate(Model $record, array $data): array
    {
        if (! filled($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make((string) $data['password']);
        }

        return $data;
    }

    public static function createUsingService(array $data): Model
    {
        $roleIds = Arr::pull($data, 'role_ids', []);
        /** @var Admin $admin */
        $admin = parent::createUsingService($data);

        if (is_array($roleIds)) {
            $admin = static::service()->syncRoles($admin, $roleIds);
        }

        $admin->loadMissing('roles');

        return $admin;
    }

    public static function updateUsingService(Model $record, array $data): Model
    {
        $roleIds = Arr::pull($data, 'role_ids', null);
        /** @var Admin $admin */
        $admin = parent::updateUsingService($record, $data);

        if (is_array($roleIds)) {
            $admin = static::service()->syncRoles($admin, $roleIds);
        }

        $admin->loadMissing('roles');

        return $admin;
    }
}
