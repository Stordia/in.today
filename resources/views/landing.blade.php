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

    {{-- Analytics Scripts --}}
    @include('partials.analytics')
</head>
<body class="antialiased bg-page text-primary">

    <!-- Navigation -->
    <nav class="fixed w-full bg-page/95 backdrop-blur-sm shadow-sm z-50 border-b border-default">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ '/' . $currentLocale }}" class="text-xl font-bold text-brand hover:opacity-80 transition">
                        {{ __('landing.nav.logo') }}
                    </a>
                </div>

                @php($baseUrl = '/' . $currentLocale)

                <!-- Centered Nav Links (Desktop) -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ $baseUrl }}#features" class="text-sm font-medium text-secondary hover:text-brand transition">{{ __('landing.nav.features') }}</a>
                    <a href="{{ $baseUrl }}#pricing" class="text-sm font-medium text-secondary hover:text-brand transition">{{ __('landing.nav.pricing') }}</a>
                    <a href="{{ $baseUrl }}#how-it-works" class="text-sm font-medium text-secondary hover:text-brand transition">{{ __('landing.nav.how_it_works') }}</a>
                    <a href="{{ $baseUrl }}#faq" class="text-sm font-medium text-secondary hover:text-brand transition">{{ __('landing.nav.faq') }}</a>
                </div>

                <!-- Right Side: Theme + CTA -->
                <div class="flex items-center space-x-3">
                    <!-- Dark Mode Toggle -->
                    <button
                        id="theme-toggle"
                        type="button"
                        class="p-2 rounded-lg text-secondary hover:text-brand hover:bg-card transition"
                        aria-label="{{ __('landing.nav.dark_mode') }}"
                    >
                        <svg data-theme-icon="light" class="hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg data-theme-icon="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>

                    <!-- Contact CTA -->
                    <a href="{{ $baseUrl }}#contact" class="hidden sm:inline-flex px-4 py-2 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-brand-hover transition">
                        {{ __('landing.nav.contact') }}
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- 1. Hero Section -->
    <section id="hero" class="pt-24 pb-12 md:pt-28 md:pb-16 hero-gradient">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                <!-- Text Content -->
                <div class="text-center lg:text-left">
                    <p class="text-sm font-semibold text-brand uppercase tracking-wide mb-3">{{ __('landing.hero.eyebrow') }}</p>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-primary mb-4 leading-tight">
                        {{ __('landing.hero.title') }}
                    </h1>
                    <p class="text-lg text-secondary mb-6 max-w-xl mx-auto lg:mx-0">
                        {{ __('landing.hero.subtitle') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start mb-4">
                        <div class="flex flex-col items-center sm:items-start">
                            <a href="#contact" class="w-full sm:w-auto px-6 py-3 bg-brand text-white font-semibold rounded-lg hover:bg-brand-hover transition shadow-md text-center btn-cta">
                                {{ __('landing.hero.primary_cta') }}
                            </a>
                            <span class="text-xs text-muted mt-1.5">{{ __('landing.hero.primary_cta_note') }}</span>
                        </div>
                        <div class="flex flex-col items-center sm:items-start">
                            <a href="#pricing" class="w-full sm:w-auto px-6 py-3 bg-card text-brand font-semibold rounded-lg hover:bg-card-hover transition border border-brand text-center btn-cta">
                                {{ __('landing.hero.secondary_cta') }}
                            </a>
                            <span class="text-xs text-muted mt-1.5">{{ __('landing.hero.secondary_cta_note') }}</span>
                        </div>
                    </div>
                    <p class="text-xs text-muted">{{ __('landing.hero.trust_badge') }}</p>
                </div>

                <!-- Hero Illustration - Fake Website Preview -->
                <div class="hidden lg:block">
                    <div class="relative">
                        <!-- Browser Chrome -->
                        <div class="bg-card rounded-xl shadow-2xl border border-default overflow-hidden">
                            <!-- Browser Header -->
                            <div class="bg-page-secondary px-4 py-3 flex items-center space-x-2 border-b border-default">
                                <div class="flex space-x-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                </div>
                                <div class="flex-1 mx-4">
                                    <div class="bg-card rounded px-3 py-1 text-xs text-muted text-center border border-default">
                                        trattoria-esempio.in.today
                                    </div>
                                </div>
                            </div>
                            <!-- Website Content Preview -->
                            <div class="p-4 space-y-4">
                                <!-- Nav bar -->
                                <div class="flex justify-between items-center">
                                    <div class="w-20 h-4 bg-brand rounded"></div>
                                    <div class="flex space-x-3">
                                        <div class="w-10 h-3 bg-page-secondary rounded"></div>
                                        <div class="w-10 h-3 bg-page-secondary rounded"></div>
                                        <div class="w-10 h-3 bg-page-secondary rounded"></div>
                                    </div>
                                </div>
                                <!-- Hero area -->
                                <div class="bg-brand/10 rounded-lg p-6 text-center">
                                    <div class="w-32 h-4 bg-brand/30 rounded mx-auto mb-2"></div>
                                    <div class="w-48 h-3 bg-page-secondary rounded mx-auto mb-3"></div>
                                    <div class="w-24 h-6 bg-brand rounded mx-auto"></div>
                                </div>
                                <!-- Menu section -->
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="bg-page-secondary rounded p-2">
                                        <div class="w-full h-12 bg-card rounded mb-2"></div>
                                        <div class="w-16 h-2 bg-page-secondary rounded"></div>
                                    </div>
                                    <div class="bg-page-secondary rounded p-2">
                                        <div class="w-full h-12 bg-card rounded mb-2"></div>
                                        <div class="w-14 h-2 bg-page-secondary rounded"></div>
                                    </div>
                                    <div class="bg-page-secondary rounded p-2">
                                        <div class="w-full h-12 bg-card rounded mb-2"></div>
                                        <div class="w-12 h-2 bg-page-secondary rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Floating accent elements -->
                        <div class="absolute -top-3 -right-3 w-16 h-16 bg-accent/20 rounded-full blur-xl"></div>
                        <div class="absolute -bottom-3 -left-3 w-20 h-20 bg-brand/20 rounded-full blur-xl"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Who It's For Section -->
    <section id="who" class="py-14 md:py-20 bg-page">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-primary mb-3">
                    {{ __('landing.who.title') }}
                </h2>
                <p class="text-base text-secondary max-w-2xl mx-auto">
                    {{ __('landing.who.subtitle') }}
                </p>
            </div>

            @php($whoItems = __('landing.who.items'))

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($whoItems as $index => $item)
                <div class="bg-card rounded-xl p-5 hover:shadow-md transition border border-default group">
                    <div class="w-10 h-10 bg-brand-secondary rounded-lg flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        @if($index === 0)
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        @elseif($index === 1)
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        @elseif($index === 2)
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        @endif
                    </div>
                    <span class="text-xs font-semibold text-brand uppercase tracking-wide">{{ $item['label'] }}</span>
                    <h3 class="text-lg font-semibold text-primary mb-1.5 mt-1">{{ $item['title'] }}</h3>
                    <p class="text-sm text-secondary mb-2">{{ $item['description'] }}</p>
                    <span class="inline-block text-xs bg-brand-secondary text-brand px-2 py-0.5 rounded">{{ $item['tag'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 3. What We Deliver Section -->
    <section id="features" class="py-14 md:py-20 bg-page-secondary">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-primary mb-3">
                    {{ __('landing.features.title') }}
                </h2>
                <p class="text-base text-secondary max-w-2xl mx-auto">
                    {{ __('landing.features.subtitle') }}
                </p>
            </div>

            @php($features = __('landing.features.items'))

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($features as $feature)
                <div class="bg-card rounded-xl p-6 shadow-sm hover:shadow-md transition border border-default">
                    <h3 class="text-lg font-semibold text-primary mb-2">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-secondary">{{ $feature['description'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 4. Pricing Section -->
    <section id="pricing" class="py-14 md:py-20 bg-page">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-primary mb-3">
                    {{ __('landing.pricing.title') }}
                </h2>
                <p class="text-base text-secondary max-w-2xl mx-auto">
                    {{ __('landing.pricing.subtitle') }}
                </p>
                <p class="text-xs text-muted mt-3">
                    {{ __('landing.pricing.disclaimer') }}
                </p>
            </div>

            @php($plans = __('landing.pricing.plans'))

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-5xl mx-auto items-stretch">
                @foreach($plans as $plan)
                    @if(!empty($plan['highlight']) && $plan['highlight'])
                    <!-- Highlighted Plan -->
                    <div class="bg-card rounded-2xl p-6 shadow-lg border-2 border-brand relative flex flex-col pricing-card pricing-card-featured">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-accent text-white text-xs font-bold px-3 py-1 rounded-full">
                            {{ __('landing.pricing.popular_badge') ?? 'Most popular' }}
                        </div>
                        <div class="pt-2">
                            <h3 class="text-xl font-bold text-primary mb-1">{{ $plan['name'] }}</h3>
                            <p class="text-sm text-secondary mb-3">{{ $plan['tagline'] }}</p>
                            <div class="mb-1">
                                <span class="text-3xl font-bold text-brand">{{ $plan['price'] }}</span>
                                <span class="text-sm text-secondary"> {{ $plan['price_note'] }}</span>
                            </div>
                            <p class="text-xs text-muted mb-2">{{ $plan['billed'] }}</p>
                            <p class="text-xs text-secondary mb-4">{{ $plan['ideal_for'] }}</p>
                        </div>
                        @if(!empty($plan['features']))
                        <ul class="space-y-2 mb-6 flex-grow">
                            @foreach($plan['features'] as $line)
                            <li class="flex items-start text-sm">
                                <svg class="w-4 h-4 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-secondary">{{ $line }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                        <a href="#contact" class="block w-full text-center px-5 py-2.5 bg-brand text-white font-semibold rounded-lg hover:bg-brand-hover transition">
                            {{ __('landing.pricing.cta') ?? 'Get Started' }}
                        </a>
                    </div>
                    @else
                    <!-- Regular Plan -->
                    <div class="bg-card border border-default rounded-2xl p-6 hover:border-brand transition flex flex-col pricing-card">
                        <h3 class="text-xl font-bold text-primary mb-1">{{ $plan['name'] }}</h3>
                        <p class="text-sm text-secondary mb-3">{{ $plan['tagline'] }}</p>
                        <div class="mb-1">
                            <span class="text-3xl font-bold text-primary">{{ $plan['price'] }}</span>
                            <span class="text-sm text-secondary"> {{ $plan['price_note'] }}</span>
                        </div>
                        <p class="text-xs text-muted mb-2">{{ $plan['billed'] }}</p>
                        <p class="text-xs text-secondary mb-4">{{ $plan['ideal_for'] }}</p>
                        @if(!empty($plan['features']))
                        <ul class="space-y-2 mb-6 flex-grow">
                            @foreach($plan['features'] as $line)
                            <li class="flex items-start text-sm">
                                <svg class="w-4 h-4 text-accent mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-secondary">{{ $line }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                        <a href="#contact" class="block w-full text-center px-5 py-2.5 bg-page-secondary text-primary font-semibold rounded-lg hover:bg-card-hover transition border border-default">
                            {{ __('landing.pricing.cta') ?? 'Get Started' }}
                        </a>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    <!-- 5. How It Works Section -->
    <section id="how-it-works" class="py-14 md:py-20 bg-page-secondary">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-primary mb-3">
                    {{ __('landing.how.title') }}
                </h2>
                <p class="text-base text-secondary max-w-2xl mx-auto">
                    {{ __('landing.how.subtitle') }}
                </p>
            </div>

            @php($steps = __('landing.how.steps'))

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($steps as $index => $step)
                <div class="relative">
                    <div class="bg-card rounded-xl p-5 shadow-sm hover:shadow-md transition h-full border border-default">
                        <div class="w-10 h-10 bg-brand text-white rounded-full flex items-center justify-center text-lg font-bold mb-3">
                            {{ $index + 1 }}
                        </div>
                        <span class="text-xs font-semibold text-brand uppercase tracking-wide">{{ $step['step'] }}</span>
                        <h3 class="text-lg font-semibold text-primary mb-2 mt-1">{{ $step['title'] }}</h3>
                        <p class="text-sm text-secondary">
                            {{ $step['description'] }}
                        </p>
                    </div>
                    @if(!$loop->last)
                    <div class="hidden lg:flex absolute top-1/2 -right-2.5 transform -translate-y-1/2 z-10">
                        <svg class="w-5 h-5 text-brand/50" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 6. FAQ Section -->
    <section id="faq" class="py-14 md:py-20 bg-page">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-primary mb-3">
                    {{ __('landing.faq.title') }}
                </h2>
                <p class="text-base text-secondary">
                    {{ __('landing.faq.subtitle') }}
                </p>
            </div>

            @php($faqs = __('landing.faq.items'))

            <div class="space-y-4">
                @foreach($faqs as $faq)
                    <div class="bg-card rounded-xl p-5 border border-default">
                        <h3 class="text-base font-semibold text-primary mb-2">
                            {{ $faq['question'] }}
                        </h3>
                        <p class="text-sm text-secondary leading-relaxed">
                            {{ $faq['answer'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 7. Contact Section -->
    <section id="contact" class="py-14 md:py-20 hero-gradient">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl md:text-3xl font-bold text-primary mb-3">
                    {{ __('landing.contact.title') }}
                </h2>
                <p class="text-base text-secondary">
                    {{ __('landing.contact.subtitle') }}
                </p>
            </div>

            {{-- Success Message --}}
            @if (session('success'))
            <div class="mb-6 alert-success border px-5 py-4 rounded-xl flex items-start gap-3" role="alert">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            @endif

            {{-- Error Message --}}
            @if (session('error'))
            <div class="mb-6 alert-error border px-5 py-4 rounded-xl flex items-start gap-3" role="alert">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
            @endif

            <div class="bg-card rounded-2xl shadow-xl p-6 md:p-8 border border-default">
                <form id="contact-form" action="{{ route('contact.submit', ['locale' => app()->getLocale()]) }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- Honeypot field (hidden from real users) --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website_confirm">Leave this field empty</label>
                        <input type="text" name="website_confirm" id="website_confirm" tabindex="-1" autocomplete="off">
                    </div>

                    {{-- Contact Information --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-primary mb-1.5">
                                {{ __('landing.contact.form.name') }} <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                placeholder="{{ __('landing.contact.form.name_placeholder') }}"
                                class="w-full px-3 py-2.5 rounded-lg border border-default bg-page text-primary placeholder-muted text-sm focus:ring-2 focus:ring-brand focus:border-brand transition @error('name') border-error @enderror"
                                required
                            >
                            @error('name')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-primary mb-1.5">
                                {{ __('landing.contact.form.email') }} <span class="text-error">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                placeholder="{{ __('landing.contact.form.email_placeholder') }}"
                                class="w-full px-3 py-2.5 rounded-lg border border-default bg-page text-primary placeholder-muted text-sm focus:ring-2 focus:ring-brand focus:border-brand transition @error('email') border-error @enderror"
                                required
                            >
                            @error('email')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-sm font-medium text-primary mb-1.5">
                                {{ __('landing.contact.form.phone') }}
                            </label>
                            <input
                                type="tel"
                                name="phone"
                                id="phone"
                                value="{{ old('phone') }}"
                                placeholder="{{ __('landing.contact.form.phone_placeholder') }}"
                                class="w-full px-3 py-2.5 rounded-lg border border-default bg-page text-primary placeholder-muted text-sm focus:ring-2 focus:ring-brand focus:border-brand transition"
                            >
                        </div>

                        {{-- Restaurant Name --}}
                        <div>
                            <label for="restaurant_name" class="block text-sm font-medium text-primary mb-1.5">
                                {{ __('landing.contact.form.restaurant_name') }} <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                name="restaurant_name"
                                id="restaurant_name"
                                value="{{ old('restaurant_name') }}"
                                placeholder="{{ __('landing.contact.form.restaurant_name_placeholder') }}"
                                class="w-full px-3 py-2.5 rounded-lg border border-default bg-page text-primary placeholder-muted text-sm focus:ring-2 focus:ring-brand focus:border-brand transition @error('restaurant_name') border-error @enderror"
                                required
                            >
                            @error('restaurant_name')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- City --}}
                        <div>
                            <label for="city" class="block text-sm font-medium text-primary mb-1.5">
                                {{ __('landing.contact.form.city') }} <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                name="city"
                                id="city"
                                value="{{ old('city') }}"
                                placeholder="{{ __('landing.contact.form.city_placeholder') }}"
                                class="w-full px-3 py-2.5 rounded-lg border border-default bg-page text-primary placeholder-muted text-sm focus:ring-2 focus:ring-brand focus:border-brand transition @error('city') border-error @enderror"
                                required
                            >
                            @error('city')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Country --}}
                        <div>
                            <label for="country" class="block text-sm font-medium text-primary mb-1.5">
                                {{ __('landing.contact.form.country') }} <span class="text-error">*</span>
                            </label>
                            <input
                                type="text"
                                name="country"
                                id="country"
                                value="{{ old('country') }}"
                                placeholder="{{ __('landing.contact.form.country_placeholder') }}"
                                class="w-full px-3 py-2.5 rounded-lg border border-default bg-page text-primary placeholder-muted text-sm focus:ring-2 focus:ring-brand focus:border-brand transition @error('country') border-error @enderror"
                                required
                            >
                            @error('country')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Current Website --}}
                        <div>
                            <label for="website_url" class="block text-sm font-medium text-primary mb-1.5">
                                {{ __('landing.contact.form.website_url') }}
                            </label>
                            <input
                                type="url"
                                name="website_url"
                                id="website_url"
                                value="{{ old('website_url') }}"
                                placeholder="{{ __('landing.contact.form.website_url_placeholder') }}"
                                class="w-full px-3 py-2.5 rounded-lg border border-default bg-page text-primary placeholder-muted text-sm focus:ring-2 focus:ring-brand focus:border-brand transition"
                            >
                        </div>

                        {{-- Business Type --}}
                        <div>
                            <label for="type" class="block text-sm font-medium text-primary mb-1.5">
                                {{ __('landing.contact.form.type') }} <span class="text-error">*</span>
                            </label>
                            <select
                                name="type"
                                id="type"
                                class="w-full px-3 py-2.5 rounded-lg border border-default bg-page text-primary text-sm focus:ring-2 focus:ring-brand focus:border-brand transition @error('type') border-error @enderror"
                                required
                            >
                                <option value="">--</option>
                                @foreach(__('landing.contact.form.type_options') as $value => $label)
                                <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')
                            <p class="mt-1 text-xs text-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Services Checkboxes --}}
                    <div>
                        <label class="block text-sm font-medium text-primary mb-1.5">
                            {{ __('landing.contact.form.services') }} <span class="text-error">*</span>
                        </label>
                        <p class="text-xs text-muted mb-2">{{ __('landing.contact.form.services_hint') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach(__('landing.contact.form.services_options') as $value => $label)
                            <label class="flex items-center space-x-2.5 p-2.5 rounded-lg border border-default bg-page hover:border-brand cursor-pointer transition">
                                <input
                                    type="checkbox"
                                    name="services[]"
                                    value="{{ $value }}"
                                    {{ is_array(old('services')) && in_array($value, old('services')) ? 'checked' : '' }}
                                    class="w-4 h-4 text-brand bg-page border-default rounded focus:ring-brand focus:ring-2"
                                >
                                <span class="text-sm text-secondary">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('services')
                        <p class="mt-1.5 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Budget Radio Buttons --}}
                    <div>
                        <label class="block text-sm font-medium text-primary mb-1.5">
                            {{ __('landing.contact.form.budget') }} <span class="text-error">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach(__('landing.contact.form.budget_options') as $value => $label)
                            <label class="flex items-center space-x-2.5 p-2.5 rounded-lg border border-default bg-page hover:border-brand cursor-pointer transition">
                                <input
                                    type="radio"
                                    name="budget"
                                    value="{{ $value }}"
                                    {{ old('budget') === $value ? 'checked' : '' }}
                                    class="w-4 h-4 text-brand bg-page border-default focus:ring-brand focus:ring-2"
                                >
                                <span class="text-sm text-secondary">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('budget')
                        <p class="mt-1.5 text-xs text-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="message" class="block text-sm font-medium text-primary mb-1.5">
                            {{ __('landing.contact.form.message') }}
                        </label>
                        <textarea
                            name="message"
                            id="message"
                            rows="3"
                            placeholder="{{ __('landing.contact.form.message_placeholder') }}"
                            class="w-full px-3 py-2.5 rounded-lg border border-default bg-page text-primary placeholder-muted text-sm focus:ring-2 focus:ring-brand focus:border-brand transition resize-none"
                        >{{ old('message') }}</textarea>
                    </div>

                    {{-- Privacy Note --}}
                    <p class="text-xs text-muted">
                        {{ __('landing.contact.privacy_note') }}
                    </p>

                    {{-- Submit Button --}}
                    <div class="text-center">
                        <button
                            type="submit"
                            class="px-6 py-3 bg-brand text-white font-semibold rounded-lg hover:bg-brand-hover transition shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ __('landing.contact.form.submit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-bg text-white py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <h3 class="text-xl font-bold mb-3">{{ __('landing.nav.logo') }}</h3>
                    <p class="text-sm footer-text">
                        {{ __('landing.footer.made_in') }}
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-sm font-semibold mb-3 uppercase tracking-wide">{{ __('landing.footer.sections.quick_links') }}</h4>
                    <ul class="space-y-1.5 text-sm">
                        <li><a href="{{ $baseUrl }}#features" class="footer-text hover:text-white transition">{{ __('landing.nav.features') }}</a></li>
                        <li><a href="{{ $baseUrl }}#pricing" class="footer-text hover:text-white transition">{{ __('landing.nav.pricing') }}</a></li>
                        <li><a href="{{ $baseUrl }}#how-it-works" class="footer-text hover:text-white transition">{{ __('landing.nav.how_it_works') }}</a></li>
                        <li><a href="{{ $baseUrl }}#faq" class="footer-text hover:text-white transition">{{ __('landing.nav.faq') }}</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-sm font-semibold mb-3 uppercase tracking-wide">{{ __('landing.nav.contact') }}</h4>
                    <ul class="space-y-1.5 text-sm footer-text">
                        <li>
                            <a href="mailto:hello@in.today" class="hover:text-white transition">hello@in.today</a>
                        </li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h4 class="text-sm font-semibold mb-3 uppercase tracking-wide">{{ __('landing.footer.sections.legal') }}</h4>
                    <ul class="space-y-1.5 text-sm">
                        <li><a href="{{ route('imprint', ['locale' => app()->getLocale()]) }}" class="footer-text hover:text-white transition">{{ __('landing.footer.links.imprint') }}</a></li>
                        <li><a href="{{ route('privacy', ['locale' => app()->getLocale()]) }}" class="footer-text hover:text-white transition">{{ __('landing.footer.links.privacy') }}</a></li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom Bar: Copyright + Country Selector -->
            <div class="mt-8 pt-6 border-t border-slate-700 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <p class="text-xs footer-text text-center md:text-left">{{ __('landing.footer.copyright') }}</p>

                <div class="flex items-center justify-center md:justify-end gap-3">
                    <label for="locale-switcher" class="text-xs footer-text">
                        {{ __('landing.footer.locale_label') }}
                    </label>
                    <select
                        id="locale-switcher"
                        class="text-sm bg-slate-800 border border-slate-600 rounded-lg px-3 py-1.5 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer"
                    >
                        <option value="en" @selected($currentLocale === 'en')>{{ __('landing.footer.locale_option_en') }}</option>
                        <option value="de" @selected($currentLocale === 'de')>{{ __('landing.footer.locale_option_de') }}</option>
                        <option value="el" @selected($currentLocale === 'el')>{{ __('landing.footer.locale_option_el') }}</option>
                        <option value="it" @selected($currentLocale === 'it')>{{ __('landing.footer.locale_option_it') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </footer>

    {{-- Cookie Consent Banner --}}
    @include('partials.cookie-banner')

    {{-- Contact Form Success Modal --}}
    <div
        id="contact-success-modal"
        class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;"
        role="dialog"
        aria-modal="true"
        aria-hidden="true"
        aria-labelledby="contact-success-title"
    >
        <div class="bg-card rounded-2xl shadow-2xl p-8 max-w-md w-full text-center border border-default">
            <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 id="contact-success-title" class="text-xl font-bold text-primary mb-2">
                {{ __('landing.contact.modal_title') }}
            </h3>
            <p id="contact-success-message" class="text-secondary mb-6">
                {{ __('landing.contact.modal_body') }}
            </p>
            <button
                type="button"
                data-close-modal
                class="px-6 py-2.5 bg-brand text-white font-semibold rounded-lg hover:bg-brand-hover transition"
            >
                {{ __('landing.contact.modal_close') }}
            </button>
        </div>
    </div>

</body>
</html>
