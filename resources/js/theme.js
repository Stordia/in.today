/**
 * Dark Mode Theme Manager
 *
 * Handles:
 * - System preference detection
 * - User preference storage (localStorage)
 * - Theme toggle functionality
 * - Smooth transitions between themes
 */

// Theme constants
const STORAGE_KEY = 'theme';
const THEME_LIGHT = 'light';
const THEME_DARK = 'dark';
const THEME_SYSTEM = 'system';

/**
 * Get the system's preferred color scheme
 */
function getSystemTheme() {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        return THEME_DARK;
    }
    return THEME_LIGHT;
}

/**
 * Get the user's stored theme preference
 * Priority: localStorage > system preference > light (default)
 */
function getStoredTheme() {
    try {
        return localStorage.getItem(STORAGE_KEY) || null;
    } catch (e) {
        console.warn('localStorage not available:', e);
        return null;
    }
}

/**
 * Store the user's theme preference
 */
function storeTheme(theme) {
    try {
        localStorage.setItem(STORAGE_KEY, theme);
    } catch (e) {
        console.warn('Could not store theme preference:', e);
    }
}

/**
 * Resolve the actual theme to apply (light or dark)
 * based on user preference and system settings
 */
function resolveTheme(preference) {
    if (preference === THEME_SYSTEM || !preference) {
        return getSystemTheme();
    }
    return preference === THEME_DARK ? THEME_DARK : THEME_LIGHT;
}

/**
 * Apply the theme to the document
 */
function applyTheme(theme) {
    const root = document.documentElement;

    if (theme === THEME_DARK) {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }

    // Update any theme toggle UI if it exists
    updateToggleUI(theme);
}

/**
 * Update the theme toggle button UI
 */
function updateToggleUI(theme) {
    const toggle = document.getElementById('theme-toggle');
    if (!toggle) return;

    const sunIcon = toggle.querySelector('[data-theme-icon="light"]');
    const moonIcon = toggle.querySelector('[data-theme-icon="dark"]');

    if (theme === THEME_DARK) {
        sunIcon?.classList.remove('hidden');
        moonIcon?.classList.add('hidden');
        toggle.setAttribute('aria-label', 'Switch to light mode');
    } else {
        sunIcon?.classList.add('hidden');
        moonIcon?.classList.remove('hidden');
        toggle.setAttribute('aria-label', 'Switch to dark mode');
    }
}

/**
 * Toggle between light and dark themes
 */
function toggleTheme() {
    const currentPreference = getStoredTheme() || THEME_SYSTEM;
    const currentTheme = resolveTheme(currentPreference);

    // Toggle to opposite theme
    const newTheme = currentTheme === THEME_DARK ? THEME_LIGHT : THEME_DARK;

    // Store and apply
    storeTheme(newTheme);
    applyTheme(newTheme);
}

/**
 * Initialize theme on page load
 * This should run as early as possible to prevent flash
 */
function initTheme() {
    const storedTheme = getStoredTheme();
    const theme = resolveTheme(storedTheme);
    applyTheme(theme);
}

/**
 * Listen for system theme changes
 */
function watchSystemTheme() {
    if (!window.matchMedia) return;

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    mediaQuery.addEventListener('change', (e) => {
        // Only respond to system changes if user hasn't set a preference
        const storedTheme = getStoredTheme();
        if (!storedTheme || storedTheme === THEME_SYSTEM) {
            const newTheme = e.matches ? THEME_DARK : THEME_LIGHT;
            applyTheme(newTheme);
        }
    });
}

/**
 * Set up event listeners for theme toggle button
 */
function setupToggleButton() {
    const toggle = document.getElementById('theme-toggle');
    if (toggle) {
        toggle.addEventListener('click', toggleTheme);
    }
}

/**
 * Initialize everything when DOM is ready
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setupToggleButton();
        watchSystemTheme();
    });
} else {
    setupToggleButton();
    watchSystemTheme();
}

// Initialize theme immediately (before DOM ready to prevent flash)
initTheme();

// Export for potential use in other modules
export { initTheme, toggleTheme, applyTheme, THEME_LIGHT, THEME_DARK, THEME_SYSTEM };
