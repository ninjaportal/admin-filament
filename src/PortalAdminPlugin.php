<?php

namespace NinjaPortal\Admin;

use Filament\Contracts\Plugin;
use Filament\Panel;
use NinjaPortal\Admin\Support\ResourceRegistry;

class PortalAdminPlugin implements Plugin
{
    public function __construct(protected ResourceRegistry $registry) {}

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'portal-admin';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources($this->registry->resources())
            ->pages($this->registry->pages())
            ->widgets($this->registry->widgets());
    }

    public function boot(Panel $panel): void {}
}
