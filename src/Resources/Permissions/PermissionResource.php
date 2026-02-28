<?php

namespace NinjaPortal\Admin\Resources\Permissions;

use Filament\Resources\Pages\PageRegistration;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use NinjaPortal\Admin\Resources\Permissions\Pages\CreatePermission;
use NinjaPortal\Admin\Resources\Permissions\Pages\EditPermission;
use NinjaPortal\Admin\Resources\Permissions\Pages\ListPermissions;
use NinjaPortal\Admin\Resources\Permissions\Schemas\PermissionForm;
use NinjaPortal\Admin\Resources\Permissions\Tables\PermissionsTable;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Portal\Contracts\Services\PermissionServiceInterface;
use NinjaPortal\Portal\Utils;
use Spatie\Permission\Models\Permission;

class PermissionResource extends PortalResource
{
    public static function getModel(): string
    {
        return Utils::getPermissionModel() ?: Permission::class;
    }

    public static function getResourceKey(): string
    {
        return 'permissions';
    }

    public static function service(): PermissionServiceInterface
    {
        return app(PermissionServiceInterface::class);
    }

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table, static::class);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => static::page('index', ListPermissions::class)::route('/'),
            'create' => static::page('create', CreatePermission::class)::route('/create'),
            'edit' => static::page('edit', EditPermission::class)::route('/{record}/edit'),
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

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guard_name'] = (string) config('portal-admin.panel.rbac_guard', 'admin');

        return $data;
    }
}
