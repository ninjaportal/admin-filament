<?php

namespace NinjaPortal\Admin\Resources\Settings\Pages;

use NinjaPortal\Admin\Resources\Pages\CreateRecordUsingService;
use NinjaPortal\Admin\Resources\Settings\SettingResource;

class CreateSetting extends CreateRecordUsingService
{
    protected static string $resource = SettingResource::class;

    public function mount(): void
    {
        parent::mount();

        $settingGroupId = request()->integer('setting_group_id');

        if (! $settingGroupId) {
            return;
        }

        $this->form->fill([
            ...($this->data ?? []),
            'setting_group_id' => $settingGroupId,
        ]);
    }
}
