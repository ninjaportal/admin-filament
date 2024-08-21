<?php

namespace NinjaPortal\Admin\Resources\ApiProductResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use NinjaPortal\Admin\Resources\ApiProductResource;
use NinjaPortal\FilamentTranslations\Actions\LocaleSwitcher;

class ListApiProducts extends ListRecords
{
    protected static string $resource = ApiProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            LocaleSwitcher::make(),

        ];
    }
}
