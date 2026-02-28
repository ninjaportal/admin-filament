<?php

namespace NinjaPortal\Admin\Resources\Users\Pages;

use NinjaPortal\Admin\Resources\Pages\CreateRecordUsingService;
use NinjaPortal\Admin\Resources\Users\UserResource;

class CreateUser extends CreateRecordUsingService
{
    protected static string $resource = UserResource::class;
}
