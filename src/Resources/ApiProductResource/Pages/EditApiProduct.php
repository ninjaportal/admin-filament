<?php

namespace NinjaPortal\Admin\Resources\ApiProductResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use NinjaPortal\Admin\Concerns\Resources\Pages\EditRecordWithService;
use NinjaPortal\Admin\Resources\ApiProductResource;
use NinjaPortal\FilamentTranslations\Actions\LocaleSwitcher;
use NinjaPortal\FilamentTranslations\Resources\Pages\EditRecord\Concerns\Translatable;

class EditApiProduct extends EditRecordWithService
{

    use Translatable;

    protected static string $resource = ApiProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            LocaleSwitcher::make(),
        ];
    }
}
