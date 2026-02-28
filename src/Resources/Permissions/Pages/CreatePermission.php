<?php

namespace NinjaPortal\Admin\Resources\Permissions\Pages;

use NinjaPortal\Admin\Resources\Pages\CreateRecordUsingService;
use NinjaPortal\Admin\Resources\Permissions\PermissionResource;

class CreatePermission extends CreateRecordUsingService
{
    protected static string $resource = PermissionResource::class;
}
