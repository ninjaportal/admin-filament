<?php

namespace NinjaPortal\Admin\Resources\CategoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use NinjaPortal\Admin\Resources\CategoryResource;
use NinjaPortal\FilamentTranslations\Resources\Pages\ListRecords\Concerns\Translatable;
use NinjaPortal\FilamentTranslations\Actions\LocaleSwitcher;

class ListCategories extends ListRecords
{
    use Translatable;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            LocaleSwitcher::make(),
        ];
    }
}
