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
        Resources\SettingGroupResource::class
    ];

    protected array $widgets = [
        Widgets\OverviewWidget::class,
        Widgets\UsersWidgetTable::class,
    ];

    protected array $pages = [
        Pages\MenuManager::class,
        Pages\Dashboard::class,
    ];

    public function getId(): string
    {
        return 'ninjaadmin';
    }

    public function register(Panel $panel): void
    {
        $panel->widgets($this->widgets)
            ->pages($this->pages)
            ->plugins([
                FilamentShieldPlugin::make(),
                NinjaFilamentTranslatablePlugin::make()
                    ->defaultLocales(['en', 'ar']),
            ])->navigationGroups($this->getNavigationGroups());
        $this->registerResources($panel);
    }

    public function boot(Panel $panel): void
    {
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
                ->label(fn () :string => __("ninjaadmin::ninjaadmin.navigation_groups.$value"));
        }

        FilamentShieldPlugin::setNavigationGroup('ninjaadmin::ninjaadmin.navigation_groups.admin');
        return $groups;
    }

    protected function registerResources(Panel $panel): void
    {
        $resources = $this->resources;
        $appNamespace = app()->getNamespace();
        $namespace = __NAMESPACE__ . '\Resources';
        $registeredResources = $panel->getResources();
        $unregisteredResources = $this->filterUnregistered($resources, $registeredResources, $namespace, $appNamespace);
        $panel->resources($unregisteredResources);
    }

    /**
     * Filters unregistered items by comparing transformed names.
     *
     * @param array $items Items to register.
     * @param array $registeredItems Items already registered.
     * @param string $namespace The namespace to replace in items.
     * @param string $appNamespace The namespace to replace in registered items.
     * @return array The filtered list of items to register.
     */
    protected function filterUnregistered(array $items, array $registeredItems, string $namespace, string $appNamespace): array
    {
        return array_filter($items, function ($item) use ($registeredItems, $namespace, $appNamespace) {
            $itemName = str($item)->replace($namespace, '')->toString();
            $check = array_filter($registeredItems, function ($registeredItem) use ($itemName, $appNamespace) {
                return str($registeredItem)->replace($appNamespace, '')->toString() === $itemName;
            });
            return empty($check);
        });
    }


}
