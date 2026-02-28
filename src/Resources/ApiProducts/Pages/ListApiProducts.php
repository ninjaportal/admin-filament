<?php

namespace NinjaPortal\Admin\Resources\ApiProducts\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use NinjaPortal\Admin\Resources\ApiProducts\ApiProductResource;

class ListApiProducts extends ListRecords
{
    protected static string $resource = ApiProductResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
