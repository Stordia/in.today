<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div class="mt-6 flex gap-3">
            <x-filament::button type="submit" icon="heroicon-o-rocket-launch">
                Create Restaurant & Owner
            </x-filament::button>

            <x-filament::button
                :href="\App\Filament\Resources\RestaurantResource::getUrl('index')"
                tag="a"
                color="gray"
            >
                Cancel
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
