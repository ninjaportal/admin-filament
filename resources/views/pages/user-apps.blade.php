<x-filament-panels::page>
    <div class="space-y-6">
        @if ($errors->has('error'))
            <x-filament::section>
                <p class="text-sm text-danger-600 dark:text-danger-400">
                    {{ $errors->first('error') }}
                </p>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">{{ __('Applications') }}</x-slot>

            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
