<?php

namespace NinjaPortal\Admin\Resources\Admin\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use NinjaPortal\Admin\Resources\Admin\AdminResource;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
