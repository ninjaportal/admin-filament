<x-filament-panels::page>
    <x-filament::grid :default="3" class="gap-x-5">
        <x-filament::grid.column :default="1">
            <x-filament::section>
                <form wire:submit="addLink">
                    {{ $this->linksForm }}
                    <div class="fi-form-actions mt-6">
                        <x-filament::button
                            type="submit"
                            class="mt-4 w-full">
                            Add Link
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </x-filament::grid.column>
        <x-filament::grid.column :default="2">
            {{ $this->menuForm }}
            @if($this->menuData['currentMenu'])
            <x-filament::section
                class="mt-8">
                <div
                    x-load-js="['https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js']"
                    x-init="
                        const el = $refs.menuItems;
                        const sortable = new Sortable(el, {
                           animation: 150,
                           onEnd: (event) => {
                                const item = event.item;
                                const key = item.getAttribute('key');
                                const newIndex = event.newIndex;
                                const oldIndex = event.oldIndex;
                                const items = Array.from(el.children).map((item) => item.getAttribute('key'));
                                @this.reorderMenuItems(items);
                           },
                        });
                    "
                >
                    <table class="table w-full">
                        <tbody
                            x-ref="menuItems"
                            class="divide-y divide-gray-200 dark:divide-white/5">
                        @forelse ($this->menuData['content'] as $menu)
                            <tr
                                class="bg-gray-50 dark:bg-white/5"
                                key="{{ $menu['slug'] }}">
                                <td class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-name">
                                    {{ $menu['name'] }}
                                </td>
                                <td>
                                    <div class="flex">
                                        <x-filament::icon-button
                                            icon="heroicon-o-pencil"
                                            wire:click="editMenuItem('{{ $menu['slug'] ?? '' }}')"
                                            class="w-full">
                                        </x-filament::icon-button>
                                        <x-filament::icon-button
                                            icon="heroicon-o-trash"
                                            color="danger"
                                            wire:click="deleteMenuItem('{{ $menu['slug'] }}')"
                                            class="w-full">
                                        </x-filament::icon-button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                    No menu items found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <x-filament::button
                    wire:click="saveMenu"
                    class="mt-8 w-full">
                    Save Menu
                </x-filament::button>
            </x-filament::section>
            @endif
        </x-filament::grid.column>
    </x-filament::grid>
</x-filament-panels::page>
