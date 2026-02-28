<?php

namespace NinjaPortal\Admin\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use NinjaPortal\Admin\Support\ResourceRegistry;

abstract class PortalResource extends Resource
{
    abstract public static function getResourceKey(): string;

    abstract public static function service(): mixed;

    public static function mutateFormDataBeforeFill(array $data, ?Model $record = null): array
    {
        return $data;
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    public static function mutateFormDataBeforeUpdate(Model $record, array $data): array
    {
        return $data;
    }

    public static function createUsingService(array $data): Model
    {
        /** @var Model $record */
        $record = static::service()->create(static::mutateFormDataBeforeCreate($data));

        return $record;
    }

    public static function updateUsingService(Model $record, array $data): Model
    {
        /** @var Model $updated */
        $updated = static::service()->update($record, static::mutateFormDataBeforeUpdate($record, $data));

        return $updated;
    }

    public static function deleteUsingService(Model $record): void
    {
        static::service()->delete($record->getKey());
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return config('portal-admin.panel.icons.'.static::getResourceKey());
    }

    /**
     * @param  class-string  $default
     * @return class-string
     */
    protected static function page(string $pageKey, string $default): string
    {
        return app(ResourceRegistry::class)->resourcePage(static::getResourceKey(), $pageKey, $default);
    }
}
