<?php

namespace NinjaPortal\Admin\Resources\Admin\Pages;

use Filament\Resources\Pages\CreateRecord;
use NinjaPortal\Admin\Resources\Admin\AdminResource;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
