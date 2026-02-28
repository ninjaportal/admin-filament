<?php

namespace NinjaPortal\Admin\Resources\Users\Pages;

use NinjaPortal\Admin\Resources\Pages\EditRecordUsingService;
use NinjaPortal\Admin\Resources\Users\UserResource;

class EditUser extends EditRecordUsingService
{
    protected static string $resource = UserResource::class;
}
