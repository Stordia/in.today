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
    @endphp

    {{-- Page Title --}}
    <title>@yield('title', 'in.today')</title>

    {{-- Meta Tags --}}
    <meta name="description" content="@yield('meta_description', '')">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <meta name="application-name" content="in.today">

    {{-- Canonical URL --}}
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif

    {{-- Hreflang Tags for Internationalization --}}
    @stack('hreflang')

    {{-- OpenGraph Meta Tags --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="in.today">
    <meta property="og:title" content="@yield('og_title')@yield('title')">
    <meta property="og:description" content="@yield('og_description')@yield('meta_description')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
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
    <meta name="twitter:title" content="@yield('og_title')@yield('title')">
    <meta name="twitter:description" content="@yield('og_description')@yield('meta_description')">
    <meta name="twitter:image" content="{{ asset('img/og-in-today.jpg') }}">

    {{-- Additional Head Content --}}
    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Analytics Scripts --}}
    @include('partials.analytics')
</head>
<body class="antialiased bg-page text-primary">

    {{-- Minimal Venue Header - No marketing navigation, focus on venue content --}}
    <nav class="fixed w-full bg-page/95 backdrop-blur-sm shadow-sm z-50 border-b border-default">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <div class="flex-shrink-0">
                    <a href="{{ route('root') }}" class="text-xl font-bold text-brand hover:opacity-80 transition">
                        in.today
                    </a>
                </div>

                {{-- Right Side: Theme Toggle + For Venues Link --}}
                <div class="flex items-center space-x-3">
                    {{-- Dark Mode Toggle --}}
                    <button
                        id="theme-toggle"
                        type="button"
                        class="p-2 rounded-lg text-secondary hover:text-brand hover:bg-card transition"
                        aria-label="Toggle dark mode"
                    >
                        <svg data-theme-icon="light" class="hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg data-theme-icon="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>

                    {{-- For Venues Link (subtle) - TODO: Update to business landing page when available --}}
                    <a
                        href="{{ route('root') }}"
                        class="hidden sm:inline-flex text-sm font-medium text-secondary hover:text-brand transition"
                    >
                        For venues
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content - Padding to account for fixed header --}}
    <main class="pt-16">
        @yield('content')
    </main>

    {{-- Cookie Consent Banner --}}
    @include('partials.cookie-banner')

    {{-- Additional Scripts --}}
    @stack('scripts')

</body>
</html>
