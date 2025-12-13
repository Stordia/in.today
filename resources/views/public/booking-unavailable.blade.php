@extends('layouts.venue')

@section('title', 'Online Booking Unavailable – ' . $restaurant->name . ' | in.today')
@section('meta_description', 'Online booking is currently unavailable for ' . $restaurant->name . '. Contact us directly for reservations.')
@section('canonical', route('public.venue.book.show', ['country' => $country, 'city' => $city, 'venue' => $venue]))
@section('robots', 'noindex,follow')

@section('content')
    {{-- Shared Venue Header --}}
    @include('public.venue.partials.header', [
        'restaurant' => $restaurant,
        'country' => $country,
        'city' => $city,
        'venue' => $venue,
        'currentTab' => 'book',
        'cuisineName' => $cuisineName ?? null,
        'cityName' => $cityName ?? null,
        'countryName' => $countryName ?? null,
        'tagline' => $restaurant->settings['tagline'] ?? null,
    ])

    {{-- Main Content --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <div class="max-w-2xl mx-auto">
            {{-- Unavailable Message Card --}}
            <div class="bg-card rounded-2xl shadow-sm border border-default p-6 sm:p-8 text-center">
                {{-- Icon --}}
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                {{-- Title --}}
                <h1 class="text-2xl sm:text-3xl font-bold text-primary mb-3">
                    Online Booking Unavailable
                </h1>

                {{-- Message --}}
                <p class="text-secondary mb-6">
                    Online booking is currently not available for {{ $restaurant->name }}.
                    Please contact us directly to make a reservation.
                </p>

                {{-- Contact Options --}}
                @if($phone || $email || $websiteUrl)
                    <div class="space-y-3 mb-6">
                        @if($phone)
                            <a
                                href="tel:{{ $phone }}"
                                class="flex items-center justify-center gap-3 px-6 py-3 bg-brand text-white font-medium rounded-xl hover:bg-brand-hover transition shadow-sm"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                Call {{ $phone }}
                            </a>
                        @endif

                        @if($email)
                            <a
                                href="mailto:{{ $email }}"
                                class="flex items-center justify-center gap-3 px-6 py-3 bg-page hover:bg-gray-100 dark:hover:bg-gray-800 border border-default text-primary font-medium rounded-xl transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Email {{ $email }}
                            </a>
                        @endif

                        @if($websiteUrl)
                            <a
                                href="{{ $websiteUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center justify-center gap-3 px-6 py-3 bg-page hover:bg-gray-100 dark:hover:bg-gray-800 border border-default text-primary font-medium rounded-xl transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                Visit Website
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Back to Venue --}}
                <div class="pt-4 border-t border-default">
                    <a
                        href="{{ route('public.venue.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}"
                        class="inline-flex items-center text-sm font-medium text-secondary hover:text-primary transition"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to venue
                    </a>
                </div>
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
