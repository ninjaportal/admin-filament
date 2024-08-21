<?php

namespace NinjaPortal\Admin\Resources\UserResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use NinjaPortal\Admin\Concerns\Resources\Pages\EditRecordWithService;
use NinjaPortal\Admin\Resources\UserResource;

class EditUser extends EditRecordWithService
{

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
