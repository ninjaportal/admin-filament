<?php

namespace NinjaPortal\Admin\Concerns\Resources\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRecordWithService extends CreateRecord
{

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
