<?php

namespace NinjaPortal\Admin\Resources\SettingGroups\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use NinjaPortal\Admin\Resources\SettingGroups\SettingGroupResource;
use NinjaPortal\Admin\Resources\Settings\SettingResource;

class ListSettingGroups extends ListRecords
{
    protected static string $resource = SettingGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openSettings')
                ->label(__('Open settings'))
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->url(SettingResource::getUrl('index')),
            CreateAction::make(),
        ];
    }
}
