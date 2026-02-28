<?php

namespace NinjaPortal\Admin\Resources\Categories\Pages;

use NinjaPortal\Admin\Resources\Categories\CategoryResource;
use NinjaPortal\Admin\Resources\Pages\CreateRecordUsingService;

class CreateCategory extends CreateRecordUsingService
{
    protected static string $resource = CategoryResource::class;
}
