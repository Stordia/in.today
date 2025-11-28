@extends('layouts.marketing')

@section('title', __('landing.language.page_title') . ' - in.today')

@section('content')
    <section
        id="language-root"
        class="min-h-[60vh] flex items-center justify-center bg-page text-primary px-4 py-16 pt-24"
    >
        <div class="max-w-3xl w-full space-y-10">
            <!-- Header -->
            <header class="space-y-3 text-center md:text-left">
                <h1 class="text-2xl md:text-3xl font-semibold text-primary">
                    {{ __('landing.language.title') }}
                </h1>
                <p class="text-secondary text-sm md:text-base">
                    {{ __('landing.language.description') }}
                </p>
            </header>

            <div class="space-y-10">
                {{-- North America --}}
                <section class="space-y-3">
                    <h2 class="text-xs font-semibold tracking-wide text-secondary uppercase">
                        {{ __('landing.language.region_north_america') }}
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button
                            type="button"
                            class="w-full rounded-xl border border-default bg-card px-4 py-3 text-left hover:border-brand hover:shadow-md transition group"
                            data-locale="en"
                        >
                            <div class="font-medium text-primary group-hover:text-brand transition">United States</div>
                            <div class="text-xs text-secondary mt-0.5">English</div>
                        </button>
                    </div>
                </section>

                {{-- Europe --}}
                <section class="space-y-3">
                    <h2 class="text-xs font-semibold tracking-wide text-secondary uppercase">
                        {{ __('landing.language.region_europe') }}
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button
                            type="button"
                            class="w-full rounded-xl border border-default bg-card px-4 py-3 text-left hover:border-brand hover:shadow-md transition group"
                            data-locale="de"
                        >
                            <div class="font-medium text-primary group-hover:text-brand transition">Deutschland</div>
                            <div class="text-xs text-secondary mt-0.5">Deutsch</div>
                        </button>

                        <button
                            type="button"
                            class="w-full rounded-xl border border-default bg-card px-4 py-3 text-left hover:border-brand hover:shadow-md transition group"
                            data-locale="el"
                        >
                            <div class="font-medium text-primary group-hover:text-brand transition">Ελλάδα</div>
                            <div class="text-xs text-secondary mt-0.5">Ελληνικά</div>
                        </button>

                        <button
                            type="button"
                            class="w-full rounded-xl border border-default bg-card px-4 py-3 text-left hover:border-brand hover:shadow-md transition group"
                            data-locale="it"
                        >
                            <div class="font-medium text-primary group-hover:text-brand transition">Italia</div>
                            <div class="text-xs text-secondary mt-0.5">Italiano</div>
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </section>
@endsection
