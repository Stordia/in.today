@extends('layouts.marketing')

@section('title', 'Menu – ' . $restaurant->name)
@section('meta_description', 'View the menu for ' . $restaurant->name)
@section('robots', 'noindex,nofollow')

@section('content')
    {{-- Hero Header with Restaurant Branding --}}
    <div class="relative">
        {{-- Cover Image --}}
        <div class="h-48 sm:h-56 md:h-64 w-full overflow-hidden">
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
            <div class="relative -mt-16 sm:-mt-20">
                <div class="bg-card rounded-2xl shadow-lg border border-default p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-6">
                        {{-- Logo --}}
                        <div class="flex-shrink-0 -mt-12 sm:-mt-16">
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden ring-4 ring-card shadow-lg bg-card">
                                <img
                                    src="{{ $restaurant->getLogoUrlOrPlaceholder() }}"
                                    alt="{{ $restaurant->name }} logo"
                                    class="w-full h-full object-cover"
                                >
                            </div>
                        </div>

                        {{-- Restaurant Info --}}
                        <div class="flex-1 text-center sm:text-left">
                            <h1 class="text-2xl sm:text-3xl font-bold text-primary">{{ $restaurant->name }}</h1>

                            {{-- Cuisine & Location --}}
                            @if($cuisineName || $cityName || $countryName)
                                <p class="mt-2 text-secondary text-sm sm:text-base">
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

                            {{-- Back to venue link --}}
                            <p class="mt-2">
                                <a
                                    href="{{ route('public.venue.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}"
                                    class="text-sm text-primary hover:underline"
                                >
                                    ← Back to venue page
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-card rounded-xl shadow-sm border border-default p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-primary mb-4">Menu</h2>

            <div class="prose prose-sm max-w-none text-secondary">
                <p class="text-base">
                    The interactive menu widget will be implemented in Phase 2.5.
                </p>
                <p class="text-base mt-4">
                    For now, please contact the venue directly or check their website/social media for the current menu.
                </p>

                {{-- Link back to venue if website available --}}
                @if($restaurant->settings['website_url'] ?? null)
                    @php
                        $websiteUrl = $restaurant->settings['website_url'];
                        if (!preg_match('/^https?:\/\//i', $websiteUrl)) {
                            $websiteUrl = 'https://' . $websiteUrl;
                        }
                    @endphp
                    <p class="mt-6">
                        <a
                            href="{{ $websiteUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center px-4 py-2 bg-primary text-white font-medium rounded-lg hover:bg-primary-dark transition"
                        >
                            Visit Website
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </p>
                @endif
            </div>

            {{-- Quick action: Book a table --}}
            <div class="mt-8 pt-6 border-t border-default">
                <a
                    href="{{ route('public.venue.book.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}"
                    class="inline-flex items-center justify-center px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition shadow-md"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Book a Table
                </a>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 border-t border-default">
        <p class="text-center text-sm text-secondary">
            Powered by <a href="{{ route('root') }}" class="text-primary hover:underline font-medium">in.today</a>
        </p>
    </div>
@endsection
