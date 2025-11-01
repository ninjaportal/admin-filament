<?php

namespace NinjaPortal\Admin\Resources\SettingGroup\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use NinjaPortal\Admin\Resources\SettingGroup\SettingGroupResource;

class EditSettingGroup extends EditRecord
{
    protected static string $resource = SettingGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
