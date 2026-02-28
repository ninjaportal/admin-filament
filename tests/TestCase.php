<?php

namespace NinjaPortal\Admin\Tests;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use NinjaPortal\Portal\Models\Admin;
use NinjaPortal\Portal\Seeders\RbacSeeder;
use Spatie\Permission\Models\Role;
use Tests\TestCase as ApplicationTestCase;

abstract class TestCase extends ApplicationTestCase
{
    protected Admin $admin;

    protected static bool $databaseMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$databaseMigrated) {
            $this->artisan('migrate', ['--force' => true])->assertSuccessful();
            static::$databaseMigrated = true;
        }

        DB::connection()->beginTransaction();
        $this->beforeApplicationDestroyed(static function (): void {
            DB::connection()->rollBack();
        });

        Filament::setCurrentPanel(Filament::getPanel('portal-admin'));

        $this->seed(RbacSeeder::class);
        $this->admin = $this->signInAdmin();
    }

    protected function signInAdmin(?Admin $admin = null): Admin
    {
        $guard = (string) config('portal-admin.panel.guard', 'admin_panel');

        $admin ??= Admin::query()->create([
            'name' => 'Portal Admin',
            'email' => 'admin@ninjaportal.test',
            'password' => Hash::make('password'),
        ]);

        $role = Role::query()
            ->where('guard_name', 'admin')
            ->where('name', 'super_admin')
            ->firstOrFail();

        if (! $admin->hasRole($role)) {
            $admin->assignRole($role);
        }

        $this->actingAs($admin, $guard);

        return $admin;
    }
}
