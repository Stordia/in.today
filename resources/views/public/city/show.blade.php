@extends('layouts.venue')

@section('title', 'Places in ' . $city->name . ', ' . $country->name)
@section('meta_description', 'Discover restaurants, bars and venues in ' . $city->name . ' with online booking')
@section('robots', 'index,follow')

@section('content')
    {{-- Header Section --}}
    <div class="bg-card border-b border-default">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="max-w-3xl">
                <h1 class="text-3xl sm:text-4xl font-bold text-primary mb-2">
                    Places in {{ $city->name }}, {{ $country->name }}
                </h1>
                @if($restaurants->isNotEmpty())
                    <p class="text-secondary">
                        {{ $restaurants->count() }} {{ Str::plural('venue', $restaurants->count()) }} with online booking
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($restaurants->isEmpty())
            {{-- Empty State --}}
            <div class="text-center py-16">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                    <svg class="w-10 h-10 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-primary mb-2">No venues available yet</h2>
                <p class="text-secondary mb-6">
                    No venues with online booking in this city yet.
                </p>
                <a
                    href="{{ route('root') }}"
                    class="inline-flex items-center px-6 py-3 bg-brand text-white font-medium rounded-xl hover:bg-brand-hover transition"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to search
                </a>
            </div>
        @else
            {{-- Venue Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($restaurants as $restaurant)
                    @php
                        // Safely resolve cuisine name
                        $cuisineName = null;
                        if ($restaurant->cuisine) {
                            $cuisineName = is_object($restaurant->cuisine)
                                ? $restaurant->cuisine->getName()
                                : $restaurant->cuisine;
                        }

                        // Get tagline from settings
                        $tagline = $restaurant->settings['tagline'] ?? null;

                        // Build venue URL
                        $venueUrl = route('public.venue.show', [
                            'country' => $countrySlug,
                            'city' => $citySlug,
                            'venue' => $restaurant->booking_public_slug,
                        ]);

                        $bookUrl = route('public.venue.book.show', [
                            'country' => $countrySlug,
                            'city' => $citySlug,
                            'venue' => $restaurant->booking_public_slug,
                        ]);
                    @endphp

                    {{-- Venue Card --}}
                    <div class="bg-card rounded-xl shadow-sm border border-default overflow-hidden hover:shadow-md transition group">
                        {{-- Cover Image --}}
                        <div class="relative h-40 overflow-hidden bg-gray-100 dark:bg-gray-800">
                            <img
                                src="{{ $restaurant->getCoverImageUrlOrPlaceholder() }}"
                                alt="{{ $restaurant->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                            >
                            {{-- Gradient Overlay --}}
                            <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-transparent"></div>

                            {{-- Online Booking Badge --}}
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    Online booking
                                </span>
                            </div>
                        </div>

                        {{-- Card Content --}}
                        <div class="p-5">
                            {{-- Logo & Name --}}
                            <div class="flex items-start gap-3 mb-3">
                                <div class="flex-shrink-0 -mt-10">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden ring-2 ring-card shadow-md bg-card">
                                        <img
                                            src="{{ $restaurant->getLogoUrlOrPlaceholder() }}"
                                            alt="{{ $restaurant->name }} logo"
                                            class="w-full h-full object-cover"
                                        >
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0 pt-1">
                                    <h3 class="text-lg font-semibold text-primary truncate">
                                        <a href="{{ $venueUrl }}" class="hover:text-brand transition">
                                            {{ $restaurant->name }}
                                        </a>
                                    </h3>
                                </div>
                            </div>

                            {{-- Cuisine & Location --}}
                            <p class="text-sm text-secondary mb-2">
                                @if($cuisineName)
                                    <span class="font-medium">{{ $cuisineName }}</span>
                                    <span class="mx-1.5">•</span>
                                @endif
                                {{ $city->name }}, {{ $country->name }}
                            </p>

                            {{-- Tagline --}}
                            @if($tagline)
                                <p class="text-sm text-secondary italic mb-4 line-clamp-2">
                                    {{ $tagline }}
                                </p>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="flex gap-2 mt-4">
                                <a
                                    href="{{ $venueUrl }}"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium text-primary bg-page hover:bg-gray-100 dark:hover:bg-gray-800 border border-default rounded-lg transition"
                                >
                                    View
                                </a>
                                <a
                                    href="{{ $bookUrl }}"
                                    class="flex-1 px-4 py-2 text-center text-sm font-medium text-white bg-brand hover:bg-brand-hover rounded-lg transition shadow-sm"
                                >
                                    Book a table
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Back to Search Link --}}
            <div class="mt-12 text-center">
                <a
                    href="{{ route('root') }}"
                    class="inline-flex items-center text-sm font-medium text-secondary hover:text-primary transition"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Search another city
                </a>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 border-t border-default">
        <p class="text-center text-sm text-secondary">
            Powered by <a href="{{ route('root') }}" class="text-primary hover:underline font-medium">in.today</a>
        </p>
    </div>
@endsection
