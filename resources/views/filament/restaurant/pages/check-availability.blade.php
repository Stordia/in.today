<x-filament-panels::page>
    @if(! $this->hasRestaurant())
        <div class="flex flex-col items-center justify-center py-12">
            <x-heroicon-o-building-storefront class="h-16 w-16 text-gray-400 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No Restaurant Selected</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Please select a restaurant to check availability.</p>
        </div>
    @else
        <x-filament::section>
            <x-slot name="heading">
                Search Parameters
            </x-slot>

            <form wire:submit="checkAvailability">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
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

                    <div>
                        <label for="party_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Party Size</label>
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
                    </div>

                    <div>
                        <x-filament::button type="submit" wire:loading.attr="disabled">
                            <x-filament::loading-indicator wire:loading wire:target="checkAvailability" class="h-4 w-4 mr-2" />
                            Check Availability
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </x-filament::section>

        @if($this->availabilityResult !== null)
            <x-filament::section class="mt-6">
                <x-slot name="heading">
                    Available Time Slots
                </x-slot>
                <x-slot name="description">
                    {{ \Carbon\Carbon::parse($this->date)->format('l, F j, Y') }} &middot; Party of {{ $this->party_size }}
                </x-slot>

                @if(count($this->getSlots()) === 0)
                    <div class="flex flex-col items-center justify-center py-8">
                        <x-heroicon-o-calendar-days class="h-12 w-12 text-gray-400 mb-3" />
                        <p class="text-gray-500 dark:text-gray-400">No availability for this date.</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">The restaurant may be closed or fully booked.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Time</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Max Party Size</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Status</th>
                                    <th class="text-right py-3 px-4 font-medium text-gray-700 dark:text-gray-300">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->getSlots() as $slot)
                                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                        <td class="py-3 px-4">
                                            <span class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $slot->getStartTime() }}
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                - {{ $slot->getEndTime() }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600 dark:text-gray-400">
                                            {{ $slot->maxPartySizeForSlot }} guests
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($slot->isBookable)
                                                <x-filament::badge color="success">
                                                    Bookable
                                                </x-filament::badge>
                                            @else
                                                <x-filament::badge color="gray">
                                                    Not Available
                                                </x-filament::badge>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($slot->isBookable)
                                                <x-filament::button
                                                    size="sm"
                                                    color="primary"
                                                    icon="heroicon-o-user-plus"
                                                    :href="$this->getCreateReservationUrl($slot->getStartTime())"
                                                    tag="a"
                                                >
                                                    Create Reservation
                                                </x-filament::button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        <p>
                            <strong>{{ $this->availabilityResult->bookableSlotCount() }}</strong> of
                            <strong>{{ $this->availabilityResult->totalSlots() }}</strong> slots are bookable.
                        </p>
                    </div>
                @endif
            </x-filament::section>
        @endif
    @endif
</x-filament-panels::page>
