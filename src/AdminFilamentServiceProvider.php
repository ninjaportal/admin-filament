<?php

namespace NinjaPortal\Admin;

use LogicException;
use NinjaPortal\Admin\Support\ResourceRegistry;
use NinjaPortal\Portal\Utils;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AdminFilamentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('portal-admin')
            ->hasConfigFile('portal-admin')
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ResourceRegistry::class);

        $guard = (string) config('portal-admin.panel.guard', 'admin_panel');
        $provider = (string) config('portal-admin.panel.provider', 'admins');
        $rbacGuard = (string) config('portal-admin.panel.rbac_guard', Utils::getAdminRbacGuard());
        $apiAdminGuard = (string) config('portal-api.auth.guards.admin', Utils::getAdminRbacGuard());
        $adminModel = Utils::getAdminModel();

        if (in_array($guard, array_unique(array_filter([$rbacGuard, $apiAdminGuard])), true)) {
            throw new LogicException(sprintf(
                'The Filament panel guard [%s] conflicts with the admin RBAC/API guard. Configure portal-admin.panel.guard to a dedicated session guard such as [admin_panel].',
                $guard,
            ));
        }

        if ($adminModel) {
            config()->set("auth.providers.{$provider}", array_filter([
                'driver' => 'eloquent',
                'model' => $adminModel,
            ]));
        }

        config()->set("auth.guards.{$guard}", array_filter([
            'driver' => 'session',
            'provider' => $provider,
        ]));

        if (! config()->has("auth.passwords.{$provider}")) {
            config()->set("auth.passwords.{$provider}", [
                'provider' => $provider,
                'table' => 'password_reset_tokens',
                'expire' => 60,
                'throttle' => 60,
            ]);
        }
    }
}
