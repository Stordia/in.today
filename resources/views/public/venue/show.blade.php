@extends('layouts.venue')

@section('title', $restaurant->name . ' – ' . ($cityName ?? '') . ($countryName ? ', ' . $countryName : ''))
@section('meta_description', $tagline ?? $restaurant->name)
@section('robots', 'index,follow')

@section('content')
    {{-- Shared Venue Header --}}
    @include('public.venue.partials.header', [
        'currentTab' => 'overview',
        'restaurant' => $restaurant,
        'country' => $country,
        'city' => $city,
        'venue' => $venue,
        'cuisineName' => $cuisineName ?? null,
        'cityName' => $cityName ?? null,
        'countryName' => $countryName ?? null,
        'tagline' => $tagline ?? null,
    ])

    {{-- Main Content --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Column --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Description --}}
                <div class="bg-card rounded-xl shadow-sm border border-default p-6">
                    <h2 class="text-xl font-semibold text-primary mb-4">About</h2>
                    <div class="prose prose-sm max-w-none text-secondary">
                        @if($description)
                            {!! nl2br(e($description)) !!}
                        @else
                            <p class="italic text-secondary/70">This venue has not added a description yet.</p>
                        @endif
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

                {{-- Opening Hours --}}
                <div class="bg-card rounded-xl shadow-sm border border-default p-6">
                    <h2 class="text-lg font-semibold text-primary mb-4">Opening Hours</h2>
                    @if($openingHours->isNotEmpty())
                        @php
                            $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $hoursGrouped = $openingHours->groupBy('day_of_week');
                            $todayIsOpen = false;

                            // Check if today is open
                            if (isset($hoursGrouped[$todayDayOfWeek])) {
                                $todayHours = $hoursGrouped[$todayDayOfWeek];
                                $todayIsOpen = $todayHours->contains('is_open', true);
                            }
                        @endphp

                        <div class="space-y-2 text-sm">
                            @foreach($dayNames as $dayIndex => $dayName)
                                @php
                                    $dayHours = $hoursGrouped->get($dayIndex);
                                    $isToday = $dayIndex === $todayDayOfWeek;
                                @endphp
                                <div class="flex justify-between items-center py-1 {{ $isToday ? 'font-semibold' : '' }}">
                                    <span class="text-secondary">
                                        {{ $dayName }}
                                        @if($isToday)
                                            <span class="ml-1 text-xs bg-brand/10 text-brand px-2 py-0.5 rounded-full">Today</span>
                                        @endif
                                    </span>
                                    <span class="text-secondary">
                                        @if($dayHours && $dayHours->where('is_open', true)->isNotEmpty())
                                            @foreach($dayHours->where('is_open', true) as $hours)
                                                {{ $hours->open_time ? $hours->open_time->format('H:i') : '' }} – {{ $hours->close_time ? $hours->close_time->format('H:i') : '' }}
                                                @if(!$loop->last), @endif
                                            @endforeach
                                        @else
                                            <span class="text-secondary/60">Closed</span>
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Book a table CTA (only if booking enabled) --}}
                        @if($restaurant->booking_enabled)
                            <div class="mt-4 pt-4 border-t border-default">
                                <a
                                    href="{{ route('public.venue.book.show', ['country' => $country, 'city' => $city, 'venue' => $venue]) }}"
                                    class="block w-full px-4 py-2.5 text-center text-sm font-medium text-white bg-brand hover:bg-brand-hover rounded-lg transition shadow-sm"
                                >
                                    Book a table
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-secondary/70 italic">
                            Opening hours not provided yet.
                        </p>
                    @endif
                </div>

                {{-- Map Placeholder --}}
                <div class="bg-card rounded-xl shadow-sm border border-default p-6">
                    <h2 class="text-lg font-semibold text-primary mb-4">Location</h2>
                    <div class="flex items-center justify-center h-40 bg-gray-100 dark:bg-gray-800 rounded-lg">
                        <p class="text-sm text-secondary">Map coming soon</p>
                    </div>
                </div>

                {{-- TODO Phase 2: Share buttons --}}

                {{-- TODO Phase 2: Social links --}}

                {{-- TODO Phase 2: Contact form --}}
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
