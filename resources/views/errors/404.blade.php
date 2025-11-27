@extends('layouts.marketing')

@section('title', __('errors.404.title') . ' – in.today')
@section('robots', 'noindex,nofollow')

@section('content')
    <!-- Error Content -->
    <section class="pt-24 min-h-[calc(100vh-16rem)] flex items-center justify-center px-4">
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
    </section>
@endsection
