<?php

namespace NinjaPortal\Admin\Resources\ApiProducts\Pages;

use NinjaPortal\Admin\Resources\ApiProducts\ApiProductResource;
use NinjaPortal\Admin\Resources\Pages\EditRecordUsingService;

class EditApiProduct extends EditRecordUsingService
{
    protected static string $resource = ApiProductResource::class;
}
