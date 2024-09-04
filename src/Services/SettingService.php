<?php

namespace NinjaPortal\Admin\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use LaravelIdea\Helper\NinjaPortal\Admin\Models\_IH_Setting_C;
use LaravelIdea\Helper\NinjaPortal\Admin\Models\_IH_Setting_QB;
use NinjaPortal\Admin\Models\Setting;

class SettingService
{
    protected static function getCacheKey(string $key): string
    {
        return Config::get('c') . ".{$key}";
    }

    protected static function isCachable(): bool
    {
        return Config::get('ninjaadmin.settings.cachable', false);
    }

    protected static function getTtl(): int
    {
        return Config::get('ninjaadmin.settings.ttl', 3600);
    }

    protected static function setConfig(string $key, string $value, string $type): void
    {
        if ($type === 'integer') {
            Config::set($key, (int)$value);
        } elseif ($type === 'boolean') {
            Config::set($key, $value === 'true');
        } else {
            Config::set($key, $value);
        }
    }

    public static function loadAllSettings(): void
    {
        $settings = Setting::all();
        foreach ($settings as $setting) {
            self::setConfig($setting->key, $setting->value, $setting->type);
        }
    }

    public static function get(string $key): ?string
    {
        if (self::isCachable()) {
            $cacheKey = self::getCacheKey($key);
            return Cache::remember($cacheKey, self::getTtl(), function () use ($key) {
                $setting = Setting::where('key', $key);
                return [
                    'value' => $setting->value,
                    'type' => $setting->type
                ];
            });
        }
        return Setting::where('key', $key)->value('value');
    }

    public static function set(string $key, string $value, string $type): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]);
        if (self::isCachable()) {
            Cache::put(self::getCacheKey($key),
                [
                    'value' => $value,
                    'type' => $type
                ]
                , self::getTtl());
        }
    }

    public static function delete(string $key): void
    {
        Setting::where('key', $key)->delete();
        Config::set($key, null);

        if (self::isCachable()) {
            Cache::forget(self::getCacheKey($key));
        }
    }

    public static function all(): Collection|_IH_Setting_C|array
    {
        return Setting::all();
    }

    public static function query(): Builder|_IH_Setting_QB
    {
        return Setting::query();
    }

}
