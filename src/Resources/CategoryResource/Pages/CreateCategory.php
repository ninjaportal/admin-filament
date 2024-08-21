<?php

namespace NinjaPortal\Admin\Resources\CategoryResource\Pages;

use NinjaPortal\Admin\Concerns\Resources\Pages\CreateRecordWithService;
use NinjaPortal\Admin\Resources\CategoryResource;
use NinjaPortal\FilamentTranslations\Actions\LocaleSwitcher;
use NinjaPortal\FilamentTranslations\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateCategory extends CreateRecordWithService
{
    use Translatable;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }


}
