<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>in.today - Loading...</title>
    <style>
        /* Light mode colors (BRANDING v2.0) */
        :root {
            --loader-bg: linear-gradient(to bottom, #EFF6FF 0%, #F4F4F5 100%);
            --loader-text: #020617;
            --spinner-track: rgba(29, 78, 216, 0.2);
            --spinner-head: #1D4ED8;
        }
        /* Dark mode colors */
        @media (prefers-color-scheme: dark) {
            :root {
                --loader-bg: linear-gradient(to bottom, #0F172A 0%, #020617 100%);
                --loader-text: #F9FAFB;
                --spinner-track: rgba(96, 165, 250, 0.2);
                --spinner-head: #60A5FA;
            }
        }
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--loader-bg);
            color: var(--loader-text);
        }
        .loader {
            text-align: center;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--spinner-track);
            border-radius: 50%;
            border-top-color: var(--spinner-head);
            animation: spin 1s ease-in-out infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p>Redirecting...</p>
    </div>

    <script type="module">
        // Language detection constants
        const STORAGE_KEY = 'intoday_lang';
        const DEFAULT_LANGUAGE = 'en';
        const SUPPORTED_LANGUAGES = ['en', 'de', 'el', 'it'];

        /**
         * Get stored language preference
         */
        function getStoredLanguage() {
            try {
                const stored = localStorage.getItem(STORAGE_KEY);
                if (stored && SUPPORTED_LANGUAGES.includes(stored)) {
                    return stored;
                }
            } catch (e) {
                // localStorage not available
            }
            return null;
        }

        /**
         * Detect browser language
         */
        function getBrowserLanguage() {
            const browserLangs = navigator.languages || [navigator.language || navigator.userLanguage];

            for (const lang of browserLangs) {
                const primaryLang = lang.split('-')[0].toLowerCase();
                if (SUPPORTED_LANGUAGES.includes(primaryLang)) {
                    return primaryLang;
                }
            }

            return DEFAULT_LANGUAGE;
        }

        /**
         * Redirect to appropriate language
         */
        function redirect() {
            // Check localStorage first
            let language = getStoredLanguage();

            // Fall back to browser detection
            if (!language) {
                language = getBrowserLanguage();

                // Store detected language
                try {
                    localStorage.setItem(STORAGE_KEY, language);
                } catch (e) {
                    // Ignore storage errors
                }
            }

            // Redirect to localized page
            window.location.replace(`/${language}`);
        }

        // Execute redirect immediately
        redirect();
    </script>
</body>
</html>
