<?php

namespace NinjaPortal\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\TableWidget;
use NinjaPortal\Admin\Widgets\OverviewWidget;
use NinjaPortal\Admin\Widgets\UsersWidgetTable;

class Dashboard extends  BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            OverviewWidget::make(),
            UsersWidgetTable::make()
        ];
    }


}
