<?php

namespace NinjaPortal\Admin\Resources\AudienceResource\Pages;

use Filament\Actions\DeleteAction;
use NinjaPortal\Admin\Concerns\Resources\Pages\EditRecordWithService;
use NinjaPortal\Admin\Resources\AudienceResource;

class EditAudience extends EditRecordWithService
{

    protected static string $resource = AudienceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
