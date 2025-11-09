<?php

namespace NinjaPortal\Admin\Resources\Menu\Pages;

use Filament\Resources\Pages\CreateRecord;
use NinjaPortal\Admin\Resources\Menu\MenuResource;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
