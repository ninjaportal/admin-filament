<?php

namespace NinjaPortal\Admin\Concerns\Resources\Pages;

use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use NinjaPortal\FilamentTranslations\Resources\Pages\CreateRecord\Concerns\Translatable;
use NinjaPortal\Portal\Translatable\Locales;

class CreateRecordWithService extends CreateRecord
{

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
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
