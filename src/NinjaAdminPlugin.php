<?php

namespace NinjaPortal\Admin;

use NinjaPortal\Admin\Resources\Audience\AudienceResource;
use NinjaPortal\Admin\Resources\Category\CategoryResource;
use NinjaPortal\Admin\Resources\ApiProduct\ApiProductResource;
use NinjaPortal\Admin\Resources\User\UserResource;
use NinjaPortal\Admin\Resources\SettingGroup\SettingGroupResource;
use NinjaPortal\Admin\Resources\Menu\MenuResource;
use NinjaPortal\Admin\Pages\Dashboard;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use NinjaPortal\AI\AIPlugin;
use NinjaPortal\FilamentShield\FilamentShieldPlugin;
use NinjaPortal\FilamentTranslations\NinjaFilamentTranslatablePlugin;

class NinjaAdminPlugin implements Plugin
{
    protected array $resources = [
        AudienceResource::class,
        CategoryResource::class,
        ApiProductResource::class,
        UserResource::class,
        SettingGroupResource::class,
        MenuResource::class,
    ];

    protected array $widgets = [
        Widgets\OverviewWidget::class,
        Widgets\UsersWidgetTable::class,
    ];

    protected array $pages = [
        Dashboard::class,
    ];

    public function getId(): string
    {
        return 'ninjaadmin';
    }

    public function register(Panel $panel): void
    {
        // Register widgets, pages, resources with extension support
        $this->registerWidgets($panel);
        $this->registerPages($panel);
        $this->registerResources($panel);

        // Core panel setup
        $panel
            ->topNavigation()
            ->plugins([
                FilamentShieldPlugin::make(),
                NinjaFilamentTranslatablePlugin::make()
                    ->defaultLocales(array_keys(config('ninjaportal.locales'))),
            ])
            ->navigationGroups($this->getNavigationGroups());
    }

    public function boot(Panel $panel): void
    {
        // Boot logic if needed
    }

    public static function make(): static
    {
        return app(static::class);
    }

    protected function getNavigationGroups(): array
    {

        $groups = array_map(function ($value) {
            return NavigationGroup::make()
                ->label(fn(): string => __("ninjaadmin::ninjaadmin.navigation_groups.$value"));
        }, Constants::NAVIGATION_GROUPS);
        FilamentShieldPlugin::setNavigationGroup('ninjaadmin::ninjaadmin.navigation_groups.admin');
        return $groups;
    }

    protected function registerResources(Panel $panel): void
    {
        $namespace = __NAMESPACE__ . '\Resources';
        $registered = $panel->getResources();
        $toRegister = $this->filterUnregistered($this->resources, $registered, $namespace, app()->getNamespace());
        if (! empty($toRegister)) {
            $panel->resources($toRegister);
        }
    }

    protected function registerPages(Panel $panel): void
    {
        $namespace = __NAMESPACE__ . '\Pages';
        $registered = $panel->getPages();
        $toRegister = $this->filterUnregistered($this->pages, $registered, $namespace, app()->getNamespace());

        if (! empty($toRegister)) {
            $panel->pages($toRegister);
        }
    }

    protected function registerWidgets(Panel $panel): void
    {
        $namespace = __NAMESPACE__ . '\Widgets';
        $registered = $panel->getWidgets();
        $toRegister = $this->filterUnregistered($this->widgets, $registered, $namespace, app()->getNamespace());

        if (! empty($toRegister)) {
            $panel->widgets($toRegister);
        }
    }

    /**
     * Filters unregistered items by comparing class names without base namespaces.
     *
     * @param string[] $items
     * @param string[] $registeredItems
     * @param string   $itemNamespace
     * @param string   $appNamespace
     * @return string[]
     */
    protected function filterUnregistered(array $items, array $registeredItems, string $itemNamespace, string $appNamespace): array
    {
        return array_filter($items, function ($item) use ($registeredItems, $itemNamespace, $appNamespace) {
            if (class_exists($item) === false) {
                return false;
            }
            $itemName = str($item)
                ->replace($itemNamespace, '')
                ->toString();

            $found = array_filter($registeredItems, function ($registered) use ($itemName, $appNamespace) {
                return str($registered)
                    ->replace($appNamespace, '')
                    ->endsWith($itemName);
            });

            return empty($found);
        });
    }
}
