<?php

namespace NinjaPortal\Admin\Resources\ApiProducts\Pages;

use NinjaPortal\Admin\Resources\ApiProducts\ApiProductResource;
use NinjaPortal\Admin\Resources\Pages\CreateRecordUsingService;

class CreateApiProduct extends CreateRecordUsingService
{
    protected static string $resource = ApiProductResource::class;
}
