<?php

namespace NinjaPortal\Admin\Concerns\Resources\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRecordWithService extends CreateRecord
{

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (method_exists(static::getResource(), 'mutateFormDataBeforeCreate')) {
            $data = static::getResource()::mutateFormDataBeforeCreate($data);
        }
        return parent::mutateFormDataBeforeCreate($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (method_exists($this, 'beforeSaveRecord')) {
            return $this->mutateFormDataBeforeSave($data);
        }

        if (method_exists(static::getResource(), 'mutateFormDataBeforeSave')) {
            return static::getResource()::mutateFormDataBeforeSave($data);
        }

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (method_exists($this, 'beforeFillForm')) {
            return $this->beforeFillForm($data);
        }

        if (method_exists(static::getResource(), 'mutateFormDataBeforeFill')) {
            return static::getResource()::mutateFormDataBeforeFill($data);
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = self::getResource()::service()->create($data);

        if (
            static::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }


        return $record;

    }
}
