/**
 * Cookie Consent Banner
 *
 * Shows a GDPR-friendly cookie banner on first visit.
 * Stores consent in localStorage to avoid showing it again.
 */

const STORAGE_KEY = 'intoday_cookie_consent';

function initCookieConsent() {
    const banner = document.getElementById('cookie-banner');
    const acceptButton = document.getElementById('cookie-accept');

    if (!banner || !acceptButton) {
        return;
    }

    // Check if consent was already given
    const consent = localStorage.getItem(STORAGE_KEY);

    if (!consent) {
        // Show the banner
        banner.classList.remove('hidden');
    }

    // Handle accept button click
    acceptButton.addEventListener('click', () => {
        localStorage.setItem(STORAGE_KEY, 'accepted');
        banner.classList.add('hidden');
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCookieConsent);
} else {
    initCookieConsent();
}
