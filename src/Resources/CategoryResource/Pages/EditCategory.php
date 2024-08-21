<?php

namespace NinjaPortal\Admin\Resources\CategoryResource\Pages;

use Filament\Actions\DeleteAction;
use NinjaPortal\Admin\Concerns\Resources\Pages\EditRecordWithService;
use NinjaPortal\Admin\Resources\CategoryResource;
use NinjaPortal\FilamentTranslations\Actions\LocaleSwitcher;
use NinjaPortal\FilamentTranslations\Resources\Pages\EditRecord\Concerns\Translatable;

class EditCategory extends EditRecordWithService
{
    use Translatable;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            LocaleSwitcher::make(),
        ];
    }
}
