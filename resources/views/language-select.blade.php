<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('landing.language.page_title') }} - in.today</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-page text-primary">

    @php
        $continue = request()->query('continue', url('/'));
    @endphp

    <main
        id="language-select-root"
        class="min-h-screen flex items-center justify-center px-4 py-12"
        data-continue="{{ $continue }}"
    >
        <div class="max-w-xl w-full space-y-8">
            <!-- Logo -->
            <div class="text-center">
                <a href="/" class="text-2xl font-bold text-brand hover:opacity-80 transition">in.today</a>
            </div>

            <!-- Title & Description -->
            <div class="text-center space-y-3">
                <h1 class="text-2xl md:text-3xl font-semibold text-primary">
                    {{ __('landing.language.title') }}
                </h1>
                <p class="text-secondary text-sm md:text-base max-w-md mx-auto">
                    {{ __('landing.language.description') }}
                </p>
            </div>

            <!-- Country/Region Options -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button
                    type="button"
                    class="w-full rounded-xl border border-default bg-card px-5 py-4 text-left hover:border-brand hover:shadow-md transition group"
                    data-locale="en"
                >
                    <div class="font-medium text-primary group-hover:text-brand transition">United States</div>
                    <div class="text-sm text-secondary mt-0.5">English</div>
                </button>

                <button
                    type="button"
                    class="w-full rounded-xl border border-default bg-card px-5 py-4 text-left hover:border-brand hover:shadow-md transition group"
                    data-locale="de"
                >
                    <div class="font-medium text-primary group-hover:text-brand transition">Deutschland</div>
                    <div class="text-sm text-secondary mt-0.5">Deutsch</div>
                </button>

                <button
                    type="button"
                    class="w-full rounded-xl border border-default bg-card px-5 py-4 text-left hover:border-brand hover:shadow-md transition group"
                    data-locale="el"
                >
                    <div class="font-medium text-primary group-hover:text-brand transition">Ελλάδα</div>
                    <div class="text-sm text-secondary mt-0.5">Ελληνικά</div>
                </button>

                <button
                    type="button"
                    class="w-full rounded-xl border border-default bg-card px-5 py-4 text-left hover:border-brand hover:shadow-md transition group"
                    data-locale="it"
                >
                    <div class="font-medium text-primary group-hover:text-brand transition">Italia</div>
                    <div class="text-sm text-secondary mt-0.5">Italiano</div>
                </button>
            </div>

            <!-- Back Link -->
            <div class="text-center pt-4">
                <a href="/" class="text-sm text-secondary hover:text-brand transition">
                    ← {{ __('landing.language.back_auto') }}
                </a>
            </div>
        </div>
    </main>

</body>
</html>
