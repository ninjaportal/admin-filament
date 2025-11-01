<?php

namespace NinjaPortal\Admin\Resources\SettingGroup\Pages;

use Filament\Resources\Pages\CreateRecord;
use NinjaPortal\Admin\Resources\SettingGroup\SettingGroupResource;

class CreateSettingGroup extends CreateRecord
{

    protected static string $resource = SettingGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
