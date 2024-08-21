<?php

namespace NinjaPortal\Admin\Resources\ApiProductResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use NinjaPortal\Admin\Concerns\Resources\Pages\CreateRecordWithService;
use NinjaPortal\Admin\Resources\ApiProductResource;
use NinjaPortal\FilamentTranslations\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateApiProduct extends CreateRecordWithService
{
    use Translatable;

    protected static string $resource = ApiProductResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
