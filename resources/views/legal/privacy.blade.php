<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- SEO Meta Tags --}}
    <title>{{ __('landing.legal.privacy.title') }} – in.today</title>
    <meta name="description" content="{{ __('landing.legal.privacy.meta_description') }}">
    <meta name="robots" content="noindex,follow">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-page text-primary">

    <!-- Navigation -->
    <nav class="fixed w-full bg-page shadow-sm z-50 border-b border-default">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <a href="{{ route('landing', ['locale' => app()->getLocale()]) }}" class="text-2xl font-bold text-brand">
                        in.today
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button
                        id="theme-toggle"
                        type="button"
                        class="p-2 rounded-lg bg-card hover:bg-card-hover transition"
                        aria-label="{{ __('landing.nav.dark_mode') }}"
                    >
                        <svg data-theme-icon="light" class="hidden w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg data-theme-icon="dark" class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>

                    <!-- Language Switcher -->
                    <div class="flex items-center space-x-2 text-sm">
                        @foreach(config('locales.supported') as $locale)
                            <a
                                href="{{ route('privacy', ['locale' => $locale]) }}"
                                class="px-2 py-1 rounded hover:bg-card-hover transition {{ app()->getLocale() === $locale ? 'font-bold text-brand' : 'text-secondary' }}"
                            >
                                {{ strtoupper($locale) }}
                            </a>
                            @if(!$loop->last)
                                <span class="text-muted">|</span>
                            @endif
                        @endforeach
                    </div>

                    <!-- Back to Home -->
                    <a href="{{ route('landing', ['locale' => app()->getLocale()]) }}" class="px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-hover transition">
                        {{ __('landing.nav.logo') }}
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Header -->
    <section class="pt-24 pb-12 md:pt-32 md:pb-16 bg-gradient-to-br from-blue-50 to-page dark:from-slate-900 dark:to-page-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-extrabold text-primary mb-4">
                    {{ __('landing.legal.privacy.title') }}
                </h1>
                <p class="text-lg text-secondary max-w-2xl mx-auto">
                    {{ __('landing.legal.privacy.subtitle') }}
                </p>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="py-12 md:py-16 bg-page">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-card rounded-2xl shadow-lg p-8 md:p-12 border border-default space-y-8">

                @php($sections = __('landing.legal.privacy.sections'))

                @foreach($sections as $key => $section)
                <div class="border-b border-default pb-6 last:border-b-0 last:pb-0">
                    <h2 class="text-xl font-semibold text-primary mb-3">
                        {{ $section['title'] }}
                    </h2>
                    <p class="text-secondary leading-relaxed">
                        {{ $section['body'] }}
                    </p>
                </div>
                @endforeach

            </div>

            <!-- Placeholder Notice -->
            <div class="mt-8 p-4 bg-amber-100 dark:bg-amber-900/30 border border-amber-400 dark:border-amber-600 rounded-lg">
                <p class="text-amber-800 dark:text-amber-200 text-sm text-center">
                    {{ __('landing.legal.privacy.placeholder_notice') }}
                </p>
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
                        <li><a href="{{ route('landing', ['locale' => app()->getLocale()]) }}#features" class="footer-text hover:text-white transition">{{ __('landing.nav.solutions') }}</a></li>
                        <li><a href="{{ route('landing', ['locale' => app()->getLocale()]) }}#pricing" class="footer-text hover:text-white transition">{{ __('landing.nav.pricing') }}</a></li>
                        <li><a href="{{ route('landing', ['locale' => app()->getLocale()]) }}#faq" class="footer-text hover:text-white transition">{{ __('landing.nav.faq') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 footer-text">
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
