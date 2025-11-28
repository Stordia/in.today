/**
 * Language Detection & Management
 *
 * Handles:
 * - Browser language detection
 * - User preference storage (localStorage)
 * - Language switching functionality
 * - Smooth navigation between locales
 */

// Language constants
const STORAGE_KEY = 'intoday_locale';
const BACK_URL_KEY = 'intoday_language_back';
const DEFAULT_LANGUAGE = 'en';
const SUPPORTED_LANGUAGES = ['en', 'de', 'el', 'it'];

/**
 * Get the user's stored language preference
 */
function getStoredLanguage() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored && SUPPORTED_LANGUAGES.includes(stored)) {
            return stored;
        }
        return null;
    } catch (e) {
        console.warn('localStorage not available:', e);
        return null;
    }
}

/**
 * Store the user's language preference
 */
function storeLanguage(language) {
    try {
        if (SUPPORTED_LANGUAGES.includes(language)) {
            localStorage.setItem(STORAGE_KEY, language);
        }
    } catch (e) {
        console.warn('Could not store language preference:', e);
    }
}

/**
 * Detect browser language and map to supported locale
 */
function getBrowserLanguage() {
    // Get browser languages (ordered by preference)
    const browserLangs = navigator.languages || [navigator.language || navigator.userLanguage];

    // Try to match against supported languages
    for (const lang of browserLangs) {
        // Extract primary language code (e.g., 'de' from 'de-DE')
        const primaryLang = lang.split('-')[0].toLowerCase();

        if (SUPPORTED_LANGUAGES.includes(primaryLang)) {
            return primaryLang;
        }
    }

    // Fallback to default
    return DEFAULT_LANGUAGE;
}

/**
 * Resolve which language to use
 * Priority: localStorage > browser detection > default
 */
function resolveLanguage() {
    return getStoredLanguage() || getBrowserLanguage();
}

/**
 * Navigate to a specific language URL
 */
function navigateToLanguage(language) {
    if (!SUPPORTED_LANGUAGES.includes(language)) {
        language = DEFAULT_LANGUAGE;
    }

    // Store preference
    storeLanguage(language);

    // Navigate to localized page
    window.location.href = `/${language}`;
}

/**
 * Get current locale from URL
 */
function getCurrentLocale() {
    const path = window.location.pathname;
    const match = path.match(/^\/([a-z]{2})(\/|$)/);

    if (match && SUPPORTED_LANGUAGES.includes(match[1])) {
        return match[1];
    }

    return null;
}

/**
 * Set up footer country/region link
 * Stores current URL in localStorage for return flow (no query params)
 */
function setupChangeCountryLink() {
    const link = document.getElementById('change-country-link');
    if (!link) return;

    link.addEventListener('click', (event) => {
        event.preventDefault();

        const currentUrl = window.location.href; // includes hash

        try {
            localStorage.setItem(BACK_URL_KEY, currentUrl);
        } catch (e) {
            // ignore storage errors
        }

        // Navigate to /language without query params
        const href = link.getAttribute('href') || '/language';
        window.location.href = href;
    });
}

/**
 * Initialize the language selection page (/language)
 * Reads back URL from localStorage, handles locale switching with return flow
 */
function initLanguageSelectionPage() {
    const root = document.getElementById('language-root');
    if (!root) return;

    const buttons = root.querySelectorAll('[data-locale]');

    // Read back URL from localStorage
    let backUrl = window.location.origin + '/en';
    try {
        const stored = localStorage.getItem(BACK_URL_KEY);
        if (stored) {
            backUrl = stored;
        }
    } catch (e) {
        // ignore
    }

    /**
     * Build a new URL with the locale segment replaced
     */
    function buildLocaleUrl(locale, rawUrl) {
        try {
            const url = new URL(rawUrl);
            const segments = url.pathname.split('/').filter(Boolean);

            // Replace or prepend locale segment
            if (segments.length > 0 && SUPPORTED_LANGUAGES.includes(segments[0])) {
                segments[0] = locale;
            } else {
                segments.unshift(locale);
            }

            url.pathname = '/' + segments.join('/');
            return url.toString();
        } catch (e) {
            return `/${locale}`;
        }
    }

    buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const locale = btn.dataset.locale || 'en';
            const target = buildLocaleUrl(locale, backUrl);

            try {
                // Store locale preference
                storeLanguage(locale);
                // Clear the back URL
                localStorage.removeItem(BACK_URL_KEY);
            } catch (e) {
                // ignore
            }

            // Redirect to the new locale URL
            window.location.href = target;
        });
    });
}

/**
 * Set up language attribute on HTML element
 */
function setHtmlLang() {
    const locale = getCurrentLocale();
    if (locale) {
        document.documentElement.lang = locale;
    }
}

/**
 * Initialize language system
 */
function initLanguage() {
    setHtmlLang();
}

// Initialize immediately
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setupChangeCountryLink();
        initLanguageSelectionPage();
    });
} else {
    setupChangeCountryLink();
    initLanguageSelectionPage();
}

initLanguage();

// Export for use in other modules
export {
    resolveLanguage,
    navigateToLanguage,
    storeLanguage,
    getStoredLanguage,
    getCurrentLocale,
    SUPPORTED_LANGUAGES,
    DEFAULT_LANGUAGE,
};
