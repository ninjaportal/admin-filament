<?php

namespace NinjaPortal\Admin\Resources\Audience\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use NinjaPortal\Admin\Resources\Audience\AudienceResource;

class ListAudiences extends ListRecords
{
    protected static string $resource = AudienceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
