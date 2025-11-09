<?php

namespace NinjaPortal\Admin\Concerns\Resources\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use NinjaPortal\FilamentTranslations\Resources\Pages\CreateRecord\Concerns\Translatable;
use NinjaPortal\Portal\Translatable\Locales;

class EditRecordWithService extends EditRecord
{
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (method_exists(static::getResource(), 'mutateFormDataBeforeSave')) {
            $data = static::getResource()::mutateFormDataBeforeSave($data);
        }

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (method_exists(static::getResource(), 'mutateFormDataBeforeFill')) {
            $data = static::getResource()::mutateFormDataBeforeFill($data);
        }

        return parent::mutateFormDataBeforeFill($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return self::getResource()::service()->update($record, $data);
    }
}
