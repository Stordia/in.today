Θα έγραφα κάτι σαν “μίτο της Αριάδνης” για το μέλλον – σύντομο αλλά πυκνό.
Να στο δώσω έτοιμο, ώστε απλά να το κάνεις copy-paste στο νέο Chat:

---

### Context for new chat – in.today (v0.x)

I’m continuing a project called **in.today** and I need you to pick up from here.

#### 1. What in.today is

-   **Global platform** for **restaurant discovery & reservations** (B2B2C).
-   Target:

    -   **B2C**: users discover where to eat/go out _today_.
    -   **B2B**: restaurants use it as a white-label booking & lead-generation system.

-   Phase we’re in now:
    **Marketing / sales landing page + lead generation for restaurant owners**
    (NOT yet the full multi-tenant booking engine).

#### 2. Tech & infrastructure

-   **Backend**: Laravel **12.39.0**, PHP **8.3+** locally, **8.4** on server CLI.
-   **Frontend**: Blade + **Tailwind CSS 4** + **Vite** (no frontend framework yet).
-   **Repo**: `https://github.com/Stordia/in.today.git`
-   **Dev environment**:

    -   Subdomain: `https://dev.in.today/`
    -   Document root: `/var/www/vhosts/in.today/dev.in.today/public`
    -   Git deployment via **Plesk**; after each pull, Plesk runs `bash deploy.sh` in project root.

-   **deploy.sh** (important assumptions):

    -   `cd /var/www/vhosts/in.today/dev.in.today`
    -   Composer via: `/var/www/vhosts/in.today/.phpenv/shims/composer`
    -   PHP via: `/var/www/vhosts/in.today/.phpenv/shims/php`
    -   Node/NPM via: `/var/www/vhosts/in.today/.nodenv/shims/node|npm`
    -   Steps:

        1. `composer install --no-dev --optimize-autoloader`
        2. `npm install`
        3. `npm run build`
        4. `php artisan config:clear`, `route:clear`, `view:clear`

If you propose new deploy steps, they must respect this environment.

#### 3. What’s already implemented

**a) Landing page**

-   Single landing view: `resources/views/landing.blade.php`
-   Sections (with anchors and IDs):

    -   `hero`
    -   `who` (who it’s for)
    -   `features`
    -   `pricing`
    -   `how-it-works`
    -   `faq`
    -   `contact`

-   Header navigation and footer links scroll to these via `/{locale}#section`.

**b) Dark mode**

-   Dark mode via `.dark` class on `<html>`, JS file `resources/js/theme.js`.
-   Semantic Tailwind tokens in `resources/css/app.css` for:

    -   page background, cards, borders, text-primary, text-secondary, brand, accent, etc.

-   Dark mode works, and **colors have been updated** to Branding v2 (see below).

**c) Multi-language system**

-   Default language: **English**.
-   Supported locales: `en`, `de`, `el`, `it`.
-   Route structure:

    -   `/` → auto-detects & redirects to `/{locale}`
    -   `/{locale}` → localized landing (route name: `landing`)
    -   `/language` → language selection page.

-   Language detection:

    -   Browser language (`Accept-Language`)
    -   If supported, redirect to appropriate `/{locale}`
    -   User selection stored in `localStorage`, respected over browser prefs.

-   Middleware:

    -   `SetLocale` in `app/Http/Middleware/SetLocale.php` to apply locale from URL.

-   Language files:

    -   `resources/lang/en/landing.php`
    -   `resources/lang/de/landing.php`
    -   `resources/lang/el/landing.php`
    -   `resources/lang/it/landing.php`

-   **German (`de`) file is already fully written with proper marketing copy for DACH restaurant owners** (hero, features, pricing, FAQ, legal, etc.).
-   Blade uses `__('landing.*')` everywhere with the **new key structure**:

    -   `nav.*`, `hero.*`, `who.*`, `features.items[]`, `pricing.plans[]`, `how.steps[]`, `faq.items[]`, `contact.*`, `footer.*`, etc.

**d) Branding v2.0**

There is a file `resources/BRANDING_v2.0.md` (conceptual brand guide):

-   Brand name: **in.today**

-   Positioning: calm, professional SaaS for restaurant owners (not “party neon”).

-   Color system (main tokens, not exact full spec):

    -   **Service Blue** (brand primary): `#1D4ED8`
    -   **Today Signal Green** (accent): `#22C55E`
    -   **Night Navy** (dark background): `#020617`
    -   **Fog Grey** (light background): `#F4F4F5`
    -   Text:

        -   Primary dark: near `#020617`
        -   Primary light: near `#F9FAFB`
        -   Secondary: slate/neutral variants

    -   Borders & surfaces adjusted for WCAG contrast.

-   These colors are already wired into:

    -   `resources/css/app.css` semantic tokens (including dark variants),
    -   Landing hero, pricing highlight, icons, footer, legal pages gradients.

**Logo/icon:**

-   The visual concept is **not** generated from the AI images.
-   Logo/icon is being drawn manually in CorelDRAW as vector, based on Branding v2.
-   For now the UI uses a simple text treatment / placeholder; final SVGs will be added later.

**e) SEO & meta**

In `landing.blade.php`’s `<head>`:

-   Per-locale:

    -   `<title>` and `<meta name="description">` from translations.

-   Canonical + hreflang for all supported locales:

    -   `rel="canonical"` for current locale
    -   `rel="alternate" hreflang="en|de|el|it"` and `x-default`.

-   Open Graph:

    -   `og:type`, `og:site_name`, `og:title`, `og:description`, `og:url`, `og:image` (placeholder `/img/og-in-today.jpg`), `og:locale` and `og:locale:alternate`.

-   Twitter card:

    -   `summary_large_image`, `twitter:title`, `twitter:description`, `twitter:image`.

-   JSON-LD:

    -   Organization schema with name, url, logo, description, sameAs (socials to be filled later).

**f) Contact form**

-   Located in the `#contact` section of `landing.blade.php`.
-   Submits to: `POST /{locale}/contact` handled by `ContactController@submit`.
-   Fields:

    -   `name`, `email`, `phone`
    -   `restaurant_name`, `city`, `country`, `website_url`
    -   `type` (select: restaurant / bar / café / hotel, etc.)
    -   `services` (checkboxes: website, online reservations, menu, delivery, etc.)
    -   `budget` (radio groups: budget ranges)
    -   `message` (textarea)

-   Anti-spam:

    -   Honeypot field `website_confirm` (if filled, silently discarded).

-   Validation:

    -   Localized errors per field via `landing.php`.

-   Behavior:

    -   Sends email to `proposals@in.today` (with fallback to logging).
    -   Shows success message (flash) and scrolls back to `#contact`.
    -   Fully translated in all 4 languages.

**g) Legal pages**

-   Routes (per locale):

    -   `/{locale}/imprint` (route name: `imprint`)
    -   `/{locale}/privacy` (route name: `privacy`)

-   Views:

    -   `resources/views/legal/imprint.blade.php`
    -   `resources/views/legal/privacy.blade.php`

-   Content:

    -   Localized headings & intro text via `landing.php` translations.
    -   Visual style consistent with landing (gradients, background, footer).

-   Footer of landing uses `route('imprint', ['locale' => app()->getLocale()])` and similar for `privacy`.

#### 4. How I work with tools

-   I use **Claude Code** (connected to the repo) as my coding co-pilot:

    -   I give it precise prompts (file paths, behavior, commit message).
    -   It edits the repo, runs `npm run build`, then `git commit` + `git push main`.

-   I use **you** (ChatGPT) more as:

    -   architect/product+UX lead,
    -   reviewer of flows,
    -   writer of the detailed prompts for Claude Code,
    -   helper for infra/DevOps + next-step planning.

So when I ask you “write a prompt for Claude Code / for the repo”,
you should produce **very explicit, step-by-step prompts** in English that mention concrete files and expected behavior.

#### 5. Direction / roadmap (short)

Immediate next steps (rough order):

1. **Store contact form submissions in database** as `ContactLead` model/table
   (non-breaking, alongside email).
2. Later: small **admin panel (Filament)** to browse leads (read-only at first).
3. Then: start gradually **extracting the real platform**:

    - cities, cuisines, restaurants schema,
    - tenant onboarding,
    - basic reservation model,
    - public “Berlin restaurants” browsing,
    - embeddable widget for single restaurant.

For now we’re still in: **“polish the marketing/lead layer until it’s rock-solid and bug-free.”**

---

Αυτό μπορείς να το κάνεις paste σε καινούριο Chat και να του πεις κάτι τύπου:

> “Read this context carefully and then help me with the next step…”

και θα ξέρει ακριβώς πού βρισκόμαστε και προς τα πού πάμε.
