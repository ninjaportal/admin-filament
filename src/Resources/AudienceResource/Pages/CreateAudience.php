<?php

namespace NinjaPortal\Admin\Resources\AudienceResource\Pages;

use NinjaPortal\Admin\Concerns\Resources\Pages\CreateRecordWithService;
use NinjaPortal\Admin\Resources\AudienceResource;

class CreateAudience extends CreateRecordWithService
{


    protected static string $resource = AudienceResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

}
