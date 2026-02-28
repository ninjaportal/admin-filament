<?php

namespace NinjaPortal\Admin;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PortalAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $path = (string) config('portal-admin.panel.path', 'admin');
        $guard = (string) config('portal-admin.panel.guard', 'admin_panel');
        $provider = (string) config('portal-admin.panel.provider', 'admins');
        $broker = (string) config('portal-admin.panel.password_broker', $provider);

        $panel = $panel
            ->id((string) config('portal-admin.panel.id', 'portal-admin'))
            ->path($path)
            ->domain(config('portal-admin.panel.domain'))
            ->authGuard($guard)
            ->authPasswordBroker($broker)
            ->brandName((string) config('portal-admin.panel.brand_name', 'NinjaPortal'))
            ->brandLogo(config('portal-admin.panel.brand_logo'))
            ->brandLogoHeight((string) config('portal-admin.panel.brand_logo_height', '2rem'))
            ->plugin(PortalAdminPlugin::make())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        if (config('portal-admin.panel.default', false)) {
            $panel = $panel->default();
        }

        if (config('portal-admin.panel.login', true)) {
            $panel = $panel->login();
        }

        if (config('portal-admin.panel.profile', true)) {
            $panel = $panel->profile();
        }

        if (config('portal-admin.panel.password_reset', false)) {
            $panel = $panel->passwordReset();
        }

        foreach ((array) config('portal-admin.panel.colors', []) as $name => $color) {
            if (is_string($color)) {
                $panel = $panel->colors([
                    $name => $this->resolveColor($color),
                ]);
            }
        }

        return $panel;
    }

    protected function resolveColor(string $color): array
    {
        return match ($color) {
            'amber' => Color::Amber,
            'blue' => Color::Blue,
            'emerald' => Color::Emerald,
            'gray' => Color::Gray,
            'green' => Color::Green,
            'indigo' => Color::Indigo,
            'orange' => Color::Orange,
            'pink' => Color::Pink,
            'red' => Color::Red,
            'rose' => Color::Rose,
            'sky' => Color::Sky,
            'teal' => Color::Teal,
            'violet' => Color::Violet,
            'yellow' => Color::Yellow,
            default => Color::Amber,
        };
    }
}
