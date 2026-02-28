<?php

namespace NinjaPortal\Admin\Tests\Feature;

use Livewire\Livewire;
use NinjaPortal\Admin\Resources\Permissions\Pages\CreatePermission;
use NinjaPortal\Admin\Resources\Roles\Pages\ListRoles;
use NinjaPortal\Admin\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RbacResourceTest extends TestCase
{
    public function test_roles_resource_only_lists_roles_for_the_admin_guard(): void
    {
        $adminRole = Role::query()->create([
            'name' => 'support-admin',
            'guard_name' => 'admin',
        ]);

        $webRole = Role::query()->create([
            'name' => 'support-admin',
            'guard_name' => 'web',
        ]);

        Livewire::test(ListRoles::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$adminRole])
            ->assertCanNotSeeTableRecords([$webRole]);
    }

    public function test_permissions_can_be_created_with_the_same_name_as_another_guard(): void
    {
        Permission::query()->create([
            'name' => 'portal.custom.audit',
            'guard_name' => 'web',
        ]);

        Livewire::test(CreatePermission::class)
            ->fillForm([
                'name' => 'portal.custom.audit',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('permissions', [
            'name' => 'portal.custom.audit',
            'guard_name' => 'admin',
        ]);
    }
}
