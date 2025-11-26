<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('landing.meta.title') }}</title>
    <meta name="description" content="{{ __('landing.meta.description') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-page text-primary">

    <!-- Navigation -->
    <nav class="fixed w-full bg-page shadow-sm z-50 border-b border-default">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <h1 class="text-2xl font-bold text-brand">{{ __('landing.navigation.brand') }}</h1>
                </div>
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="#features" class="text-secondary hover:text-brand transition">{{ __('landing.navigation.features') }}</a>
                    <a href="#pricing" class="text-secondary hover:text-brand transition">{{ __('landing.navigation.pricing') }}</a>
                    <a href="#how-it-works" class="text-secondary hover:text-brand transition">{{ __('landing.navigation.how_it_works') }}</a>
                    <a href="#faq" class="text-secondary hover:text-brand transition">{{ __('landing.navigation.faq') }}</a>

                    <!-- Dark Mode Toggle -->
                    <button
                        id="theme-toggle"
                        type="button"
                        class="p-2 rounded-lg bg-card hover:bg-card-hover transition"
                        aria-label="{{ __('landing.navigation.theme_toggle_aria') }}"
                    >
                        <!-- Sun Icon (shown in dark mode) -->
                        <svg data-theme-icon="light" class="hidden w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <!-- Moon Icon (shown in light mode) -->
                        <svg data-theme-icon="dark" class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>

                    <!-- Language Switcher -->
                    <div id="language-switcher" class="flex items-center space-x-2 text-sm">
                        @foreach(config('locales.supported') as $locale)
                            <a
                                href="/{{ $locale }}"
                                data-locale="{{ $locale }}"
                                class="px-2 py-1 rounded hover:bg-card-hover transition {{ app()->getLocale() === $locale ? 'font-bold text-brand' : 'text-secondary' }}"
                                {{ app()->getLocale() === $locale ? 'aria-current=true' : '' }}
                            >
                                {{ strtoupper($locale) }}
                            </a>
                            @if(!$loop->last)
                                <span class="text-muted">|</span>
                            @endif
                        @endforeach
                    </div>

                    <a href="#contact" class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-hover transition">{{ __('landing.navigation.contact') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 1. Hero Section -->
    <section id="hero" class="pt-24 pb-16 md:pt-32 md:pb-24 bg-gradient-to-br from-indigo-100 to-page dark:from-indigo-950 dark:to-page-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-4xl md:text-6xl font-extrabold text-primary mb-6">
                    {{ __('landing.hero.title') }}
                    <span class="text-brand">{{ __('landing.hero.title_highlight') }}</span>
                </h2>
                <p class="text-xl md:text-2xl text-secondary mb-8 max-w-3xl mx-auto">
                    {{ __('landing.hero.subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#pricing" class="px-8 py-4 bg-brand text-white text-lg font-semibold rounded-lg hover:bg-brand-hover transition shadow-lg">
                        {{ __('landing.hero.cta_primary') }}
                    </a>
                    <a href="#contact" class="px-8 py-4 bg-card text-brand text-lg font-semibold rounded-lg hover:bg-card-hover transition border-2 border-brand">
                        {{ __('landing.hero.cta_secondary') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Who It's For Section -->
    <section id="who-its-for" class="py-16 md:py-24 bg-page">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    {{ __('landing.who_its_for.title') }}
                </h3>
                <p class="text-lg text-secondary max-w-2xl mx-auto">
                    {{ __('landing.who_its_for.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Small Local Restaurants -->
                <div class="bg-card rounded-xl p-6 hover:shadow-md transition border border-default">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-primary mb-2">{{ __('landing.who_its_for.small_restaurants.title') }}</h4>
                    <p class="text-secondary">{{ __('landing.who_its_for.small_restaurants.description') }}</p>
                </div>

                <!-- Casual Dining & Bistros -->
                <div class="bg-card rounded-xl p-6 hover:shadow-md transition border border-default">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-primary mb-2">{{ __('landing.who_its_for.casual_dining.title') }}</h4>
                    <p class="text-secondary">{{ __('landing.who_its_for.casual_dining.description') }}</p>
                </div>

                <!-- Bars & Clubs -->
                <div class="bg-card rounded-xl p-6 hover:shadow-md transition border border-default">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-primary mb-2">{{ __('landing.who_its_for.bars_clubs.title') }}</h4>
                    <p class="text-secondary">{{ __('landing.who_its_for.bars_clubs.description') }}</p>
                </div>

                <!-- Fine Dining -->
                <div class="bg-card rounded-xl p-6 hover:shadow-md transition border border-default">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-primary mb-2">{{ __('landing.who_its_for.fine_dining.title') }}</h4>
                    <p class="text-secondary">{{ __('landing.who_its_for.fine_dining.description') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. What We Deliver Section -->
    <section id="features" class="py-16 md:py-24 bg-page-secondary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    {{ __('landing.features.title') }}
                </h3>
                <p class="text-lg text-secondary max-w-2xl mx-auto">
                    {{ __('landing.features.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Custom Website -->
                <div class="bg-card rounded-xl p-8 shadow-sm hover:shadow-md transition border border-default">
                    <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.features.custom_website.title') }}</h4>
                    <p class="text-secondary mb-4">{{ __('landing.features.custom_website.description') }}</p>
                    <ul class="space-y-2 text-secondary">
                        @foreach(trans('landing.features.custom_website.items') as $item)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Domain & Hosting -->
                <div class="bg-card rounded-xl p-8 shadow-sm hover:shadow-md transition border border-default">
                    <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.features.domain_hosting.title') }}</h4>
                    <p class="text-secondary mb-4">{{ __('landing.features.domain_hosting.description') }}</p>
                    <ul class="space-y-2 text-secondary">
                        @foreach(trans('landing.features.domain_hosting.items') as $item)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Menu & SEO -->
                <div class="bg-card rounded-xl p-8 shadow-sm hover:shadow-md transition border border-default">
                    <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.features.menu_pages.title') }}</h4>
                    <p class="text-secondary mb-4">{{ __('landing.features.menu_pages.description') }}</p>
                    <ul class="space-y-2 text-secondary">
                        @foreach(trans('landing.features.menu_pages.items') as $item)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Photography -->
                <div class="bg-card rounded-xl p-8 shadow-sm hover:shadow-md transition border border-default">
                    <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.features.photography.title') }}</h4>
                    <p class="text-secondary mb-4">{{ __('landing.features.photography.description') }}</p>
                    <ul class="space-y-2 text-secondary">
                        @foreach(trans('landing.features.photography.items') as $item)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Reservations -->
                <div class="bg-card rounded-xl p-8 shadow-sm hover:shadow-md transition border border-default">
                    <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.features.reservations.title') }}</h4>
                    <p class="text-secondary mb-4">{{ __('landing.features.reservations.description') }}</p>
                    <ul class="space-y-2 text-secondary">
                        @foreach(trans('landing.features.reservations.items') as $item)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Support -->
                <div class="bg-card rounded-xl p-8 shadow-sm hover:shadow-md transition border border-default">
                    <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.features.support.title') }}</h4>
                    <p class="text-secondary mb-4">{{ __('landing.features.support.description') }}</p>
                    <ul class="space-y-2 text-secondary">
                        @foreach(trans('landing.features.support.items') as $item)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Pricing Section -->
    <section id="pricing" class="py-16 md:py-24 bg-page">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    {{ __('landing.pricing.title') }}
                </h3>
                <p class="text-lg text-secondary max-w-2xl mx-auto">
                    {{ __('landing.pricing.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Base Plan -->
                <div class="bg-card border-2 border-default rounded-2xl p-8 hover:border-brand transition">
                    <h4 class="text-2xl font-bold text-primary mb-2">{{ __('landing.pricing.base.name') }}</h4>
                    <p class="text-secondary mb-6">{{ __('landing.pricing.base.tagline') }}</p>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-primary">{{ __('landing.pricing.base.launch_fee') }}</span>
                        <span class="text-secondary"> {{ __('landing.pricing.base.launch_fee_label') }}</span>
                    </div>
                    <div class="mb-6">
                        <span class="text-2xl font-bold text-primary">{{ __('landing.pricing.base.monthly') }}</span>
                        <span class="text-secondary">{{ __('landing.pricing.base.monthly_label') }}</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        @foreach(trans('landing.pricing.base.features') as $feature)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-secondary">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="#contact" class="block w-full text-center px-6 py-3 bg-page-secondary text-primary font-semibold rounded-lg hover:bg-card-hover transition">
                        {{ __('landing.pricing.base.cta') }}
                    </a>
                </div>

                <!-- Pro Plan (Featured) -->
                <div class="bg-indigo-600 dark:bg-indigo-700 text-white rounded-2xl p-8 shadow-xl transform md:scale-105 relative">
                    <div class="absolute top-0 right-0 bg-yellow-400 text-gray-900 dark:text-slate-900 text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-2xl">
                        {{ __('landing.pricing.pro.badge') }}
                    </div>
                    <h4 class="text-2xl font-bold mb-2">{{ __('landing.pricing.pro.name') }}</h4>
                    <p class="text-indigo-100 dark:text-indigo-200 mb-6">{{ __('landing.pricing.pro.tagline') }}</p>
                    <div class="mb-6">
                        <span class="text-4xl font-bold">{{ __('landing.pricing.pro.launch_fee') }}</span>
                        <span class="text-indigo-100 dark:text-indigo-200"> {{ __('landing.pricing.pro.launch_fee_label') }}</span>
                    </div>
                    <div class="mb-6">
                        <span class="text-2xl font-bold">{{ __('landing.pricing.pro.monthly') }}</span>
                        <span class="text-indigo-100 dark:text-indigo-200">{{ __('landing.pricing.pro.monthly_label') }}</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        @foreach(trans('landing.pricing.pro.features') as $feature)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="#contact" class="block w-full text-center px-6 py-3 bg-white text-indigo-600 dark:text-indigo-700 font-semibold rounded-lg hover:bg-gray-100 dark:hover:bg-slate-100 transition">
                        {{ __('landing.pricing.pro.cta') }}
                    </a>
                </div>

                <!-- Prime Plan -->
                <div class="bg-card border-2 border-default rounded-2xl p-8 hover:border-brand transition">
                    <h4 class="text-2xl font-bold text-primary mb-2">{{ __('landing.pricing.prime.name') }}</h4>
                    <p class="text-secondary mb-6">{{ __('landing.pricing.prime.tagline') }}</p>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-primary">{{ __('landing.pricing.prime.launch_fee') }}</span>
                        <span class="text-secondary"> {{ __('landing.pricing.prime.launch_fee_label') }}</span>
                    </div>
                    <div class="mb-6">
                        <span class="text-2xl font-bold text-primary">{{ __('landing.pricing.prime.monthly') }}</span>
                        <span class="text-secondary">{{ __('landing.pricing.prime.monthly_label') }}</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        @foreach(trans('landing.pricing.prime.features') as $feature)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-secondary">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="#contact" class="block w-full text-center px-6 py-3 bg-page-secondary text-primary font-semibold rounded-lg hover:bg-card-hover transition">
                        {{ __('landing.pricing.prime.cta') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. How It Works Section -->
    <section id="how-it-works" class="py-16 md:py-24 bg-page-secondary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    {{ __('landing.how_it_works.title') }}
                </h3>
                <p class="text-lg text-secondary max-w-2xl mx-auto">
                    {{ __('landing.how_it_works.subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="relative">
                    <div class="bg-card rounded-xl p-6 shadow-sm hover:shadow-md transition h-full border border-default">
                        <div class="w-12 h-12 bg-brand text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">
                            1
                        </div>
                        <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.how_it_works.step1.title') }}</h4>
                        <p class="text-secondary">
                            {{ __('landing.how_it_works.step1.description') }}
                        </p>
                    </div>
                    <div class="hidden lg:block absolute top-1/2 right-0 transform translate-x-1/2 -translate-y-1/2">
                        <svg class="w-8 h-8 text-indigo-300 dark:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative">
                    <div class="bg-card rounded-xl p-6 shadow-sm hover:shadow-md transition h-full border border-default">
                        <div class="w-12 h-12 bg-brand text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">
                            2
                        </div>
                        <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.how_it_works.step2.title') }}</h4>
                        <p class="text-secondary">
                            {{ __('landing.how_it_works.step2.description') }}
                        </p>
                    </div>
                    <div class="hidden lg:block absolute top-1/2 right-0 transform translate-x-1/2 -translate-y-1/2">
                        <svg class="w-8 h-8 text-indigo-300 dark:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative">
                    <div class="bg-card rounded-xl p-6 shadow-sm hover:shadow-md transition h-full border border-default">
                        <div class="w-12 h-12 bg-brand text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">
                            3
                        </div>
                        <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.how_it_works.step3.title') }}</h4>
                        <p class="text-secondary">
                            {{ __('landing.how_it_works.step3.description') }}
                        </p>
                    </div>
                    <div class="hidden lg:block absolute top-1/2 right-0 transform translate-x-1/2 -translate-y-1/2">
                        <svg class="w-8 h-8 text-indigo-300 dark:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>

                <!-- Step 4 -->
                <div>
                    <div class="bg-card rounded-xl p-6 shadow-sm hover:shadow-md transition h-full border border-default">
                        <div class="w-12 h-12 bg-brand text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">
                            4
                        </div>
                        <h4 class="text-xl font-semibold text-primary mb-3">{{ __('landing.how_it_works.step4.title') }}</h4>
                        <p class="text-secondary">
                            {{ __('landing.how_it_works.step4.description') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center">
                <p class="text-lg text-primary mb-4">
                    <strong>{{ __('landing.how_it_works.timeline') }}</strong> {{ __('landing.how_it_works.timeline_value') }}
                </p>
                <a href="#contact" class="inline-block px-8 py-3 bg-brand text-white font-semibold rounded-lg hover:bg-brand-hover transition shadow-md">
                    {{ __('landing.how_it_works.cta') }}
                </a>
            </div>
        </div>
    </section>

    <!-- 6. FAQ Section -->
    <section id="faq" class="py-16 md:py-24 bg-page">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    {{ __('landing.faq.title') }}
                </h3>
                <p class="text-lg text-secondary">
                    {{ __('landing.faq.subtitle') }}
                </p>
            </div>

            <div class="space-y-6">
                @foreach(trans('landing.faq.questions') as $faq)
                    <div class="bg-card rounded-xl p-6 border border-default">
                        <h4 class="text-xl font-semibold text-primary mb-2">
                            {{ $faq['question'] }}
                        </h4>
                        <p class="text-secondary">
                            {{ $faq['answer'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <p class="text-secondary mb-4">{{ __('landing.faq.still_questions') }}</p>
                <a href="#contact" class="inline-block px-8 py-3 bg-brand text-white font-semibold rounded-lg hover:bg-brand-hover transition">
                    {{ __('landing.faq.cta') }}
                </a>
            </div>
        </div>
    </section>

    <!-- 7. Contact Section -->
    <section id="contact" class="py-16 md:py-24 bg-gradient-to-br from-indigo-100 to-page dark:from-indigo-950 dark:to-page-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    {{ __('landing.contact.title') }}
                </h3>
                <p class="text-lg text-secondary">
                    {{ __('landing.contact.subtitle') }}
                </p>
            </div>

            <div class="bg-card rounded-2xl shadow-xl p-8 md:p-12 border border-default">
                <!-- Simple contact info for Phase 1 -->
                <div class="text-center space-y-6">
                    <div>
                        <h4 class="text-xl font-semibold text-primary mb-2">{{ __('landing.contact.email_title') }}</h4>
                        <a href="mailto:{{ __('landing.contact.email') }}" class="text-lg text-brand hover:text-brand-hover">
                            {{ __('landing.contact.email') }}
                        </a>
                    </div>

                    <div class="pt-6 border-t border-default">
                        <p class="text-secondary mb-6">
                            {{ __('landing.contact.call_prompt') }}
                        </p>
                        <a href="mailto:{{ __('landing.contact.email') }}?subject=Request for Call - in.today"
                           class="inline-block px-8 py-4 bg-brand text-white text-lg font-semibold rounded-lg hover:bg-brand-hover transition shadow-md">
                            {{ __('landing.contact.call_cta') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900 dark:bg-slate-950 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4">{{ __('landing.footer.brand') }}</h3>
                    <p class="text-slate-400 dark:text-slate-500">
                        {{ __('landing.footer.tagline') }}
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">{{ __('landing.footer.quick_links_title') }}</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-slate-400 dark:text-slate-500 hover:text-white transition">{{ __('landing.navigation.features') }}</a></li>
                        <li><a href="#pricing" class="text-slate-400 dark:text-slate-500 hover:text-white transition">{{ __('landing.navigation.pricing') }}</a></li>
                        <li><a href="#how-it-works" class="text-slate-400 dark:text-slate-500 hover:text-white transition">{{ __('landing.navigation.how_it_works') }}</a></li>
                        <li><a href="#faq" class="text-slate-400 dark:text-slate-500 hover:text-white transition">{{ __('landing.navigation.faq') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">{{ __('landing.footer.contact_title') }}</h4>
                    <ul class="space-y-2 text-slate-400 dark:text-slate-500">
                        <li>{{ __('landing.footer.email_label') }} <a href="mailto:{{ __('landing.contact.email') }}" class="hover:text-white transition">{{ __('landing.contact.email') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-800 dark:border-slate-900 text-center text-slate-400 dark:text-slate-500">
                <p>{{ __('landing.footer.copyright') }}</p>
            </div>
        </div>
    </footer>

</body>
</html>
