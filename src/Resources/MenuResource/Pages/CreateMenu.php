<?php

namespace NinjaPortal\Admin\Resources\MenuResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use NinjaPortal\Admin\Resources\MenuResource;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
