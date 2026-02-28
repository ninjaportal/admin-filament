<?php

namespace NinjaPortal\Admin\Resources\Roles\Pages;

use NinjaPortal\Admin\Resources\Pages\CreateRecordUsingService;
use NinjaPortal\Admin\Resources\Roles\RoleResource;

class CreateRole extends CreateRecordUsingService
{
    protected static string $resource = RoleResource::class;
}
