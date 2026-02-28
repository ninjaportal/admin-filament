@php
    /** @var array<string, \Filament\Schemas\Components\Tabs\Tab> $tabs */
@endphp

<x-filament::tabs contained="false">
    @foreach ($tabs as $tabKey => $tab)
        <x-filament::tabs.item
            :active="$activeTab === (string) $tabKey"
            :badge="$tab->getBadge()"
            :badge-color="$tab->getBadgeColor()"
            :badge-icon="$tab->getBadgeIcon()"
            :badge-icon-position="$tab->getBadgeIconPosition()"
            :badge-tooltip="$tab->getBadgeTooltip()"
            :href="$tabUrls[(string) $tabKey] ?? null"
            :icon="$tab->getIcon()"
            :icon-position="$tab->getIconPosition()"
            tag="a"
        >
            {{ $tab->getLabel() }}
        </x-filament::tabs.item>
    @endforeach
</x-filament::tabs>
