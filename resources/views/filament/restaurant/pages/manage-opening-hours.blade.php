<x-filament-panels::page>
    @if ($this->hasRestaurant())
        <form wire:submit="save">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" size="lg">
                    Save Changes
                </x-filament::button>
            </div>
        </form>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">No restaurant selected. Please select a restaurant to manage opening hours.</p>
        </div>
    @endif
</x-filament-panels::page>
