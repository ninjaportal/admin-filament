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
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return self::getResource()::service()->update($record, $data);
    }
}
