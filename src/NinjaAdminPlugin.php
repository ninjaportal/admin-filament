<?php

namespace NinjaPortal\Admin;

use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use NinjaPortal\Admin\Pages\MenuManager;
use NinjaPortal\Admin\Resources\AdminResource;
use NinjaPortal\Admin\Resources\ApiProductResource;
use NinjaPortal\Admin\Resources\AudienceResource;
use NinjaPortal\Admin\Resources\CategoryResource;
use NinjaPortal\Admin\Resources\UserResource;
use NinjaPortal\FilamentTranslations\NinjaFilamentTranslatablePlugin;

class NinjaAdminPlugin implements Plugin
{

    protected array $resources = [
        AdminResource::class,
        AudienceResource::class,
        CategoryResource::class,
        ApiProductResource::class,
        UserResource::class
    ];

    protected array $widgets = [];

    protected array $pages = [
        MenuManager::class,
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


    protected function getNavigationGroups()
    {
        $groups = [];
        foreach (Constants::NAVIGATION_GROUPS as $key => $value) {
            $groups[$key] = NavigationGroup::make($key)
                ->label(__($value));
        }
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
