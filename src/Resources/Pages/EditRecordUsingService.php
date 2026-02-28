<?php

namespace NinjaPortal\Admin\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use NinjaPortal\Admin\Resources\PortalResource;

abstract class EditRecordUsingService extends EditRecord
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var class-string<PortalResource> $resource */
        $resource = static::getResource();

        return $resource::mutateFormDataBeforeFill($data, $this->getRecord());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var class-string<PortalResource> $resource */
        $resource = static::getResource();

        return $resource::mutateFormDataBeforeUpdate($this->getRecord(), $data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var class-string<PortalResource> $resource */
        $resource = static::getResource();

        return $resource::updateUsingService($record, $data);
    }
}
