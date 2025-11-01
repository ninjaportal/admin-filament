<?php

namespace NinjaPortal\Admin\Resources\User\Pages;

use Filament\Resources\Pages\CreateRecord;
use NinjaPortal\Admin\Concerns\Resources\Pages\CreateRecordWithService;
use NinjaPortal\Admin\Resources\User\UserResource;

class CreateUser extends CreateRecordWithService
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
