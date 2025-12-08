@extends('layouts.marketing')

@section('title', __('booking.page_title') . ' – ' . $restaurant->name)
@section('meta_description', __('booking.page_subtitle'))
@section('robots', 'noindex,nofollow')

@section('content')
    <div class="pt-24 pb-16 min-h-screen">
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

                    {{-- Page Header --}}
                    <div class="mb-2">
                        <h1 class="text-3xl sm:text-4xl font-bold text-primary">
                            {{ __('booking.page_title') }}
                        </h1>
                        <p class="mt-2 text-lg text-secondary">
                            {{ __('booking.page_subtitle') }}
                        </p>
                    </div>

                    {{-- Step 1: Date & Party Size --}}
                    <div class="bg-card rounded-2xl shadow-sm border border-default p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-primary mb-5 flex items-center gap-3">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center text-sm font-bold">1</span>
                            {{ __('booking.step_1.title') }}
                        </h2>

                        <form method="GET" action="{{ route('public.booking.show', $restaurant->booking_public_slug) }}">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                {{-- Date Input --}}
                                <div>
                                    <label for="check_date" class="block text-sm font-medium text-primary mb-2">
                                        {{ __('booking.step_1.date_label') }}
                                    </label>
                                    <input
                                        type="date"
                                        id="check_date"
                                        name="date"
                                        value="{{ $date }}"
                                        min="{{ $minDate }}"
                                        max="{{ $maxDate }}"
                                        required
                                        class="w-full px-4 py-3 rounded-xl border border-default bg-page text-primary focus:ring-2 focus:ring-brand focus:border-transparent transition"
                                    >
                                </div>

                                {{-- Party Size Input --}}
                                <div>
                                    <label for="check_party_size" class="block text-sm font-medium text-primary mb-2">
                                        {{ __('booking.step_1.party_size_label') }}
                                    </label>
                                    <input
                                        type="number"
                                        id="check_party_size"
                                        name="party_size"
                                        value="{{ $partySize }}"
                                        min="{{ $minPartySize }}"
                                        max="{{ $maxPartySize }}"
                                        required
                                        class="w-full px-4 py-3 rounded-xl border border-default bg-page text-primary focus:ring-2 focus:ring-brand focus:border-transparent transition"
                                    >
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
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                                @foreach($availability->slots as $slot)
                                    @if($slot->isBookable)
                                        @php $timeValue = $slot->getStartTime(); @endphp
                                        <label class="relative cursor-pointer">
                                            <input
                                                type="radio"
                                                name="time"
                                                value="{{ $timeValue }}"
                                                form="booking-form"
                                                class="sr-only peer"
                                                @checked(old('time', $selectedTime) === $timeValue)
                                            >
                                            <div class="rounded-xl border p-3 text-center transition-all duration-150
                                                        bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/50
                                                        hover:border-green-400 dark:hover:border-green-600
                                                        peer-checked:bg-brand peer-checked:text-white peer-checked:border-brand peer-checked:ring-2 peer-checked:ring-brand peer-checked:ring-offset-2">
                                                <span class="block text-lg font-semibold">{{ $timeValue }}</span>
                                                <span class="block text-xs mt-0.5 opacity-75">{{ __('booking.step_2.available') }}</span>
                                            </div>
                                            <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-brand rounded-full items-center justify-center ring-2 ring-white dark:ring-gray-900 hidden peer-checked:flex">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </span>
                                        </label>
                                    @else
                                        <div class="rounded-xl border border-default bg-gray-50 dark:bg-gray-800/50 p-3 text-center opacity-50 cursor-not-allowed">
                                            <span class="block text-lg font-semibold text-secondary">{{ $slot->getStartTime() }}</span>
                                            <span class="block text-xs mt-0.5 text-secondary">{{ __('booking.step_2.not_available') }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <div class="mt-5 pt-5 border-t border-default">
                                <p class="text-sm text-secondary">
                                    {{ __('booking.step_2.slots_summary', ['available' => $availability->bookableSlotCount(), 'total' => $availability->totalSlots()]) }}
                                </p>
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

                            {{-- Booking Summary --}}
                            <div class="mb-6 p-4 rounded-xl bg-brand/5 dark:bg-brand/10 border border-brand/20">
                                <h3 class="text-sm font-medium text-brand mb-2">{{ __('booking.step_3.summary_title') }}</h3>
                                <div class="flex flex-wrap gap-4 text-sm text-primary">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ \Carbon\Carbon::parse($date)->translatedFormat('D, j M') }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span id="selected-time-display">{{ $selectedTime }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $partySize }} {{ $partySize === 1 ? 'guest' : 'guests' }}
                                    </span>
                                </div>
                            </div>

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

                                    {{-- Deposit Info Box (shown when deposit is required) --}}
                                    @if($requiresDeposit)
                                        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50">
                                            <h4 class="font-semibold text-amber-800 dark:text-amber-300 flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ __('booking.deposit.title') }}
                                            </h4>
                                            <p class="text-amber-700 dark:text-amber-400 mt-2 text-sm">
                                                {{ __('booking.deposit.message', [
                                                    'threshold' => $depositThreshold,
                                                    'amount' => $formattedDepositAmount,
                                                    'type' => $depositType === 'fixed_per_person' ? __('booking.deposit.per_person') : __('booking.deposit.per_reservation')
                                                ]) }}
                                            </p>
                                            @if($depositPolicy)
                                                <p class="text-amber-600 dark:text-amber-500 mt-2 text-sm">
                                                    {{ $depositPolicy }}
                                                </p>
                                            @endif
                                            <p class="text-amber-600 dark:text-amber-500 mt-2 text-sm">
                                                {{ __('booking.deposit.payment_note') }}
                                            </p>
                                        </div>

                                        {{-- Deposit Consent Checkbox --}}
                                        <div class="flex items-start gap-3">
                                            <input
                                                type="checkbox"
                                                id="accepted_deposit"
                                                name="accepted_deposit"
                                                value="1"
                                                {{ old('accepted_deposit') ? 'checked' : '' }}
                                                class="mt-1 w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-brand focus:ring-brand dark:bg-gray-800 @error('accepted_deposit') border-red-500 @enderror"
                                            >
                                            <label for="accepted_deposit" class="text-sm text-primary">
                                                {{ __('booking.deposit.consent_label') }} <span class="text-red-500">*</span>
                                            </label>
                                        </div>
                                    @endif

                                    {{-- Terms & Privacy Checkbox --}}
                                    <div class="flex items-start gap-3">
                                        <input
                                            type="checkbox"
                                            id="accepted_terms"
                                            name="accepted_terms"
                                            value="1"
                                            {{ old('accepted_terms') ? 'checked' : '' }}
                                            class="mt-1 w-5 h-5 rounded border-gray-300 dark:border-gray-600 text-brand focus:ring-brand dark:bg-gray-800 @error('accepted_terms') border-red-500 @enderror"
                                        >
                                        <label for="accepted_terms" class="text-sm text-primary">
                                            {{ __('booking.step_3.terms_consent_label') }} <span class="text-red-500">*</span>
                                        </label>
                                    </div>

                                    {{-- Submit Button --}}
                                    <div class="pt-3">
                                        <button
                                            type="submit"
                                            class="w-full sm:w-auto px-8 py-3.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition focus:ring-2 focus:ring-brand focus:ring-offset-2 flex items-center justify-center gap-2"
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

                {{-- Right Column: Restaurant Info Card --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        {{-- Restaurant Card --}}
                        <div class="bg-card rounded-2xl shadow-sm border border-default p-6">
                            <h3 class="text-sm font-medium text-secondary uppercase tracking-wide mb-4">
                                {{ __('booking.restaurant_info.title') }}
                            </h3>

                            <div class="space-y-4">
                                {{-- Restaurant Name --}}
                                <div>
                                    <h4 class="text-xl font-bold text-primary">{{ $restaurant->name }}</h4>
                                    @if($restaurant->cuisine || $restaurant->city)
                                        <p class="text-secondary mt-1">
                                            @if($restaurant->cuisine)
                                                {{ $restaurant->cuisine->name }}
                                            @endif
                                            @if($restaurant->cuisine && $restaurant->city)
                                                &middot;
                                            @endif
                                            @if($restaurant->city)
                                                {{ $restaurant->city->name }}@if($restaurant->country), {{ $restaurant->country }}@endif
                                            @endif
                                        </p>
                                    @endif
                                </div>

                                {{-- Divider --}}
                                <hr class="border-default">

                                {{-- Booking Rules --}}
                                <div class="space-y-3 text-sm">
                                    <h5 class="font-medium text-primary">{{ __('booking.info.title') }}</h5>

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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Update time display when selection changes --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const timeRadios = document.querySelectorAll('input[name="time"]');
            const timeDisplay = document.getElementById('selected-time-display');

            if (timeRadios.length && timeDisplay) {
                timeRadios.forEach(radio => {
                    radio.addEventListener('change', () => {
                        timeDisplay.textContent = radio.value;
                    });
                });
            }
        });
    </script>
    @endpush
@endsection
