<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose Your Language - in.today</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-page text-primary min-h-screen flex items-center justify-center">

    <div class="max-w-2xl mx-auto px-4 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-brand mb-4">in.today</h1>
            <p class="text-xl text-secondary">Choose your language / Wählen Sie Ihre Sprache / Επιλέξτε τη γλώσσα σας / Scegli la tua lingua</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach(config('locales.names') as $code => $name)
                <a
                    href="/{{ $code }}"
                    data-locale="{{ $code }}"
                    class="language-option block bg-card hover:bg-card-hover border-2 border-default hover:border-brand rounded-xl p-6 text-center transition-all transform hover:scale-105"
                >
                    <span class="text-2xl font-semibold text-primary">{{ $name }}</span>
                    <span class="block text-sm text-muted mt-2">{{ strtoupper($code) }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="/" class="text-secondary hover:text-brand transition">← Auto-detect my language</a>
        </div>
    </div>

    <script type="module">
        // Store language preference when user clicks a language
        const STORAGE_KEY = 'intoday_lang';

        document.querySelectorAll('.language-option').forEach(link => {
            link.addEventListener('click', (e) => {
                const locale = e.currentTarget.getAttribute('data-locale');
                try {
                    localStorage.setItem(STORAGE_KEY, locale);
                } catch (e) {
                    // Ignore storage errors
                }
                // Navigation happens via href
            });
        });
    </script>

</body>
</html>
