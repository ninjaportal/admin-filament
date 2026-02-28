<?php

namespace NinjaPortal\Admin\Tests\Feature;

use Filament\Facades\Filament;
use NinjaPortal\Admin\Resources\SettingGroups\SettingGroupResource;
use NinjaPortal\Admin\Tests\TestCase;
use NinjaPortal\Admin\Widgets\PortalOverviewStatsWidget;

class PanelBootTest extends TestCase
{
    public function test_portal_admin_panel_registers_core_resources(): void
    {
        $panel = Filament::getPanel('portal-admin');

        $this->assertNotNull($panel);
        $this->assertTrue($panel->isDefault());
        $this->assertContains(SettingGroupResource::class, $panel->getResources());
        $this->assertContains(PortalOverviewStatsWidget::class, $panel->getWidgets());
    }

    public function test_panel_guard_is_separate_from_the_shared_admin_rbac_guard(): void
    {
        $this->assertNotSame(
            config('portal-admin.panel.guard'),
            config('portal-admin.panel.rbac_guard'),
        );

        $this->assertSame(
            config('portal-api.auth.guards.admin'),
            config('portal-admin.panel.rbac_guard'),
        );
    }
}
