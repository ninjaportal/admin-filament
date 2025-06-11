<?php

namespace NinjaPortal\Admin;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use NinjaPortal\FilamentShield\FilamentShieldPlugin;
use NinjaPortal\FilamentTranslations\NinjaFilamentTranslatablePlugin;

class NinjaAdminPlugin implements Plugin
{
    protected array $resources = [
        Resources\AdminResource::class,
        Resources\AudienceResource::class,
        Resources\CategoryResource::class,
        Resources\ApiProductResource::class,
        Resources\UserResource::class,
        Resources\SettingGroupResource::class,
        Resources\MenuResource::class,
    ];

    protected array $widgets = [
        Widgets\OverviewWidget::class,
        Widgets\UsersWidgetTable::class,
    ];

    protected array $pages = [
        Pages\Dashboard::class,
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
                    ->defaultLocales(config('ninjaadmin.locales', ['en'])),
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
        $groups = [];

        foreach (Constants::NAVIGATION_GROUPS as $key => $value) {
            $groups[$key] = NavigationGroup::make()
                ->label(fn(): string => __("ninjaadmin::ninjaadmin.navigation_groups.$value"));
        }

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
