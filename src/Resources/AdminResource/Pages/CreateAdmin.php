<?php

namespace NinjaPortal\Admin\Resources\AdminResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use NinjaPortal\Admin\Resources\AdminResource;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
