<?php

namespace NinjaPortal\Admin\Support;

class ResourceRegistry
{
    /**
     * @return array<int, class-string>
     */
    public function resources(): array
    {
        return collect(config('portal-admin.resources', []))
            ->pluck('resource')
            ->filter(fn ($class) => is_string($class) && class_exists($class))
            ->values()
            ->all();
    }

    /**
     * @return array<int, class-string>
     */
    public function pages(): array
    {
        return collect(config('portal-admin.pages', []))
            ->filter(fn ($class) => is_string($class) && class_exists($class))
            ->values()
            ->all();
    }

    /**
     * @return array<int, class-string>
     */
    public function widgets(): array
    {
        return collect(config('portal-admin.widgets', []))
            ->filter(fn ($class) => is_string($class) && class_exists($class))
            ->values()
            ->all();
    }

    /**
     * @param  class-string  $default
     * @return class-string
     */
    public function resourcePage(string $resourceKey, string $pageKey, string $default): string
    {
        $configured = config("portal-admin.resources.{$resourceKey}.pages.{$pageKey}");

        if (is_string($configured) && class_exists($configured)) {
            return $configured;
        }

        return $default;
    }
}
