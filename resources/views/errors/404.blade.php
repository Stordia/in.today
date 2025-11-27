<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ __('errors.404.title') }} – in.today</title>
    <meta name="robots" content="noindex,nofollow">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-page text-primary">

    <!-- Simple Header -->
    <header class="w-full bg-page border-b border-default">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="/{{ app()->getLocale() }}" class="text-2xl font-bold text-brand">
                    in.today
                </a>
                <!-- Dark Mode Toggle -->
                <button
                    id="theme-toggle"
                    type="button"
                    class="p-2 rounded-lg bg-card hover:bg-card-hover transition"
                    aria-label="Toggle dark mode"
                >
                    <svg data-theme-icon="light" class="hidden w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg data-theme-icon="dark" class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Error Content -->
    <main class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4">
        <div class="text-center max-w-lg">
            <p class="text-8xl font-extrabold text-brand mb-4">404</p>
            <h1 class="text-3xl md:text-4xl font-bold text-primary mb-4">
                {{ __('errors.404.title') }}
            </h1>
            <p class="text-lg text-secondary mb-8">
                {{ __('errors.404.message') }}
            </p>
            <a
                href="/{{ app()->getLocale() }}"
                class="inline-block px-6 py-3 bg-brand text-white font-semibold rounded-lg hover:bg-brand-hover transition shadow-md btn-cta"
            >
                {{ __('errors.404.cta') }}
            </a>
        </div>
    </main>

</body>
</html>
