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
const STORAGE_KEY = 'intoday_lang';
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
 * Set up language switcher UI with hash preservation
 * Handles all language links on the page (both desktop and mobile)
 */
function setupLanguageSwitcher() {
    const currentLocale = getCurrentLocale();

    // Find all language links with data-locale attribute or lang-pill class
    const links = document.querySelectorAll('[data-locale], a.lang-pill, a[href^="/en"], a[href^="/de"], a[href^="/el"], a[href^="/it"]');

    links.forEach(link => {
        const href = link.getAttribute('href');
        const locale = link.getAttribute('data-locale') || extractLocaleFromHref(href);

        if (!locale || !SUPPORTED_LANGUAGES.includes(locale)) return;

        // Highlight current
        if (locale === currentLocale) {
            link.setAttribute('aria-current', 'page');
        }

        // Set up click handlers to preserve hash
        link.addEventListener('click', (e) => {
            // Only intercept simple locale links (e.g., /en, /de)
            if (!href || !href.match(/^\/[a-z]{2}$/)) return;

            e.preventDefault();
            storeLanguage(locale);

            // Preserve current hash when switching languages
            const currentHash = window.location.hash;
            window.location.href = `/${locale}${currentHash}`;
        });
    });
}

/**
 * Extract locale from href like "/en" or "/de"
 */
function extractLocaleFromHref(href) {
    if (!href) return null;
    const match = href.match(/^\/([a-z]{2})$/);
    return match ? match[1] : null;
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
        setupLanguageSwitcher();
    });
} else {
    setupLanguageSwitcher();
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
