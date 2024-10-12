<?php

namespace NinjaPortal\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NinjaPortal\Portal\Models\User;

class OverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('ninjaadmin::ninjaadmin.active_users'),
                User::where('status', User::$ACTIVE_STATUS)->count()
            )->color('success')->icon('heroicon-o-user-group'),

            Stat::make(__('ninjaadmin::ninjaadmin.inactive_users'),
                User::where('status', User::$INACTIVE_STATUS)->count()
            )->color('danger')->icon('heroicon-o-user-group'),

            Stat::make(__('ninjaadmin::ninjaadmin.pending_users'),
                User::where('status', User::$DEFAULT_STATUS)->count()
            )->color('warning')->icon('heroicon-o-user-group'),

        ];
    }

}
