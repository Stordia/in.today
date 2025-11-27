{{-- Cookie Consent Banner --}}
<div
    id="cookie-banner"
    class="fixed bottom-0 left-0 right-0 z-50 hidden"
    role="dialog"
    aria-labelledby="cookie-banner-title"
    aria-describedby="cookie-banner-message"
>
    <div class="bg-card border-t border-default shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex-1">
                    <p id="cookie-banner-message" class="text-sm text-secondary">
                        {{ __('landing.cookie.message') }}
                        <a
                            href="{{ route('privacy', ['locale' => app()->getLocale()]) }}"
                            class="text-brand hover:underline font-medium"
                        >{{ __('landing.cookie.privacy_link') }}</a>.
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <a
                        href="{{ route('privacy', ['locale' => app()->getLocale()]) }}"
                        class="text-sm text-secondary hover:text-brand transition"
                    >
                        {{ __('landing.cookie.learn_more') }}
                    </a>
                    <button
                        id="cookie-accept"
                        type="button"
                        class="px-4 py-2 bg-brand text-white text-sm font-semibold rounded-lg hover:bg-brand-hover transition btn-cta"
                    >
                        {{ __('landing.cookie.accept') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
