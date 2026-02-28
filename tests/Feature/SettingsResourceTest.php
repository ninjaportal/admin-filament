<?php

namespace NinjaPortal\Admin\Tests\Feature;

use Livewire\Livewire;
use Mockery;
use NinjaPortal\Admin\Resources\SettingGroups\Pages\ListSettingGroups;
use NinjaPortal\Admin\Resources\Settings\Pages\CreateSetting;
use NinjaPortal\Admin\Resources\Settings\Pages\EditSetting;
use NinjaPortal\Admin\Resources\Settings\Pages\ListSettings;
use NinjaPortal\Admin\Resources\Settings\SettingResource;
use NinjaPortal\Admin\Support\Settings\SettingUi;
use NinjaPortal\Admin\Tests\TestCase;
use NinjaPortal\Portal\Contracts\Services\SettingServiceInterface;
use NinjaPortal\Portal\Models\Setting;
use NinjaPortal\Portal\Models\SettingGroup;

class SettingsResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_create_setting_page_writes_through_the_setting_service(): void
    {
        $group = SettingGroup::query()->create([
            'name' => 'Feature Flags',
        ]);

        $service = Mockery::mock(SettingServiceInterface::class);
        $service->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) use ($group): bool {
                return $data['key'] === 'features.show_banner'
                    && $data['label'] === 'Show demo banner'
                    && $data['type'] === 'string'
                    && $data['value'] === 'enabled'
                    && $data['setting_group_id'] === $group->getKey();
            }))
            ->andReturnUsing(fn (array $data): Setting => Setting::query()->create($data));

        $this->app->instance(SettingServiceInterface::class, $service);

        $record = SettingResource::createUsingService([
            'key' => 'features.show_banner',
            'label' => 'Show demo banner',
            'type' => 'string',
            'value_string' => 'enabled',
            'setting_group_id' => $group->getKey(),
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'features.show_banner',
            'type' => 'string',
            'value' => 'enabled',
        ]);
        $this->assertInstanceOf(Setting::class, $record);
    }

    public function test_setting_ui_normalizes_boolean_values_for_storage(): void
    {
        $prepared = SettingUi::prepareForStorage([
            'type' => 'boolean',
            'value_boolean' => true,
        ]);

        $this->assertSame('boolean', $prepared['type']);
        $this->assertSame('1', $prepared['value']);
        $this->assertArrayNotHasKey('value_boolean', $prepared);
    }

    public function test_create_setting_page_prefills_the_group_from_the_query_string(): void
    {
        $group = SettingGroup::query()->create([
            'name' => 'Branding',
        ]);

        Livewire::withQueryParams([
            'setting_group_id' => $group->getKey(),
        ])->test(CreateSetting::class)
            ->assertSet('data.setting_group_id', $group->getKey());
    }

    public function test_edit_setting_page_prefills_the_json_editor_for_existing_json_settings(): void
    {
        $setting = Setting::query()->create([
            'key' => 'branding.theme_map',
            'label' => 'Theme map',
            'type' => 'json',
            'value' => json_encode([
                'primary' => '#111827',
                'secondary' => '#0EA5E9',
            ], JSON_UNESCAPED_SLASHES),
        ]);

        $prepared = SettingUi::prepareForForm($setting->attributesToArray());

        Livewire::test(EditSetting::class, [
            'record' => $setting->getKey(),
        ])
            ->assertSet('data.type', 'json')
            ->assertSet('data.value_json', $prepared['value_json']);
    }

    public function test_settings_and_setting_groups_pages_render_the_new_group_first_navigation(): void
    {
        $group = SettingGroup::query()->create([
            'name' => 'Branding',
        ]);

        Setting::query()->create([
            'key' => 'branding.primary_color',
            'label' => 'Primary color',
            'type' => 'string',
            'value' => '#111827',
            'setting_group_id' => $group->getKey(),
        ]);

        Setting::query()->create([
            'key' => 'portal.tagline',
            'label' => 'Portal tagline',
            'type' => 'string',
            'value' => 'Launch faster',
        ]);

        Livewire::test(ListSettings::class)
            ->assertSee('Manage groups')
            ->assertSee('All settings')
            ->assertSee('Branding')
            ->assertSee('Ungrouped');

        Livewire::test(ListSettingGroups::class)
            ->assertSee('Open settings')
            ->assertSee('View settings')
            ->assertSee('Add setting');
    }

    public function test_settings_tabs_scope_the_table_records_by_group(): void
    {
        $branding = SettingGroup::query()->create([
            'name' => 'Branding',
        ]);

        $portal = SettingGroup::query()->create([
            'name' => 'Portal',
        ]);

        $brandingSetting = Setting::query()->create([
            'key' => 'branding.primary_color',
            'label' => 'Primary color',
            'type' => 'string',
            'value' => '#111827',
            'setting_group_id' => $branding->getKey(),
        ]);

        $portalSetting = Setting::query()->create([
            'key' => 'portal.name',
            'label' => 'Portal name',
            'type' => 'string',
            'value' => 'NinjaPortal',
            'setting_group_id' => $portal->getKey(),
        ]);

        Livewire::test(ListSettings::class)
            ->set('activeTab', SettingUi::groupTabKey($branding->getKey()))
            ->assertCanSeeTableRecords([$brandingSetting])
            ->assertCanNotSeeTableRecords([$portalSetting]);
    }
}
