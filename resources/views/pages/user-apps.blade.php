@php
    $columns = [
        'name' => __('Name'),
        'displayName' => __('Display Name'),
        'description' => __('Description'),
        'status' => __('Status'),
        'createdAt' => __('Created At'),
    ];
@endphp
<x-filament-panels::page>
    <x-filament::section>
        <div class="overflow-x-auto">
            <table class="w-full fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell">
                <thead class="divide-y divide-gray-200 dark:divide-white/5 text-left">
                <tr class="bg-gray-50 dark:bg-white/5">
                    @foreach($columns as $key => $column)
                        <th class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1">
                            <div class="px-3 py-2">
                                {{ $column }}
                            </div>
                        </th>
                    @endforeach
                    <th class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-ta-selection-cell w-1">
                        <div class="px-3 py-2">
                            @lang('Actions')
                        </div>
                    </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                @forelse($this->apps as $app)
                    <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                        @foreach($columns as $key => $column)
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-first-name">
                                <div class="px-3 py-2">
                                    {{ $app[$key] }}
                                </div>
                            </td>
                        @endforeach
                            <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-first-name">
                                <div class="px-3 py-2">
                                    {{ ($this->manageAppAction)(["app"=>$app]) }}
                                </div>
                            </td>

                    </tr>
                @empty
                    <tr class="fi-ta-row">
                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 fi-table-cell-first-name" colspan="{{ count($columns) + 1 }}">
                            <div class="px-3 py-4 text-center">
                                @lang('No apps found.')
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-panels::page>
