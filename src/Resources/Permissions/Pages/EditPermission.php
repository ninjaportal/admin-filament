<?php

namespace NinjaPortal\Admin\Resources\Permissions\Pages;

use NinjaPortal\Admin\Resources\Pages\EditRecordUsingService;
use NinjaPortal\Admin\Resources\Permissions\PermissionResource;

class EditPermission extends EditRecordUsingService
{
    protected static string $resource = PermissionResource::class;
}
