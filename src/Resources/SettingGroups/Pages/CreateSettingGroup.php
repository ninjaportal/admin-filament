<?php

namespace NinjaPortal\Admin\Resources\SettingGroups\Pages;

use NinjaPortal\Admin\Resources\Pages\CreateRecordUsingService;
use NinjaPortal\Admin\Resources\SettingGroups\SettingGroupResource;

class CreateSettingGroup extends CreateRecordUsingService
{
    protected static string $resource = SettingGroupResource::class;
}
