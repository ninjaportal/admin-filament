<?php

namespace NinjaPortal\Admin\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('portal-admin::portal-admin.pages.dashboard');
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return config('portal-admin.panel.icons.dashboard') ?: parent::getNavigationIcon();
    }
}
