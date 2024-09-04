<?php

namespace NinjaPortal\Admin\Resources\SettingGroupResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use NinjaPortal\Admin\Resources\SettingGroupResource;

class CreateSettingGroup extends CreateRecord
{

    protected static string $resource = SettingGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
