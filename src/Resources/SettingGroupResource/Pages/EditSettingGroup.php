<?php

namespace NinjaPortal\Admin\Resources\SettingGroupResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use NinjaPortal\Admin\Resources\SettingGroupResource;

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
