@extends('layouts.venue')

@section('title', 'Menu – ' . $restaurant->name . ' | in.today')
@section('meta_description', 'Explore the menu of ' . $restaurant->name . ' in ' . ($cityName ?? 'Unknown') . '. Updated dishes, prices and photos.')
@section('canonical', route('public.venue.menu.show', ['country' => $country, 'city' => $city, 'venue' => $venue]))
@section('robots', 'index,follow')

{{-- OpenGraph --}}
@section('og_title', 'Menu – ' . $restaurant->name)
@section('og_description', 'Explore the menu of ' . $restaurant->name . ' in ' . ($cityName ?? 'Unknown') . '. Updated dishes, prices and photos.')

@section('content')
    {{-- Shared Venue Header --}}
    @include('public.venue.partials.header', [
        'currentTab' => 'menu',
        'restaurant' => $restaurant,
        'country' => $country,
        'city' => $city,
        'venue' => $venue,
        'cuisineName' => $cuisineName ?? null,
        'cityName' => $cityName ?? null,
        'countryName' => $countryName ?? null,
        'tagline' => null, // Menu page doesn't have tagline in current implementation
    ])

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

        </div>
    </div>

    {{-- Footer --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 border-t border-default">
        <p class="text-center text-sm text-secondary">
            Powered by <a href="{{ route('root') }}" class="text-primary hover:underline font-medium">in.today</a>
        </p>
    </div>
@endsection
