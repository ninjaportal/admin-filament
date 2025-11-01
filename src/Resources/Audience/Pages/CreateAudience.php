<?php

namespace NinjaPortal\Admin\Resources\Audience\Pages;

use NinjaPortal\Admin\Concerns\Resources\Pages\CreateRecordWithService;
use NinjaPortal\Admin\Resources\Audience\AudienceResource;

class CreateAudience extends CreateRecordWithService
{


    protected static string $resource = AudienceResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

}
