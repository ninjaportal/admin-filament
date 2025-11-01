<?php

namespace NinjaPortal\Admin\Resources\SettingGroup\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use NinjaPortal\Admin\Resources\SettingGroup\SettingGroupResource;

class ListSettingGroups extends ListRecords
{
    protected static string $resource = SettingGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
