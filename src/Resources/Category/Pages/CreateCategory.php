<?php

namespace NinjaPortal\Admin\Resources\Category\Pages;

use NinjaPortal\Admin\Concerns\Resources\Pages\CreateRecordWithService;
use NinjaPortal\Admin\Resources\Category\CategoryResource;
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
