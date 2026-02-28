<?php

namespace NinjaPortal\Admin\Support;

use Closure;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class TranslatableTabs
{
    /**
     * @param  Closure(string): array<int, mixed>  $fields
     */
    public static function make(Closure $fields, ?string $label = null): Tabs
    {
        $tabs = collect(config('ninjaportal.locales', ['en' => 'English']))
            ->map(fn (string $localeLabel, string $locale) => Tab::make($localeLabel)->schema($fields($locale)))
            ->values()
            ->all();

        return Tabs::make($label ?? __('Translations'))->tabs($tabs);
    }
}
