<x-filament-panels::page>
    @if(! $this->hasRestaurant())
        <div class="p-6 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                <x-heroicon-o-building-office-2 class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No Restaurant Selected</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Please select a restaurant to manage its settings.
            </p>
            <a href="{{ route('filament.business.pages.switch-restaurant') }}"
               class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                <x-heroicon-o-arrows-right-left class="w-4 h-4" />
                Select Restaurant
            </a>
        </div>
    @else
        <form wire:submit="save" x-data="{ activeTab: $wire.entangle('activeTab') }">
            {{-- Tabs Navigation --}}
            <x-filament::tabs contained>
                <x-filament::tabs.item
                    wire:click="setActiveTab('profile')"
                    :alpine-active="'activeTab === \'profile\''"
                    icon="heroicon-o-building-office-2"
                >
                    Profile
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="setActiveTab('bookings')"
                    :alpine-active="'activeTab === \'bookings\''"
                    icon="heroicon-o-calendar-days"
                >
                    Bookings
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    wire:click="setActiveTab('deposit')"
                    :alpine-active="'activeTab === \'deposit\''"
                    icon="heroicon-o-banknotes"
                >
                    Deposit
                </x-filament::tabs.item>
            </x-filament::tabs>

            {{-- Form Content (sections are shown/hidden via x-show in the form schema) --}}
            <div class="mt-6">
                {{ $this->form }}
            </div>

            {{-- Save Button --}}
            <div class="mt-6 flex items-center gap-4">
                <x-filament::button type="submit" size="lg">
                    Save Settings
                </x-filament::button>

                <span class="text-sm text-gray-500 dark:text-gray-400" wire:loading wire:target="save">
                    Saving...
                </span>
            </div>
        </form>
    @endif
</x-filament-panels::page>
