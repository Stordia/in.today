<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- SEO Variables --}}
    @php
        $currentLocale = app()->getLocale();
        $supportedLocales = config('locales.supported', ['en']);
        $defaultLocale = config('locales.default', 'en');
        $canonicalUrl = url($currentLocale);
    @endphp

    {{-- Primary Meta Tags --}}
    <title>{{ __('landing.meta.title') }}</title>
    <meta name="description" content="{{ __('landing.meta.description') }}">
    <meta name="robots" content="index,follow">
    <meta name="application-name" content="in.today">

    {{-- Canonical URL --}}
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Hreflang Tags for Internationalization --}}
    @foreach ($supportedLocales as $locale)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ url($locale) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url($defaultLocale) }}">

    {{-- OpenGraph Meta Tags --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="in.today">
    <meta property="og:title" content="{{ __('landing.meta.og_title') }}">
    <meta property="og:description" content="{{ __('landing.meta.og_description') }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:locale" content="{{ str_replace('-', '_', $currentLocale) }}">
    @foreach ($supportedLocales as $locale)
        @if ($locale !== $currentLocale)
    <meta property="og:locale:alternate" content="{{ str_replace('-', '_', $locale) }}">
        @endif
    @endforeach
    <meta property="og:image" content="{{ asset('img/og-in-today.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('landing.meta.og_title') }}">
    <meta name="twitter:description" content="{{ __('landing.meta.og_description') }}">
    <meta name="twitter:image" content="{{ asset('img/og-in-today.jpg') }}">

    {{-- JSON-LD Structured Data --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'in.today',
        'url' => $canonicalUrl,
        'logo' => asset('img/logo-in-today.png'),
        'description' => __('landing.meta.description'),
        'sameAs' => [
            'https://www.instagram.com/in.today.official',
            'https://www.linkedin.com/company/intoday',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-page text-primary">

    <!-- Navigation -->
    <nav class="fixed w-full bg-page shadow-sm z-50 border-b border-default">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <h1 class="text-2xl font-bold text-brand">{{ __('landing.nav.logo') }}</h1>
                </div>
                @php($baseUrl = '/' . $currentLocale)
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="{{ $baseUrl }}#features" class="text-secondary hover:text-brand transition">{{ __('landing.nav.solutions') }}</a>
                    <a href="{{ $baseUrl }}#pricing" class="text-secondary hover:text-brand transition">{{ __('landing.nav.pricing') }}</a>
                    <a href="{{ $baseUrl }}#how-it-works" class="text-secondary hover:text-brand transition">{{ __('landing.nav.how_it_works') }}</a>
                    <a href="{{ $baseUrl }}#faq" class="text-secondary hover:text-brand transition">{{ __('landing.nav.faq') }}</a>

                    <!-- Dark Mode Toggle -->
                    <button
                        id="theme-toggle"
                        type="button"
                        class="p-2 rounded-lg bg-card hover:bg-card-hover transition"
                        aria-label="{{ __('landing.nav.dark_mode') }}"
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

                    <a href="{{ $baseUrl }}#contact" class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-hover transition">{{ __('landing.nav.contact') }}</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 1. Hero Section -->
    <section id="hero" class="pt-24 pb-16 md:pt-32 md:pb-24 bg-gradient-to-br from-blue-50 to-page dark:from-slate-900 dark:to-page-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-semibold text-brand uppercase tracking-wide mb-4">{{ __('landing.hero.eyebrow') }}</p>
                <h2 class="text-4xl md:text-6xl font-extrabold text-primary mb-6">
                    {{ __('landing.hero.title') }}
                </h2>
                <p class="text-xl md:text-2xl text-secondary mb-8 max-w-3xl mx-auto">
                    {{ __('landing.hero.subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <div class="flex flex-col items-center">
                        <a href="#contact" class="px-8 py-4 bg-brand text-white text-lg font-semibold rounded-lg hover:bg-brand-hover transition shadow-lg">
                            {{ __('landing.hero.primary_cta') }}
                        </a>
                        <span class="text-sm text-secondary mt-2">{{ __('landing.hero.primary_cta_note') }}</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <a href="#pricing" class="px-8 py-4 bg-card text-brand text-lg font-semibold rounded-lg hover:bg-card-hover transition border-2 border-brand">
                            {{ __('landing.hero.secondary_cta') }}
                        </a>
                        <span class="text-sm text-secondary mt-2">{{ __('landing.hero.secondary_cta_note') }}</span>
                    </div>
                </div>
                <p class="text-sm text-muted mt-8">{{ __('landing.hero.trust_badge') }}</p>
            </div>
        </div>
    </section>

    <!-- 2. Who It's For Section -->
    <section id="who" class="py-16 md:py-24 bg-page">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    {{ __('landing.who.title') }}
                </h3>
                <p class="text-lg text-secondary max-w-2xl mx-auto">
                    {{ __('landing.who.subtitle') }}
                </p>
            </div>

            @php($whoItems = __('landing.who.items'))

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($whoItems as $index => $item)
                <div class="bg-card rounded-xl p-6 hover:shadow-md transition border border-default">
                    <div class="w-12 h-12 bg-brand-secondary rounded-lg flex items-center justify-center mb-4">
                        @if($index === 0)
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        @elseif($index === 1)
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        @elseif($index === 2)
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                        </svg>
                        @else
                        <svg class="w-6 h-6 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        @endif
                    </div>
                    <span class="text-xs font-semibold text-brand uppercase tracking-wide">{{ $item['label'] }}</span>
                    <h4 class="text-xl font-semibold text-primary mb-2 mt-1">{{ $item['title'] }}</h4>
                    <p class="text-secondary mb-3">{{ $item['description'] }}</p>
                    <span class="inline-block text-xs bg-brand-secondary text-brand px-2 py-1 rounded">{{ $item['tag'] }}</span>
                </div>
                @endforeach
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

            @php($features = __('landing.features.items'))

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($features as $feature)
                <div class="bg-card rounded-xl p-8 shadow-sm hover:shadow-md transition border border-default">
                    <h4 class="text-xl font-semibold text-primary mb-3">{{ $feature['title'] }}</h4>
                    <p class="text-secondary">{{ $feature['description'] }}</p>
                </div>
                @endforeach
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
                <p class="text-sm text-muted mt-4">
                    {{ __('landing.pricing.disclaimer') }}
                </p>
            </div>

            @php($plans = __('landing.pricing.plans'))

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                @foreach($plans as $plan)
                    @if(!empty($plan['highlight']) && $plan['highlight'])
                    <!-- Highlighted Plan -->
                    <div class="pricing-highlight rounded-2xl p-8 shadow-xl transform md:scale-105 relative">
                        <div class="absolute top-0 right-0 bg-accent text-white text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-2xl">
                            Most popular
                        </div>
                        <h4 class="text-2xl font-bold mb-2">{{ $plan['name'] }}</h4>
                        <p class="text-blue-100 dark:text-blue-200 mb-4">{{ $plan['tagline'] }}</p>
                        <div class="mb-2">
                            <span class="text-4xl font-bold">{{ $plan['price'] }}</span>
                            <span class="text-blue-100 dark:text-blue-200"> {{ $plan['price_note'] }}</span>
                        </div>
                        <p class="text-blue-100 dark:text-blue-200 text-sm mb-4">{{ $plan['billed'] }}</p>
                        <p class="text-blue-100 dark:text-blue-200 text-sm mb-6">{{ $plan['ideal_for'] }}</p>
                        @if(!empty($plan['features']))
                        <ul class="space-y-3 mb-8">
                            @foreach($plan['features'] as $line)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ $line }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                        <a href="#contact" class="block w-full text-center px-6 py-3 bg-white text-blue-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                            Get Started
                        </a>
                    </div>
                    @else
                    <!-- Regular Plan -->
                    <div class="bg-card border-2 border-default rounded-2xl p-8 hover:border-brand transition">
                        <h4 class="text-2xl font-bold text-primary mb-2">{{ $plan['name'] }}</h4>
                        <p class="text-secondary mb-4">{{ $plan['tagline'] }}</p>
                        <div class="mb-2">
                            <span class="text-4xl font-bold text-primary">{{ $plan['price'] }}</span>
                            <span class="text-secondary"> {{ $plan['price_note'] }}</span>
                        </div>
                        <p class="text-secondary text-sm mb-4">{{ $plan['billed'] }}</p>
                        <p class="text-secondary text-sm mb-6">{{ $plan['ideal_for'] }}</p>
                        @if(!empty($plan['features']))
                        <ul class="space-y-3 mb-8">
                            @foreach($plan['features'] as $line)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-secondary">{{ $line }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                        <a href="#contact" class="block w-full text-center px-6 py-3 bg-page-secondary text-primary font-semibold rounded-lg hover:bg-card-hover transition">
                            Get Started
                        </a>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <!-- 5. How It Works Section -->
    <section id="how-it-works" class="py-16 md:py-24 bg-page-secondary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    {{ __('landing.how.title') }}
                </h3>
                <p class="text-lg text-secondary max-w-2xl mx-auto">
                    {{ __('landing.how.subtitle') }}
                </p>
            </div>

            @php($steps = __('landing.how.steps'))

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($steps as $index => $step)
                <div class="relative">
                    <div class="bg-card rounded-xl p-6 shadow-sm hover:shadow-md transition h-full border border-default">
                        <div class="w-12 h-12 bg-brand text-white rounded-full flex items-center justify-center text-xl font-bold mb-4">
                            {{ $index + 1 }}
                        </div>
                        <span class="text-xs font-semibold text-brand uppercase tracking-wide">{{ $step['step'] }}</span>
                        <h4 class="text-xl font-semibold text-primary mb-3 mt-1">{{ $step['title'] }}</h4>
                        <p class="text-secondary">
                            {{ $step['description'] }}
                        </p>
                    </div>
                    @if(!$loop->last)
                    <div class="hidden lg:block absolute top-1/2 right-0 transform translate-x-1/2 -translate-y-1/2">
                        <svg class="w-8 h-8 text-blue-300 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    @endif
                </div>
                @endforeach
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

            @php($faqs = __('landing.faq.items'))

            <div class="space-y-6">
                @foreach($faqs as $faq)
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
        </div>
    </section>

    <!-- 7. Contact Section -->
    <section id="contact" class="py-16 md:py-24 bg-gradient-to-br from-blue-50 to-page dark:from-slate-900 dark:to-page-bg">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                    {{ __('landing.contact.title') }}
                </h3>
                <p class="text-lg text-secondary">
                    {{ __('landing.contact.subtitle') }}
                </p>
            </div>

            {{-- Success Message --}}
            @if (session('success'))
            <div class="mb-8 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-6 py-4 rounded-xl" role="alert">
                <p class="font-medium">{{ session('success') }}</p>
            </div>
            @endif

            {{-- Error Message --}}
            @if (session('error'))
            <div class="mb-8 bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-6 py-4 rounded-xl" role="alert">
                <p class="font-medium">{{ session('error') }}</p>
            </div>
            @endif

            <div class="bg-card rounded-2xl shadow-xl p-8 md:p-12 border border-default">
                <form action="{{ route('contact.submit', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-8">
                    @csrf

                    {{-- Honeypot field (hidden from real users) --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website_confirm">Leave this field empty</label>
                        <input type="text" name="website_confirm" id="website_confirm" tabindex="-1" autocomplete="off">
                    </div>

                    {{-- Contact Information --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-primary mb-2">
                                {{ __('landing.contact.form.name') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                placeholder="{{ __('landing.contact.form.name_placeholder') }}"
                                class="w-full px-4 py-3 rounded-lg border border-default bg-page text-primary placeholder-muted focus:ring-2 focus:ring-brand focus:border-brand transition @error('name') border-red-500 @enderror"
                                required
                            >
                            @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-primary mb-2">
                                {{ __('landing.contact.form.email') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                placeholder="{{ __('landing.contact.form.email_placeholder') }}"
                                class="w-full px-4 py-3 rounded-lg border border-default bg-page text-primary placeholder-muted focus:ring-2 focus:ring-brand focus:border-brand transition @error('email') border-red-500 @enderror"
                                required
                            >
                            @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-sm font-medium text-primary mb-2">
                                {{ __('landing.contact.form.phone') }}
                            </label>
                            <input
                                type="tel"
                                name="phone"
                                id="phone"
                                value="{{ old('phone') }}"
                                placeholder="{{ __('landing.contact.form.phone_placeholder') }}"
                                class="w-full px-4 py-3 rounded-lg border border-default bg-page text-primary placeholder-muted focus:ring-2 focus:ring-brand focus:border-brand transition"
                            >
                        </div>

                        {{-- Restaurant Name --}}
                        <div>
                            <label for="restaurant_name" class="block text-sm font-medium text-primary mb-2">
                                {{ __('landing.contact.form.restaurant_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="restaurant_name"
                                id="restaurant_name"
                                value="{{ old('restaurant_name') }}"
                                placeholder="{{ __('landing.contact.form.restaurant_name_placeholder') }}"
                                class="w-full px-4 py-3 rounded-lg border border-default bg-page text-primary placeholder-muted focus:ring-2 focus:ring-brand focus:border-brand transition @error('restaurant_name') border-red-500 @enderror"
                                required
                            >
                            @error('restaurant_name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- City --}}
                        <div>
                            <label for="city" class="block text-sm font-medium text-primary mb-2">
                                {{ __('landing.contact.form.city') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="city"
                                id="city"
                                value="{{ old('city') }}"
                                placeholder="{{ __('landing.contact.form.city_placeholder') }}"
                                class="w-full px-4 py-3 rounded-lg border border-default bg-page text-primary placeholder-muted focus:ring-2 focus:ring-brand focus:border-brand transition @error('city') border-red-500 @enderror"
                                required
                            >
                            @error('city')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Country --}}
                        <div>
                            <label for="country" class="block text-sm font-medium text-primary mb-2">
                                {{ __('landing.contact.form.country') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="country"
                                id="country"
                                value="{{ old('country') }}"
                                placeholder="{{ __('landing.contact.form.country_placeholder') }}"
                                class="w-full px-4 py-3 rounded-lg border border-default bg-page text-primary placeholder-muted focus:ring-2 focus:ring-brand focus:border-brand transition @error('country') border-red-500 @enderror"
                                required
                            >
                            @error('country')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Current Website --}}
                        <div>
                            <label for="website_url" class="block text-sm font-medium text-primary mb-2">
                                {{ __('landing.contact.form.website_url') }}
                            </label>
                            <input
                                type="url"
                                name="website_url"
                                id="website_url"
                                value="{{ old('website_url') }}"
                                placeholder="{{ __('landing.contact.form.website_url_placeholder') }}"
                                class="w-full px-4 py-3 rounded-lg border border-default bg-page text-primary placeholder-muted focus:ring-2 focus:ring-brand focus:border-brand transition"
                            >
                        </div>

                        {{-- Business Type --}}
                        <div>
                            <label for="type" class="block text-sm font-medium text-primary mb-2">
                                {{ __('landing.contact.form.type') }} <span class="text-red-500">*</span>
                            </label>
                            <select
                                name="type"
                                id="type"
                                class="w-full px-4 py-3 rounded-lg border border-default bg-page text-primary focus:ring-2 focus:ring-brand focus:border-brand transition @error('type') border-red-500 @enderror"
                                required
                            >
                                <option value="">--</option>
                                @foreach(__('landing.contact.form.type_options') as $value => $label)
                                <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Services Checkboxes --}}
                    <div>
                        <label class="block text-sm font-medium text-primary mb-2">
                            {{ __('landing.contact.form.services') }} <span class="text-red-500">*</span>
                        </label>
                        <p class="text-sm text-muted mb-3">{{ __('landing.contact.form.services_hint') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach(__('landing.contact.form.services_options') as $value => $label)
                            <label class="flex items-center space-x-3 p-3 rounded-lg border border-default bg-page hover:border-brand cursor-pointer transition">
                                <input
                                    type="checkbox"
                                    name="services[]"
                                    value="{{ $value }}"
                                    {{ is_array(old('services')) && in_array($value, old('services')) ? 'checked' : '' }}
                                    class="w-5 h-5 text-brand bg-page border-default rounded focus:ring-brand focus:ring-2"
                                >
                                <span class="text-secondary">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('services')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Budget Radio Buttons --}}
                    <div>
                        <label class="block text-sm font-medium text-primary mb-2">
                            {{ __('landing.contact.form.budget') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach(__('landing.contact.form.budget_options') as $value => $label)
                            <label class="flex items-center space-x-3 p-3 rounded-lg border border-default bg-page hover:border-brand cursor-pointer transition">
                                <input
                                    type="radio"
                                    name="budget"
                                    value="{{ $value }}"
                                    {{ old('budget') === $value ? 'checked' : '' }}
                                    class="w-5 h-5 text-brand bg-page border-default focus:ring-brand focus:ring-2"
                                >
                                <span class="text-secondary">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('budget')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="message" class="block text-sm font-medium text-primary mb-2">
                            {{ __('landing.contact.form.message') }}
                        </label>
                        <textarea
                            name="message"
                            id="message"
                            rows="4"
                            placeholder="{{ __('landing.contact.form.message_placeholder') }}"
                            class="w-full px-4 py-3 rounded-lg border border-default bg-page text-primary placeholder-muted focus:ring-2 focus:ring-brand focus:border-brand transition resize-none"
                        >{{ old('message') }}</textarea>
                    </div>

                    {{-- Privacy Note --}}
                    <p class="text-sm text-muted">
                        {{ __('landing.contact.privacy_note') }}
                    </p>

                    {{-- Submit Button --}}
                    <div class="text-center">
                        <button
                            type="submit"
                            class="px-8 py-4 bg-brand text-white text-lg font-semibold rounded-lg hover:bg-brand-hover transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ __('landing.contact.form.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-bg text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4">{{ __('landing.nav.logo') }}</h3>
                    <p class="footer-text">
                        {{ __('landing.footer.made_in') }}
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ $baseUrl }}#features" class="footer-text hover:text-white transition">{{ __('landing.nav.solutions') }}</a></li>
                        <li><a href="{{ $baseUrl }}#pricing" class="footer-text hover:text-white transition">{{ __('landing.nav.pricing') }}</a></li>
                        <li><a href="{{ $baseUrl }}#how-it-works" class="footer-text hover:text-white transition">{{ __('landing.nav.how_it_works') }}</a></li>
                        <li><a href="{{ $baseUrl }}#faq" class="footer-text hover:text-white transition">{{ __('landing.nav.faq') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2 footer-text">
                        <li>Email: <a href="mailto:{{ __('landing.contact.email_value') }}" class="hover:text-white transition">{{ __('landing.contact.email_value') }}</a></li>
                    </ul>
                    <ul class="mt-4 space-y-2 footer-text">
                        <li><a href="{{ route('imprint', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition">{{ __('landing.footer.links.imprint') }}</a></li>
                        <li><a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="hover:text-white transition">{{ __('landing.footer.links.privacy') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-700 text-center footer-text">
                <p>{{ __('landing.footer.copyright') }}</p>
            </div>
        </div>
    </footer>

</body>
</html>
