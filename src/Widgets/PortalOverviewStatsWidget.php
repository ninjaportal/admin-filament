<?php

namespace NinjaPortal\Admin\Widgets;

use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NinjaPortal\Admin\Support\DashboardStats;

class PortalOverviewStatsWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'Portal overview';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $stats = app(DashboardStats::class)->overview();

        return [
            Stat::make(__('Total developers'), number_format((int) $stats['total_developers']))
                ->icon('heroicon-o-user-group')
                ->color('primary'),
            Stat::make(__('Total apps'), number_format((int) $stats['total_apps']))
                ->icon('heroicon-o-key')
                ->description(__('Across synced developers'))
                ->descriptionIcon('heroicon-o-arrow-path', IconPosition::Before)
                ->color('success'),
            Stat::make(__('API products in portal'), number_format((int) $stats['portal_api_products']))
                ->icon('heroicon-o-cube')
                ->description(__('Linked to Apigee: :count', ['count' => number_format((int) $stats['linked_portal_api_products'])]))
                ->descriptionIcon('heroicon-o-link', IconPosition::Before)
                ->color('info'),
            Stat::make(__('API products not linked'), number_format((int) $stats['apigee_unlinked_products']))
                ->icon('heroicon-o-exclamation-circle')
                ->description(
                    (bool) $stats['apigee_products_available']
                        ? __('Total in Apigee: :count', ['count' => number_format((int) $stats['apigee_total_products'])])
                        : __('Unable to load Apigee products right now.')
                )
                ->descriptionIcon(
                    (bool) $stats['apigee_products_available'] ? 'heroicon-o-cloud' : 'heroicon-o-exclamation-triangle',
                    IconPosition::Before,
                )
                ->color((bool) $stats['apigee_products_available'] ? 'warning' : 'danger'),
        ];
    }
}
