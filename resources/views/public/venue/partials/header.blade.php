{{--
    Shared Venue Header Component

    @param \App\Models\Restaurant $restaurant - The restaurant/venue
    @param string $country - Country slug (ISO2)
    @param string $city - City slug
    @param string $venue - Venue slug
    @param string $currentTab - Current active tab ('overview', 'menu', or 'book')
    @param string|null $cuisineName - Cuisine name (optional)
    @param string|null $cityName - City display name (optional)
    @param string|null $countryName - Country display name (optional)
    @param string|null $tagline - Venue tagline (optional)
--}}

<div class="relative bg-card border-b border-default">
    {{-- Cover Image --}}
    <div class="relative h-32 sm:h-40 md:h-48 w-full overflow-hidden">
        <img
            src="{{ $restaurant->getCoverImageUrlOrPlaceholder() }}"
            alt="{{ $restaurant->name }}"
            class="w-full h-full object-cover"
        >
        {{-- Gradient Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/20 to-transparent"></div>
    </div>

    {{-- Restaurant Logo & Name Card --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative -mt-12 sm:-mt-16">
            <div class="bg-card rounded-2xl shadow-lg border border-default p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-6">
                    {{-- Logo --}}
                    <div class="flex-shrink-0 -mt-8 sm:-mt-12">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden ring-4 ring-card shadow-lg bg-card">
                            <img
                                src="{{ $restaurant->getLogoUrlOrPlaceholder() }}"
                                alt="{{ $restaurant->name }} logo"
                                class="w-full h-full object-cover"
                            >
                        </div>
                    </div>

                    {{-- Restaurant Info --}}
                    <div class="flex-1 text-center sm:text-left">
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-primary">{{ $restaurant->name }}</h1>

                        {{-- Cuisine & Location --}}
                        @if($cuisineName || $cityName || $countryName)
                            <p class="mt-1 sm:mt-2 text-secondary text-sm">
                                @if($cuisineName)
                                    <span class="font-medium">{{ $cuisineName }}</span>
                                    @if($cityName || $countryName)
                                        <span class="mx-1.5">•</span>
                                    @endif
                                @endif
                                @if($cityName)
                                    {{ $cityName }}@if($countryName),@endif
                                @endif
                                @if($countryName)
                                    {{ $countryName }}
                                @endif
                            </p>
                        @endif

                        {{-- Tagline --}}
                        @if($tagline)
                            <p class="mt-1 sm:mt-2 text-secondary text-sm italic">{{ $tagline }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Local Navigation Tabs --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 sm:mt-6">
        <nav class="flex gap-1 overflow-x-auto scrollbar-hide pb-0.5" role="tablist">
            {{-- Overview Tab --}}
            <a
                href="{{ route('public.venue.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}"
                class="flex-shrink-0 px-4 py-2.5 text-sm font-medium rounded-t-lg transition {{ $currentTab === 'overview' ? 'bg-page text-brand border-b-2 border-brand' : 'text-secondary hover:text-primary hover:bg-page/50' }}"
                role="tab"
                aria-selected="{{ $currentTab === 'overview' ? 'true' : 'false' }}"
            >
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Overview
                </span>
            </a>

            {{-- Menu Tab --}}
            <a
                href="{{ route('public.venue.menu.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}"
                class="flex-shrink-0 px-4 py-2.5 text-sm font-medium rounded-t-lg transition {{ $currentTab === 'menu' ? 'bg-page text-brand border-b-2 border-brand' : 'text-secondary hover:text-primary hover:bg-page/50' }}"
                role="tab"
                aria-selected="{{ $currentTab === 'menu' ? 'true' : 'false' }}"
            >
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Menu
                </span>
            </a>

            {{-- Book a Table Tab (only if booking enabled) --}}
            @if($restaurant->booking_enabled)
                <a
                    href="{{ route('public.venue.book.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}"
                    class="flex-shrink-0 px-4 py-2.5 text-sm font-medium rounded-t-lg transition {{ $currentTab === 'book' ? 'bg-page text-brand border-b-2 border-brand' : 'text-secondary hover:text-primary hover:bg-page/50' }}"
                    role="tab"
                    aria-selected="{{ $currentTab === 'book' ? 'true' : 'false' }}"
                >
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Book a table
                    </span>
                </a>
            @endif
        </nav>
    </div>
</div>

{{-- Add custom styles for horizontal scroll without scrollbar --}}
@push('head')
<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush
