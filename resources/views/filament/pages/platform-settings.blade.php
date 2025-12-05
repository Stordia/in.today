<x-filament-panels::page>
    <form wire:submit="save">
        {{-- Tabs Navigation --}}
        <x-filament::tabs contained>
            <x-filament::tabs.item
                wire:click="setActiveTab('email')"
                :alpine-active="'$wire.activeTab === \'email\''"
                icon="heroicon-o-envelope"
            >
                Email
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click="setActiveTab('bookings')"
                :alpine-active="'$wire.activeTab === \'bookings\''"
                icon="heroicon-o-calendar-days"
            >
                Bookings
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click="setActiveTab('affiliates')"
                :alpine-active="'$wire.activeTab === \'affiliates\''"
                icon="heroicon-o-user-group"
            >
                Affiliates
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click="setActiveTab('technical')"
                :alpine-active="'$wire.activeTab === \'technical\''"
                icon="heroicon-o-wrench-screwdriver"
            >
                Technical
            </x-filament::tabs.item>
        </x-filament::tabs>

        {{-- Form Content (sections are shown/hidden via visible() in the form schema) --}}
        <div class="mt-6">
            {{ $this->form }}
        </div>

        {{-- Save Button --}}
        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                Save Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
