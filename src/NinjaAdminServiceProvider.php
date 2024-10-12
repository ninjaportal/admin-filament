<?php

namespace NinjaPortal\Admin;

use Illuminate\Support\Facades\Config;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NinjaAdminServiceProvider extends PackageServiceProvider
{

    public function configurePackage(Package $package): void
    {
        $package->name('ninjaadmin')
            ->runsMigrations()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageRegistered()
    {
        $this->publishes(
            [__DIR__ . '/../database/migrations/' => database_path('migrations')],
            $this->package->name.'-migrations'
        );
    }

    public function packageBooted()
    {
        Config::set('auth.providers.admin',[
            'driver' => 'eloquent',
            'model' => \NinjaPortal\Admin\Models\Admin::class,
        ]);

        Config::set('auth.guards.admin',[
            'driver' => 'session',
            'provider' => 'admin',
        ]);
    }



}
