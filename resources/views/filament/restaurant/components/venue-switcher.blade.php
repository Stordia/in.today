@php
    use App\Support\Tenancy\CurrentRestaurant;
    use Illuminate\Support\Facades\Auth;

    $currentRestaurant = CurrentRestaurant::get();
    $restaurants = CurrentRestaurant::getUserRestaurants(Auth::user());
    $hasMultipleRestaurants = $restaurants->count() > 1;
@endphp

@if($currentRestaurant && $hasMultipleRestaurants)
    <div class="fi-topbar-item flex items-center justify-center relative" x-data="{ open: false }">
        <button
            type="button"
            @click="open = !open"
            @click.away="open = false"
            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 transition"
        >
            <x-filament::icon
                icon="heroicon-o-building-storefront"
                class="h-5 w-5"
            />
            <span>{{ $currentRestaurant->name }}</span>
            <x-filament::icon
                icon="heroicon-m-chevron-down"
                class="h-4 w-4 transition"
                ::class="{ 'rotate-180': open }"
            />
        </button>

        <div
            x-show="open"
            x-transition
            x-cloak
            class="absolute top-full right-0 mt-2 w-64 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 z-50"
            style="display: none;"
        >
            <div class="p-2">
                <div class="mb-2 px-3 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">
                    Switch Venue
                </div>
                @foreach($restaurants as $restaurant)
                    <a
                        href="{{ route('filament.business.pages.switch-restaurant') }}?restaurant_id={{ $restaurant->id }}"
                        wire:navigate
                        @click="open = false"
                        class="flex items-center gap-3 rounded-md px-3 py-2 text-sm {{ $restaurant->id === $currentRestaurant->id ? 'bg-gray-50 font-semibold text-gray-900 dark:bg-white/5 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5' }} transition"
                    >
                        @if($restaurant->id === $currentRestaurant->id)
                            <x-filament::icon
                                icon="heroicon-m-check"
                                class="h-4 w-4 text-primary-600 dark:text-primary-400"
                            />
                        @else
                            <span class="w-4"></span>
                        @endif
                        <div class="flex-1">
                            <div>{{ $restaurant->name }}</div>
                            @if($restaurant->city)
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $restaurant->city->name }}
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@elseif($currentRestaurant)
    {{-- Single restaurant: show name as non-clickable label --}}
    <div class="fi-topbar-item flex items-center justify-center">
        <div class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            <x-filament::icon
                icon="heroicon-o-building-storefront"
                class="h-5 w-5"
            />
            <span>{{ $currentRestaurant->name }}</span>
        </div>
    </div>
@endif
