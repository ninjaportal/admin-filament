<?php

namespace NinjaPortal\Admin\Resources\ApiProduct\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use NinjaPortal\Admin\Resources\ApiProduct\ApiProductResource;
use NinjaPortal\FilamentTranslations\Actions\LocaleSwitcher;
use NinjaPortal\FilamentTranslations\Resources\Pages\ListRecords\Concerns\Translatable;

class ListApiProducts extends ListRecords
{
    use Translatable;

    protected static string $resource = ApiProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            LocaleSwitcher::make(),
        ];
    }
}
