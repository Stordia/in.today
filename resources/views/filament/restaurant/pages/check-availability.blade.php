<x-filament-panels::page>
    @if(! $this->hasRestaurant())
        <div class="flex flex-col items-center justify-center py-16">
            <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-6">
                <x-heroicon-o-building-storefront class="h-10 w-10 text-gray-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">No Restaurant Selected</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-sm">
                Please select a restaurant from the sidebar to check table availability.
            </p>
        </div>
    @else
        {{-- Search Form --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-primary-500" />
                    Search Parameters
                </div>
            </x-slot>
            <x-slot name="description">
                Select a date and party size to check available time slots.
            </x-slot>

            <form wire:submit="checkAvailability">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div class="sm:col-span-1">
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Date
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="date"
                                wire:model="date"
                                id="date"
                                min="{{ now()->toDateString() }}"
                                required
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <div class="sm:col-span-1">
                        <label for="party_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            Party Size
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="number"
                                wire:model="party_size"
                                id="party_size"
                                min="1"
                                max="50"
                                required
                            />
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Number of guests</p>
                    </div>

                    <div class="sm:col-span-2 lg:col-span-2 flex items-end">
                        <x-filament::button type="submit" wire:loading.attr="disabled" class="w-full sm:w-auto">
                            <x-filament::loading-indicator wire:loading wire:target="checkAvailability" class="h-4 w-4 mr-2" />
                            <x-heroicon-o-clock wire:loading.remove wire:target="checkAvailability" class="h-4 w-4 mr-2" />
                            Check Availability
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </x-filament::section>

        {{-- Results --}}
        @if($this->availabilityResult !== null)
            <x-filament::section class="mt-6">
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-calendar-days class="h-5 w-5 text-primary-500" />
                        Available Time Slots
                    </div>
                </x-slot>
                <x-slot name="description">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-1.5">
                            <x-heroicon-o-calendar class="h-4 w-4" />
                            {{ \Carbon\Carbon::parse($this->date)->format('l, F j, Y') }}
                        </span>
                        <span class="text-gray-400">&middot;</span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-heroicon-o-users class="h-4 w-4" />
                            {{ $this->party_size }} {{ $this->party_size === 1 ? 'guest' : 'guests' }}
                        </span>
                    </div>
                </x-slot>

                @if(count($this->getSlots()) === 0)
                    <div class="flex flex-col items-center justify-center py-12">
                        <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                            <x-heroicon-o-calendar-days class="h-8 w-8 text-gray-400" />
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 font-medium mb-1">No availability for this date</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-sm">
                            The restaurant may be closed or fully booked. Try a different date or party size.
                        </p>
                    </div>
                @else
                    {{-- Quick Stats --}}
                    <div class="mb-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="rounded-xl bg-success-50 dark:bg-success-500/10 border border-success-200 dark:border-success-500/20 p-4">
                            <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $this->availabilityResult->bookableSlotCount() }}</p>
                            <p class="text-sm text-success-700 dark:text-success-300">Available slots</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
                            <p class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $this->availabilityResult->totalSlots() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total slots</p>
                        </div>
                        <div class="rounded-xl bg-primary-50 dark:bg-primary-500/10 border border-primary-200 dark:border-primary-500/20 p-4 hidden sm:block">
                            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                {{ round(($this->availabilityResult->bookableSlotCount() / max(1, $this->availabilityResult->totalSlots())) * 100) }}%
                            </p>
                            <p class="text-sm text-primary-700 dark:text-primary-300">Availability</p>
                        </div>
                    </div>

                    {{-- Slots Table --}}
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Time Slot</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Capacity</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                    <th class="text-right py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($this->getSlots() as $slot)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors {{ !$slot->isBookable ? 'opacity-60' : '' }}">
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 rounded-full {{ $slot->isBookable ? 'bg-success-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                                <span class="font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $slot->getStartTime() }}
                                                </span>
                                                <span class="text-gray-400 dark:text-gray-500">
                                                    – {{ $slot->getEndTime() }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                                <x-heroicon-o-users class="h-4 w-4" />
                                                {{ $slot->maxPartySizeForSlot }} max
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($slot->isBookable)
                                                <x-filament::badge color="success" icon="heroicon-o-check-circle">
                                                    Available
                                                </x-filament::badge>
                                            @else
                                                <x-filament::badge color="gray" icon="heroicon-o-x-circle">
                                                    Not Available
                                                </x-filament::badge>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            @if($slot->isBookable)
                                                <x-filament::button
                                                    size="sm"
                                                    color="primary"
                                                    icon="heroicon-o-plus"
                                                    :href="$this->getCreateReservationUrl($slot->getStartTime())"
                                                    tag="a"
                                                >
                                                    Create
                                                </x-filament::button>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500 text-sm">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>
        @else
            {{-- Initial State --}}
            <x-filament::section class="mt-6">
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="w-16 h-16 rounded-full bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center mb-4">
                        <x-heroicon-o-clock class="h-8 w-8 text-primary-500" />
                    </div>
                    <p class="text-gray-700 dark:text-gray-300 font-medium mb-1">Check table availability</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-sm">
                        Select a date and party size above to see available time slots for booking.
                    </p>
                </div>
            </x-filament::section>
        @endif
    @endif
</x-filament-panels::page>
