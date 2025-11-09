<?php

namespace NinjaPortal\Admin\Resources\Menu\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use NinjaPortal\Admin\Resources\Menu\MenuResource;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
