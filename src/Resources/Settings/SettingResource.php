<?php

namespace NinjaPortal\Admin\Resources\Settings;

use Filament\Resources\Pages\PageRegistration;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Admin\Resources\Settings\Pages\CreateSetting;
use NinjaPortal\Admin\Resources\Settings\Pages\EditSetting;
use NinjaPortal\Admin\Resources\Settings\Pages\ListSettings;
use NinjaPortal\Admin\Resources\Settings\Schemas\SettingForm;
use NinjaPortal\Admin\Resources\Settings\Tables\SettingsTable;
use NinjaPortal\Admin\Support\Settings\SettingUi;
use NinjaPortal\Portal\Contracts\Services\SettingServiceInterface;
use NinjaPortal\Portal\Models\Setting;
use NinjaPortal\Portal\Utils;

class SettingResource extends PortalResource
{
    public static function getModel(): string
    {
        return Utils::getSettingModel() ?: Setting::class;
    }

    public static function getResourceKey(): string
    {
        return 'settings';
    }

    public static function service(): SettingServiceInterface
    {
        return app(SettingServiceInterface::class);
    }

    public static function form(Schema $schema): Schema
    {
        return SettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SettingsTable::configure($table, static::class);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => static::page('index', ListSettings::class)::route('/'),
            'create' => static::page('create', CreateSetting::class)::route('/create'),
            'edit' => static::page('edit', EditSetting::class)::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return (string) config('portal-admin.panel.navigation.system', __('portal-admin::portal-admin.navigation.system'));
    }

    public static function mutateFormDataBeforeFill(array $data, ?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        return SettingUi::prepareForForm($data);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return SettingUi::prepareForStorage($data);
    }

    public static function mutateFormDataBeforeUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): array
    {
        return SettingUi::prepareForStorage($data);
    }
}
