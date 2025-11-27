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
        $baseUrl = '/' . $currentLocale;
    @endphp

    {{-- Page Title --}}
    <title>@yield('title', __('landing.meta.title'))</title>

    {{-- Meta Tags --}}
    <meta name="description" content="@yield('meta_description', __('landing.meta.description'))">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <meta name="application-name" content="in.today">

    {{-- Canonical URL --}}
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @else
        <link rel="canonical" href="{{ url($currentLocale) }}">
    @endif

    {{-- Hreflang Tags for Internationalization --}}
    @stack('hreflang')

    {{-- OpenGraph Meta Tags --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="in.today">
    <meta property="og:title" content="@yield('og_title', __('landing.meta.og_title'))">
    <meta property="og:description" content="@yield('og_description', __('landing.meta.og_description'))">
    <meta property="og:url" content="@yield('canonical', url($currentLocale))">
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
    <meta name="twitter:title" content="@yield('og_title', __('landing.meta.og_title'))">
    <meta name="twitter:description" content="@yield('og_description', __('landing.meta.og_description'))">
    <meta name="twitter:image" content="{{ asset('img/og-in-today.jpg') }}">

    {{-- Additional Head Content --}}
    @stack('head')

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
                    <a href="{{ $baseUrl }}" class="text-xl font-bold text-brand hover:opacity-80 transition">
                        {{ __('landing.nav.logo') }}
                    </a>
                </div>

                <!-- Centered Nav Links (Desktop) -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ $baseUrl }}#features" class="text-sm font-medium text-secondary hover:text-brand transition">{{ __('landing.nav.features') }}</a>
                    <a href="{{ $baseUrl }}#pricing" class="text-sm font-medium text-secondary hover:text-brand transition">{{ __('landing.nav.pricing') }}</a>
                    <a href="{{ $baseUrl }}#how-it-works" class="text-sm font-medium text-secondary hover:text-brand transition">{{ __('landing.nav.how_it_works') }}</a>
                    <a href="{{ $baseUrl }}#faq" class="text-sm font-medium text-secondary hover:text-brand transition">{{ __('landing.nav.faq') }}</a>
                </div>

                <!-- Right Side: Theme, Language, CTA -->
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

                    <!-- Language Switcher - Segmented Pills -->
                    <div class="hidden sm:flex items-center bg-card rounded-lg p-1 border border-default" role="group" aria-label="{{ __('landing.nav.language') }}">
                        @foreach($supportedLocales as $locale)
                            <a
                                href="/{{ $locale }}"
                                data-locale="{{ $locale }}"
                                class="px-2.5 py-1 text-xs font-semibold rounded-md lang-pill {{ $currentLocale === $locale ? 'bg-brand text-white' : 'text-secondary hover:text-brand' }}"
                                {{ $currentLocale === $locale ? 'aria-current=page' : '' }}
                            >
                                {{ strtoupper($locale) }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Mobile Language Switcher -->
                    <div class="flex sm:hidden items-center space-x-1 text-xs">
                        @foreach($supportedLocales as $locale)
                            <a
                                href="/{{ $locale }}"
                                data-locale="{{ $locale }}"
                                class="px-1.5 py-1 rounded lang-pill {{ $currentLocale === $locale ? 'bg-brand text-white font-semibold' : 'text-muted' }}"
                            >
                                {{ strtoupper($locale) }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Contact CTA -->
                    <a href="{{ $baseUrl }}#contact" class="hidden sm:inline-flex px-4 py-2 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-brand-hover transition">
                        {{ __('landing.nav.contact') }}
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

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
                        <li><a href="{{ route('imprint', ['locale' => $currentLocale]) }}" class="footer-text hover:text-white transition">{{ __('landing.footer.links.imprint') }}</a></li>
                        <li><a href="{{ route('privacy', ['locale' => $currentLocale]) }}" class="footer-text hover:text-white transition">{{ __('landing.footer.links.privacy') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-700 text-center">
                <p class="text-xs footer-text">{{ __('landing.footer.copyright') }}</p>
            </div>
        </div>
    </footer>

    {{-- Cookie Consent Banner --}}
    @include('partials.cookie-banner')

    {{-- Additional Scripts --}}
    @stack('scripts')

</body>
</html>
