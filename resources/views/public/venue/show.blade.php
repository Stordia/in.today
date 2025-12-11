@extends('layouts.marketing')

@section('title', $restaurant->name . ' – ' . ($cityName ?? '') . ($countryName ? ', ' . $countryName : ''))
@section('meta_description', $tagline ?? $restaurant->name)
@section('robots', 'index,follow')

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

                            {{-- Tagline --}}
                            @if($tagline)
                                <p class="mt-2 text-secondary italic">{{ $tagline }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Column --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Description --}}
                @if($description)
                    <div class="bg-card rounded-xl shadow-sm border border-default p-6">
                        <h2 class="text-xl font-semibold text-primary mb-4">About</h2>
                        <div class="prose prose-sm max-w-none text-secondary">
                            {!! nl2br(e($description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Main CTAs --}}
                <div class="bg-card rounded-xl shadow-sm border border-default p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a
                            href="{{ route('public.venue.book.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}"
                            class="inline-flex items-center justify-center px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition shadow-md"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Book a Table
                        </a>

                        <a
                            href="{{ route('public.venue.menu.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}"
                            class="inline-flex items-center justify-center px-6 py-3 bg-card text-primary font-semibold rounded-lg border-2 border-primary hover:bg-primary hover:text-white transition"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            View Menu
                        </a>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Contact Information --}}
                <div class="bg-card rounded-xl shadow-sm border border-default p-6">
                    <h2 class="text-lg font-semibold text-primary mb-4">Contact</h2>
                    <div class="space-y-3 text-sm">
                        @if($restaurant->address_street || $cityName)
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-secondary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <div class="text-secondary">
                                    @if($restaurant->address_street)
                                        {{ $restaurant->address_street }}<br>
                                    @endif
                                    @if($restaurant->address_postal || $cityName)
                                        @if($restaurant->address_postal){{ $restaurant->address_postal }} @endif{{ $cityName }}
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($phone)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-secondary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <a href="tel:{{ $phone }}" class="text-secondary hover:text-primary transition">{{ $phone }}</a>
                            </div>
                        @endif

                        @if($email)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-secondary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <a href="mailto:{{ $email }}" class="text-secondary hover:text-primary transition">{{ $email }}</a>
                            </div>
                        @endif

                        @if($websiteUrl)
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-secondary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer" class="text-secondary hover:text-primary transition">
                                    {{ parse_url($websiteUrl, PHP_URL_HOST) ?? $websiteUrl }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Opening Hours Info --}}
                <div class="bg-card rounded-xl shadow-sm border border-default p-6">
                    <h2 class="text-lg font-semibold text-primary mb-4">Hours</h2>
                    <p class="text-sm text-secondary">
                        View available booking times on the <a href="{{ route('public.venue.book.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}" class="text-primary hover:underline">booking page</a>.
                    </p>
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
