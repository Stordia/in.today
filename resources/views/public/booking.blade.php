@extends('layouts.marketing')

@section('title', __('booking.page_title') . ' – ' . $restaurant->name)
@section('meta_description', __('booking.page_subtitle'))
@section('robots', 'noindex,nofollow')

@php
    // Safely resolve cuisine name (relation object vs string)
    $cuisineName = null;
    if ($restaurant->cuisine) {
        $cuisineName = is_object($restaurant->cuisine)
            ? $restaurant->cuisine->getName()
            : $restaurant->cuisine;
    }

    // Safely resolve city name (relation object vs string)
    $cityName = null;
    $countryName = null;
    if ($restaurant->city) {
        if (is_object($restaurant->city)) {
            $cityName = $restaurant->city->name ?? null;
            // City has both 'country' relation and 'country' legacy text field
            // Try the relation first, fall back to the text field
            if ($restaurant->city->relationLoaded('country') && $restaurant->city->country) {
                $countryName = $restaurant->city->country->name ?? null;
            } elseif (is_string($restaurant->city->country)) {
                $countryName = $restaurant->city->country;
            }
        } else {
            $cityName = $restaurant->city;
        }
    }
@endphp

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

                            {{-- Cuisine & Location in text format --}}
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

                            {{-- Address --}}
                            @if($restaurant->address_street)
                                <p class="text-sm text-secondary mt-2 flex items-center justify-center sm:justify-start gap-1.5">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span>
                                        {{ $restaurant->address_street }}@if($restaurant->address_postal), {{ $restaurant->address_postal }}@endif
                                    </span>
                                </p>
                            @endif
                        </div>

                        {{-- Badge: Online Booking --}}
                        <div class="hidden sm:flex flex-shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-sm font-medium">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                {{ __('booking.header.online_booking') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-8 sm:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if (session('booking_status') === 'success')
                <div class="mb-8 rounded-2xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/50 px-6 py-5" role="alert" aria-live="polite">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 dark:bg-green-800/50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-green-800 dark:text-green-300">{{ __('booking.success.title') }}</h3>
                            <p class="text-green-700 dark:text-green-400 mt-1">
                                {{ __('booking.success.message') }}
                            </p>
                            @if (session('deposit_required'))
                                <div class="mt-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800/50">
                                    <h4 class="font-semibold text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ __('booking.success.deposit_title') }}
                                    </h4>
                                    <p class="text-amber-700 dark:text-amber-400 mt-2 text-sm">
                                        {{ __('booking.success.deposit_message', ['amount' => session('formatted_deposit_amount')]) }}
                                    </p>
                                    <p class="text-amber-600 dark:text-amber-500 mt-1 text-sm">
                                        {{ __('booking.success.deposit_instructions') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="mb-8 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 px-6 py-5" role="alert" aria-live="assertive">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-800/50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-red-800 dark:text-red-300">{{ __('booking.error.title') }}</h3>
                            <ul class="text-red-700 dark:text-red-400 mt-2 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                                        {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Main Layout: Two Column on Desktop --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Left Column: Booking Steps --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Step 1: Date & Party Size --}}
                    <div class="bg-card rounded-2xl shadow-sm border border-default p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-primary mb-5 flex items-center gap-3">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center text-sm font-bold">1</span>
                            {{ __('booking.step_1.title') }}
                        </h2>

                        <form method="POST" action="{{ route('public.booking.show', $restaurant->booking_public_slug) }}">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                {{-- Date Input with Calendar Icon --}}
                                <div>
                                    <label for="check_date" class="block text-sm font-medium text-primary mb-2">
                                        {{ __('booking.step_1.date_label') }}
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="date"
                                            id="check_date"
                                            name="date"
                                            value="{{ $date }}"
                                            min="{{ $minDate }}"
                                            max="{{ $maxDate }}"
                                            required
                                            class="w-full px-4 py-3 pr-12 rounded-xl border border-default bg-page text-primary focus:ring-2 focus:ring-brand focus:border-transparent transition"
                                        >
                                        <button
                                            type="button"
                                            onclick="openDatePicker()"
                                            class="absolute right-1 top-1/2 -translate-y-1/2 p-2 rounded-lg text-secondary hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                            aria-label="{{ __('booking.step_1.date_label') }}"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Party Size Dropdown --}}
                                <div>
                                    <label for="check_party_size" class="block text-sm font-medium text-primary mb-2">
                                        {{ __('booking.step_1.party_size_label') }}
                                    </label>
                                    <div class="relative">
                                        <select
                                            id="check_party_size"
                                            name="party_size"
                                            required
                                            class="w-full px-4 py-3 pr-10 rounded-xl border border-default bg-page text-primary focus:ring-2 focus:ring-brand focus:border-transparent transition appearance-none cursor-pointer"
                                        >
                                            @for($i = $minPartySize; $i <= $maxPartySize; $i++)
                                                <option value="{{ $i }}" @selected($partySize === $i)>
                                                    {{ trans_choice('booking.summary.guests_count', $i, ['count' => $i]) }}
                                                </option>
                                            @endfor
                                        </select>
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                            <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="mt-1.5 text-xs text-secondary">
                                        {{ __('booking.step_1.party_size_hint', ['min' => $minPartySize, 'max' => $maxPartySize]) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="mt-6">
                                <button
                                    type="submit"
                                    class="w-full sm:w-auto px-6 py-3 bg-gray-800 dark:bg-gray-700 text-white font-semibold rounded-xl hover:bg-gray-900 dark:hover:bg-gray-600 transition focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                >
                                    {{ __('booking.step_1.check_availability') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    @php
                        $bookableSlots = collect($availability->slots)->filter(fn($slot) => $slot->isBookable);
                        $selectedTime = old('time', $bookableSlots->first()?->getStartTime());
                    @endphp

                    {{-- Step 2: Time Slots --}}
                    <div class="bg-card rounded-2xl shadow-sm border border-default p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-primary mb-2 flex items-center gap-3">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center text-sm font-bold">2</span>
                            {{ __('booking.step_2.title') }}
                        </h2>

                        <p class="text-secondary mb-6 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}
                            </span>
                            <span class="text-secondary">&middot;</span>
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ __('booking.step_2.party_of', ['count' => $partySize]) }}
                            </span>
                        </p>

                        @if(! $availability->hasAnySlots() || $bookableSlots->isEmpty())
                            <div class="text-center py-12 px-4">
                                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-lg font-medium text-primary mb-2">{{ __('booking.step_2.no_slots_title') }}</p>
                                <p class="text-sm text-secondary">{{ __('booking.step_2.no_slots_hint') }}</p>
                            </div>
                        @else
                            {{-- Time Slot Grid - Pill/Button Style --}}
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2 sm:gap-3">
                                @foreach($availability->slots as $slot)
                                    @if($slot->isBookable)
                                        @php $timeValue = $slot->getStartTime(); @endphp
                                        <label class="relative cursor-pointer group">
                                            <input
                                                type="radio"
                                                name="time"
                                                value="{{ $timeValue }}"
                                                form="booking-form"
                                                class="sr-only peer"
                                                @checked(old('time', $selectedTime) === $timeValue)
                                            >
                                            <div class="rounded-xl border-2 py-3 px-2 text-center transition-all duration-150
                                                        border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20
                                                        hover:border-green-400 dark:hover:border-green-600 hover:bg-green-100 dark:hover:bg-green-900/30
                                                        peer-checked:bg-brand peer-checked:text-white peer-checked:border-brand
                                                        peer-checked:shadow-lg peer-checked:shadow-brand/25 peer-checked:scale-[1.02]
                                                        peer-focus:ring-2 peer-focus:ring-brand peer-focus:ring-offset-2">
                                                <span class="block text-lg font-bold text-green-700 dark:text-green-400 peer-checked:text-white">{{ $timeValue }}</span>
                                            </div>
                                            {{-- Checkmark Badge --}}
                                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-brand rounded-full items-center justify-center ring-2 ring-card hidden peer-checked:flex shadow-sm">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                        </label>
                                    @else
                                        {{-- Unavailable Slot --}}
                                        <div class="rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30 py-3 px-2 text-center opacity-50 cursor-not-allowed">
                                            <span class="block text-lg font-bold text-gray-400 dark:text-gray-600 line-through">{{ $slot->getStartTime() }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <div class="mt-5 pt-5 border-t border-default flex flex-wrap items-center justify-between gap-4">
                                <p class="text-sm text-secondary">
                                    {{ __('booking.step_2.slots_summary', ['available' => $availability->bookableSlotCount(), 'total' => $availability->totalSlots()]) }}
                                </p>
                                {{-- Legend --}}
                                <div class="flex items-center gap-4 text-xs text-secondary">
                                    <span class="flex items-center gap-1.5">
                                        <span class="w-3 h-3 rounded bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700"></span>
                                        {{ __('booking.step_2.legend_available') }}
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <span class="w-3 h-3 rounded bg-gray-100 dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-600"></span>
                                        {{ __('booking.step_2.legend_unavailable') }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Step 3: Booking Form --}}
                    @if($bookableSlots->isNotEmpty())
                        <div class="bg-card rounded-2xl shadow-sm border border-default p-6 sm:p-8">
                            <h2 class="text-lg font-semibold text-primary mb-5 flex items-center gap-3">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center text-sm font-bold">3</span>
                                {{ __('booking.step_3.title') }}
                            </h2>

                            <form id="booking-form" method="POST" action="{{ route('public.booking.request', $restaurant->booking_public_slug) }}">
                                @csrf

                                {{-- Hidden fields --}}
                                <input type="hidden" name="date" value="{{ $date }}">
                                <input type="hidden" name="party_size" value="{{ $partySize }}">

                                {{-- Honeypot field (spam protection) --}}
                                <input type="text" name="hp_website" autocomplete="off" class="hidden" tabindex="-1" aria-hidden="true">

                                <div class="space-y-5">
                                    {{-- Name & Email --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-primary mb-2">
                                                {{ __('booking.step_3.name_label') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                id="name"
                                                name="name"
                                                value="{{ old('name') }}"
                                                required
                                                maxlength="255"
                                                placeholder="{{ __('booking.step_3.name_placeholder') }}"
                                                class="w-full px-4 py-3 rounded-xl border border-default bg-page text-primary placeholder-secondary/50 focus:ring-2 focus:ring-brand focus:border-transparent transition @error('name') border-red-500 ring-1 ring-red-500 @enderror"
                                            >
                                            @error('name')
                                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="email" class="block text-sm font-medium text-primary mb-2">
                                                {{ __('booking.step_3.email_label') }} <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                type="email"
                                                id="email"
                                                name="email"
                                                value="{{ old('email') }}"
                                                required
                                                maxlength="255"
                                                placeholder="{{ __('booking.step_3.email_placeholder') }}"
                                                class="w-full px-4 py-3 rounded-xl border border-default bg-page text-primary placeholder-secondary/50 focus:ring-2 focus:ring-brand focus:border-transparent transition @error('email') border-red-500 ring-1 ring-red-500 @enderror"
                                            >
                                            @error('email')
                                                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Phone --}}
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-primary mb-2">
                                            {{ __('booking.step_3.phone_label') }}
                                            <span class="text-secondary font-normal">({{ __('booking.step_3.phone_optional') }})</span>
                                        </label>
                                        <input
                                            type="tel"
                                            id="phone"
                                            name="phone"
                                            value="{{ old('phone') }}"
                                            maxlength="255"
                                            placeholder="{{ __('booking.step_3.phone_placeholder') }}"
                                            class="w-full px-4 py-3 rounded-xl border border-default bg-page text-primary placeholder-secondary/50 focus:ring-2 focus:ring-brand focus:border-transparent transition"
                                        >
                                    </div>

                                    {{-- Notes --}}
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-primary mb-2">
                                            {{ __('booking.step_3.notes_label') }}
                                            <span class="text-secondary font-normal">({{ __('booking.step_3.notes_optional') }})</span>
                                        </label>
                                        <textarea
                                            id="notes"
                                            name="notes"
                                            rows="3"
                                            maxlength="1000"
                                            placeholder="{{ __('booking.step_3.notes_placeholder') }}"
                                            class="w-full px-4 py-3 rounded-xl border border-default bg-page text-primary placeholder-secondary/50 focus:ring-2 focus:ring-brand focus:border-transparent transition resize-none"
                                        >{{ old('notes') }}</textarea>
                                    </div>

                                    {{-- Deposit Section --}}
                                    @if($requiresDeposit)
                                        {{-- Deposit Info Card --}}
                                        <div class="p-5 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-800/50 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-semibold text-amber-800 dark:text-amber-300">
                                                        {{ __('booking.deposit.info_title') }}
                                                    </h4>
                                                    <p class="text-amber-700 dark:text-amber-400 mt-1 text-sm">
                                                        {{ __('booking.deposit.info_message', ['amount' => $formattedDepositAmount]) }}
                                                    </p>
                                                    <p class="text-amber-600 dark:text-amber-500 mt-2 text-sm">
                                                        {{ __('booking.deposit.payment_instructions') }}
                                                    </p>
                                                    @if($depositPolicy)
                                                        <p class="text-amber-600 dark:text-amber-500 mt-2 text-sm italic">
                                                            {{ $depositPolicy }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Deposit Consent Checkbox --}}
                                        <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-default @error('accepted_deposit') border-red-500 @enderror">
                                            <input
                                                type="checkbox"
                                                id="accepted_deposit"
                                                name="accepted_deposit"
                                                value="1"
                                                {{ old('accepted_deposit') ? 'checked' : '' }}
                                                class="mt-0.5 w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-brand focus:ring-brand dark:bg-gray-800"
                                            >
                                            <label for="accepted_deposit" class="text-sm text-primary leading-relaxed">
                                                {{ __('booking.deposit.consent_text', ['amount' => $formattedDepositAmount]) }}
                                                <span class="text-red-500">*</span>
                                            </label>
                                        </div>
                                        @error('accepted_deposit')
                                            <p class="-mt-3 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    @else
                                        {{-- No Deposit Required --}}
                                        <div class="p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800/50">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-800/50 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                                <p class="text-sm text-green-700 dark:text-green-400">
                                                    {{ __('booking.deposit.not_required') }}
                                                </p>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Terms & Privacy Checkbox --}}
                                    <div class="flex items-start gap-3 @error('accepted_terms') text-red-500 @enderror">
                                        <input
                                            type="checkbox"
                                            id="accepted_terms"
                                            name="accepted_terms"
                                            value="1"
                                            {{ old('accepted_terms') ? 'checked' : '' }}
                                            class="mt-0.5 w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-brand focus:ring-brand dark:bg-gray-800 @error('accepted_terms') border-red-500 @enderror"
                                        >
                                        <label for="accepted_terms" class="text-sm text-primary">
                                            {{ __('booking.step_3.terms_consent_label') }} <span class="text-red-500">*</span>
                                        </label>
                                    </div>
                                    @error('accepted_terms')
                                        <p class="-mt-3 text-xs text-red-500">{{ $message }}</p>
                                    @enderror

                                    {{-- Submit Button --}}
                                    <div class="pt-3">
                                        <button
                                            type="submit"
                                            class="w-full sm:w-auto px-8 py-3.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition focus:ring-2 focus:ring-brand focus:ring-offset-2 flex items-center justify-center gap-2 shadow-lg shadow-brand/25"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ __('booking.step_3.submit') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                </div>

                {{-- Right Column: Summary & Info --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">

                        {{-- Reservation Summary Card --}}
                        @if($bookableSlots->isNotEmpty())
                            <div class="bg-card rounded-2xl shadow-sm border border-default overflow-hidden">
                                <div class="bg-brand/5 dark:bg-brand/10 px-6 py-4 border-b border-brand/20">
                                    <h3 class="font-semibold text-primary flex items-center gap-2">
                                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        {{ __('booking.summary.title') }}
                                    </h3>
                                </div>

                                <div class="p-6 space-y-4">
                                    {{-- Restaurant --}}
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 flex-shrink-0">
                                            <img src="{{ $restaurant->getLogoUrlOrPlaceholder() }}" alt="" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <p class="font-medium text-primary">{{ $restaurant->name }}</p>
                                            @if($restaurant->city)
                                                <p class="text-xs text-secondary">{{ $restaurant->city->name }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <hr class="border-default">

                                    {{-- Date --}}
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-secondary uppercase tracking-wide">{{ __('booking.summary.date') }}</p>
                                            <p class="font-medium text-primary">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}</p>
                                        </div>
                                    </div>

                                    {{-- Time --}}
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-secondary uppercase tracking-wide">{{ __('booking.summary.time') }}</p>
                                            <p class="font-medium text-primary" id="summary-time-display">{{ $selectedTime ?? '—' }}</p>
                                        </div>
                                    </div>

                                    {{-- Party Size --}}
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-secondary uppercase tracking-wide">{{ __('booking.summary.guests') }}</p>
                                            <p class="font-medium text-primary">{{ trans_choice('booking.summary.guests_count', $partySize, ['count' => $partySize]) }}</p>
                                        </div>
                                    </div>

                                    {{-- Deposit Info in Summary --}}
                                    @if($requiresDeposit)
                                        <hr class="border-default">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-xs text-secondary uppercase tracking-wide">{{ __('booking.summary.deposit') }}</p>
                                                <p class="font-medium text-amber-700 dark:text-amber-400">{{ $formattedDepositAmount }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Booking Rules Card --}}
                        <div class="bg-card rounded-2xl shadow-sm border border-default p-6">
                            <h3 class="text-sm font-medium text-secondary uppercase tracking-wide mb-4">
                                {{ __('booking.info.title') }}
                            </h3>

                            <div class="space-y-3 text-sm">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-secondary">
                                        {{ __('booking.info.party_size', ['min' => $minPartySize, 'max' => $maxPartySize]) }}
                                    </p>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-secondary">
                                        {{ __('booking.info.lead_time_days', ['days' => $restaurant->booking_max_lead_time_days ?? 30]) }}
                                    </p>
                                </div>

                                @if(($restaurant->booking_min_lead_time_minutes ?? 0) > 0)
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-secondary">
                                            @if($restaurant->booking_min_lead_time_minutes >= 60)
                                                {{ __('booking.info.lead_time_hours', ['hours' => floor($restaurant->booking_min_lead_time_minutes / 60)]) }}
                                            @else
                                                {{ __('booking.info.lead_time_minutes', ['minutes' => $restaurant->booking_min_lead_time_minutes]) }}
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                {{-- Deposit threshold info --}}
                                @if($restaurant->booking_deposit_enabled && ($restaurant->booking_deposit_threshold_party_size ?? 0) > 0)
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-secondary">
                                            {{ __('booking.info.deposit_threshold', ['threshold' => $restaurant->booking_deposit_threshold_party_size]) }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Powered by in.today --}}
                        <div class="text-center">
                            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs text-secondary hover:text-primary transition">
                                {{ __('booking.footer.powered_by') }}
                                <span class="font-semibold">in.today</span>
                            </a>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Update time display when selection changes + Date picker helper --}}
    @push('scripts')
    <script>
        // Open native date picker when calendar icon is clicked
        function openDatePicker() {
            const dateInput = document.getElementById('check_date');
            if (dateInput) {
                // Prefer showPicker() if supported (Chrome 99+, Safari 16+, Edge 99+)
                if (typeof dateInput.showPicker === 'function') {
                    try {
                        dateInput.showPicker();
                    } catch (e) {
                        // Fallback for edge cases where showPicker fails
                        dateInput.focus();
                    }
                } else {
                    // Fallback for older browsers
                    dateInput.focus();
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const timeRadios = document.querySelectorAll('input[name="time"]');
            const summaryTimeDisplay = document.getElementById('summary-time-display');

            if (timeRadios.length && summaryTimeDisplay) {
                timeRadios.forEach(radio => {
                    radio.addEventListener('change', () => {
                        summaryTimeDisplay.textContent = radio.value;
                    });
                });
            }
        });
    </script>
    @endpush
@endsection
