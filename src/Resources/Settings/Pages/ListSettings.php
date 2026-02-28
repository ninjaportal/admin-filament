<?php

namespace NinjaPortal\Admin\Resources\Settings\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use NinjaPortal\Admin\Resources\SettingGroups\SettingGroupResource;
use NinjaPortal\Admin\Resources\Settings\SettingResource;
use NinjaPortal\Admin\Support\Settings\SettingUi;
use NinjaPortal\Portal\Models\SettingGroup;
use NinjaPortal\Portal\Utils;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manageGroups')
                ->label(__('Manage groups'))
                ->icon('heroicon-o-folder-open')
                ->color('gray')
                ->url(SettingGroupResource::getUrl('index')),
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $settingModel = SettingResource::getModel();
        $groupModel = Utils::getSettingGroupModel() ?: SettingGroup::class;

        $tabs = [
            'all' => Tab::make(__('All settings'))
                ->icon('heroicon-o-cog-6-tooth')
                ->badge($settingModel::query()->count()),
        ];

        $groupModel::query()
            ->withCount('settings')
            ->orderBy('name')
            ->get()
            ->each(function ($group) use (&$tabs): void {
                $tabs[SettingUi::groupTabKey($group->getKey())] = Tab::make($group->name)
                    ->icon('heroicon-o-folder')
                    ->badge($group->settings_count)
                    ->modifyQueryUsing(fn ($query) => $query->where('setting_group_id', $group->getKey()));
            });

        $ungroupedCount = $settingModel::query()->whereNull('setting_group_id')->count();

        if ($ungroupedCount > 0) {
            $tabs['ungrouped'] = Tab::make(__('Ungrouped'))
                ->icon('heroicon-o-inbox-stack')
                ->badge($ungroupedCount)
                ->modifyQueryUsing(fn ($query) => $query->whereNull('setting_group_id'));
        }

        return $tabs;
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->flushCachedTableRecords();
        $this->cachedDefaultTableColumnState = null;

        $this->applyTableColumnManager();
    }

    public function getTabsContentComponent(): Component
    {
        return View::make('portal-admin::components.settings-tabs')
            ->viewData([
                'activeTab' => (string) ($this->activeTab ?? $this->getDefaultActiveTab()),
                'tabs' => $this->getCachedTabs(),
                'tabUrls' => $this->getTabUrls(),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function getTabUrls(): array
    {
        $baseUrl = static::getResource()::getUrl('index');
        $query = request()->query();

        unset($query['page'], $query['tab']);

        $urls = [];

        foreach (array_keys($this->getCachedTabs()) as $tabKey) {
            $urls[(string) $tabKey] = filled($tabKey)
                ? $baseUrl.'?'.http_build_query([...$query, 'tab' => $tabKey])
                : $baseUrl.(empty($query) ? '' : '?'.http_build_query($query));
        }

        return $urls;
    }
}
