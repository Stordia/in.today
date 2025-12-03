<x-filament-panels::page>
    @if(! $this->hasRestaurant())
        <div class="flex flex-col items-center justify-center py-12">
            <x-heroicon-o-building-storefront class="h-16 w-16 text-gray-400 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Restaurant Selected</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Please select a restaurant to configure booking settings.</p>
            <x-filament::button
                :href="route('filament.business.pages.switch-restaurant')"
                tag="a"
                icon="heroicon-o-building-storefront"
            >
                Select Restaurant
            </x-filament::button>
        </div>
    @else
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6 flex gap-3">
                <x-filament::button type="submit">
                    Save Settings
                </x-filament::button>
            </div>
        </form>
    @endif
</x-filament-panels::page>
