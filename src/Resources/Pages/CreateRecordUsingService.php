<?php

namespace NinjaPortal\Admin\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use NinjaPortal\Admin\Resources\PortalResource;

abstract class CreateRecordUsingService extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var class-string<PortalResource> $resource */
        $resource = static::getResource();

        return $resource::mutateFormDataBeforeCreate($data);
    }

    protected function handleRecordCreation(array $data): Model
    {
        /** @var class-string<PortalResource> $resource */
        $resource = static::getResource();

        return $resource::createUsingService($data);
    }
}
