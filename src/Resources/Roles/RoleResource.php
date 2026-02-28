<?php

namespace NinjaPortal\Admin\Resources\Roles;

use Filament\Resources\Pages\PageRegistration;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Admin\Resources\Roles\Pages\CreateRole;
use NinjaPortal\Admin\Resources\Roles\Pages\EditRole;
use NinjaPortal\Admin\Resources\Roles\Pages\ListRoles;
use NinjaPortal\Admin\Resources\Roles\Schemas\RoleForm;
use NinjaPortal\Admin\Resources\Roles\Tables\RolesTable;
use NinjaPortal\Portal\Contracts\Services\RoleServiceInterface;
use NinjaPortal\Portal\Utils;
use Spatie\Permission\Models\Role;

class RoleResource extends PortalResource
{
    public static function getModel(): string
    {
        return Utils::getRoleModel() ?: Role::class;
    }

    public static function getResourceKey(): string
    {
        return 'roles';
    }

    public static function service(): RoleServiceInterface
    {
        return app(RoleServiceInterface::class);
    }

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table, static::class);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => static::page('index', ListRoles::class)::route('/'),
            'create' => static::page('create', CreateRole::class)::route('/create'),
            'edit' => static::page('edit', EditRole::class)::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return (string) config('portal-admin.panel.navigation.access', __('portal-admin::portal-admin.navigation.access'));
    }

    public static function getEloquentQuery(): Builder
    {
        $guard = (string) config('portal-admin.panel.rbac_guard', 'admin');

        return parent::getEloquentQuery()->where('guard_name', $guard);
    }

    public static function mutateFormDataBeforeFill(array $data, ?Model $record = null): array
    {
        if ($record instanceof Role) {
            $record->loadMissing('permissions');
            $data['permission_ids'] = $record->permissions->pluck('id')->all();
        }

        return $data;
    }

    public static function createUsingService(array $data): Model
    {
        $permissionIds = Arr::pull($data, 'permission_ids', []);
        $data['guard_name'] = (string) config('portal-admin.panel.rbac_guard', 'admin');

        /** @var Role $role */
        $role = parent::createUsingService($data);

        if (is_array($permissionIds)) {
            $role = static::service()->syncPermissions($role, $permissionIds);
        }

        $role->loadMissing('permissions');

        return $role;
    }

    public static function updateUsingService(Model $record, array $data): Model
    {
        $permissionIds = Arr::pull($data, 'permission_ids', null);
        /** @var Role $role */
        $role = parent::updateUsingService($record, $data);

        if (is_array($permissionIds)) {
            $role = static::service()->syncPermissions($role, $permissionIds);
        }

        $role->loadMissing('permissions');

        return $role;
    }
}
