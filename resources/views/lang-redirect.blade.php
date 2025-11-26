<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>in.today - Loading...</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .loader {
            text-align: center;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
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
