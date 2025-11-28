# DEV NOTES – in.today (Living Document)

> This file exists to give AI code assistants (Claude Code, ChatGPT, etc.) and human devs a quick, accurate overview of the in.today project: what we’ve built, how it’s structured, and what’s next.
> 
> Please keep this file **short, up to date, and high-signal**. Add links to other detailed docs instead of duplicating them.

---

## 1. High-Level Overview

- **Project**: in.today – SaaS platform for HoReCa (restaurants, cafés, bars, clubs, hotels with F&B).
- **Tech stack**
  - Laravel **12.x**
  - PHP **8.4.x** (Plesk, `phpenv` shims per vhost)
  - NodeJS via `.nodenv` (per domain)
  - Vite + Tailwind (custom design system from `BRANDING_v2.0.md`)
  - Filament v3 (admin & business panels)
  - MySQL/MariaDB (Plesk server)
- **Environments**
  - Local dev (Herd) – with separate `.env`.
  - Remote dev: `https://dev.in.today` → Document root: `dev.in.today/public`.
  - Deploy script: `deploy.sh` in project root (used both locally and via Plesk “additional deployment actions”).

The platform is built **from the start** with:
- Multi-tenant / multi-role architecture (platform admins, agencies, restaurants, staff).
- Multi-language marketing site (EN, DE, EL, IT).
- Room for high traffic and future features (reservations, widgets, billing, affiliates, etc.).

---

## 2. Frontend / Marketing Site

**Routes**

- `/` redirects to default locale (currently `/en`).
- `/en`, `/de`, `/el`, `/it` – localized landing pages.
- Legal pages per locale: `/{locale}/imprint`, `/{locale}/privacy`.
- Language / country selection: `/language`.

**Key files**

- `resources/views/layouts/marketing.blade.php`  
  Shared layout: navbar, footer, dark mode toggle, analytics partial, cookie banner.
- `resources/views/landing.blade.php`  
  Main marketing landing page.
- `resources/lang/{en,de,el,it}/landing.php`  
  All marketing copy + labels (nav, hero, pricing, features, contact form, cookie banner, locale texts).
- `resources/views/legal/imprint.blade.php`  
  Localized imprint content (basic boilerplate for now).
- `resources/views/legal/privacy.blade.php`  
  Localized privacy content.
- `resources/views/language-select.blade.php`  
  Apple-style country/region chooser page with regions (North America, Europe).

**Branding & design**

- **Color system** defined in `resources/BRANDING_v2.0.md` and implemented in `resources/css/app.css`:
  - Brand primary: service blue.
  - Accent: “Today Signal” green for CTA & focus.
  - Light mode: foggy grey background, dark ink text.
  - Dark mode: near-black background (Night Navy) with soft gradients.
- Layout:
  - Clean hero with text + “fake website preview” card (Tailwind only, no real screenshot yet).
  - Sections: Hero → Who it’s for → Features → Pricing → How it works → FAQ → Contact.
  - Fully responsive (desktop two-column hero, mobile stacked).
- **Language / locale UX**
  - No more language pill buttons in navbar.
  - Footer has **Apple-style link**: `Country or region: Germany / United States / Greece / Italy`.
  - Clicking footer link:
    - Goes to `/language`.
    - We store current URL (including hash) in `localStorage` (`intoday_language_back`).
    - On `/language`, user chooses a country/locale; we rewrite the stored URL with the new locale and redirect back.
  - No query parameters in URLs for this flow.

**SEO & compliance**

- Per-locale `<title>` and `<meta description>` via translation files.
- Canonical + `hreflang` tags (`en`, `de`, `el`, `it`, `x-default`).
- OpenGraph + Twitter card tags (image: `/img/og-in-today.jpg` placeholder).
- JSON-LD Organization schema in the head.
- **Cookie banner**:
  - `resources/views/partials/cookie-banner.blade.php`
  - `resources/js/cookie-consent.js` (localStorage based).
  - Only handles “consent remembered” for now; analytics are wired but commented until real IDs exist.
- **Analytics**:
  - `resources/views/partials/analytics.blade.php` – placeholder for GTM, Meta Pixel, etc.
  - Config in `config/services.php` and `.env` (IDs, etc.).

**Contact form**

- Shown in `/landing.blade.php` under `#contact`.
- Fields: name, email, phone, restaurant_name, city, country, website_url, type, services[], budget, message, locale, hidden honeypot (`website_confirm`).
- Backend: `app/Http/Controllers/ContactController.php`
  - Validates input (localized error messages).
  - Honeypot silently drops spam.
  - Persists into `contact_leads` table.
  - Sends email via `ContactLeadReply` mailable.
  - AJAX (JSON) support:
    - If `Accept: application/json` → returns JSON and shows success **modal** (no full page reload).
    - Fallback to standard POST + redirect + flash for non-JS.
- JS:
  - `resources/js/contact-form.js` – handles AJAX submission + success modal.
  - `resources/js/language.js` – hash preservation + locale switching.
  - `resources/js/app.js` – imports contact-form and language modules.

**Error pages**

- `resources/views/errors/404.blade.php`
- `resources/views/errors/500.blade.php`
  - Both extend the marketing layout → full navbar, footer, dark mode, cookie banner, analytics partial.
  - Translations in `resources/lang/{locale}/errors.php`.

---

## 3. Core Backend Architecture (High Level)

Detailed design is in `resources/BACKEND_ARCHITECTURE.md`.  
Quick summary:

- **Multi-tenant structure**
  - `agencies` – large agency partners; optional white-label JSON settings.
  - `agency_users` – pivot linking users to agencies with roles (owner/manager/staff).
  - `restaurants` – core tenant entity for each venue (restaurant, café, bar, etc.).
  - `restaurant_users` – pivot linking users to restaurants with roles (owner/manager/staff).
  - `users` – global users with `global_role` enum (`user`, `platform_admin`).

- **Enums**
  - `GlobalRole` – platform admin vs regular user.
  - `AgencyRole`, `RestaurantRole` – owner, manager, staff.
  - `RestaurantPlan` – starter, pro, business.
  - Reservation domain enums (`ReservationStatus`, `ReservationSource`, `WaitlistStatus`).
  - `ContactLeadStatus` – new, contacted, qualified, proposal_sent, won, lost, spam.

- **Tenancy helpers**
  - `app/Support/Tenancy/CurrentTenant.php`
  - `app/Support/Tenancy/CurrentRestaurant.php`
  - Panels and queries *must* scope to current restaurant/tenant where appropriate.

- **Reservation / Availability domain**
  - Tables:
    - `cities` – lookup with slug, country, lat/lng, timezone.
    - `cuisines` – multilingual names + icon.
    - `tables` – physical restaurant tables; seats, zone, combinability.
    - `opening_hours` – weekly schedule; day, one or more shifts.
    - `blocked_dates` – holidays, events, exceptions.
    - `reservations` – bookings with status, customer info, source, metadata.
    - `waitlist` – for overbooked timeslots.
  - `restaurants` extended with:
    - `city_id`, `cuisine_id`, address, geo, price_range, avg_rating, review_count, reservation_count, features, logo URL, cover image, etc.

---

## 4. Filament Panels

### 4.1 Platform Admin Panel (`/admin`)

- `app/Providers/Filament/AdminPanelProvider.php`  
  - Only `global_role = platform_admin` may access.
  - `<meta name="robots" content="noindex, nofollow">`.
  - Navigation groups (no group icons, resources have their own icons):
    - **Directory**
      - CityResource
      - CuisineResource
      - AgencyResource
      - RestaurantResource
    - **Bookings**
      - ReservationResource (mostly read-only for now)
      - WaitlistResource
    - **Partners**
      - AffiliateResource
      - AffiliateLinkResource
      - AffiliateConversionResource
    - **Sales**
      - ContactLeadResource

- CLI helper:
  - `php artisan app:create-platform-admin`  
    Creates a platform admin with random password (printed once; currently we also have seeded demo accounts).

- Affiliates:
  - Tables:
    - `affiliates`
    - `affiliate_links`
    - `affiliate_conversions`
  - Resources:
    - AffiliateResource – CRUD, stats, status.
    - AffiliateLinkResource – CRUD, click & conversion stats.
    - AffiliateConversionResource – tracking conversions, payouts.

### 4.2 Business Panel (`/business`)

- `app/Providers/Filament/RestaurantPanelProvider.php`
  - Branded as **“in.today Business”**.
  - Path/ID uses “business” instead of “restaurant”.
  - `<meta name="robots" content="noindex, nofollow">`.
  - Only users with an active `RestaurantUser` record may access.
  - Multi-restaurant users can switch via `/business/switch-restaurant`.

- Resources (scoped to current restaurant):
  - TableResource
  - OpeningHourResource
  - BlockedDateResource
  - ReservationResource (full CRUD + status actions: confirm, complete, no-show, cancel, etc.)
  - WaitlistResource (notify guest, convert to reservation)

- Dashboard:
  - Current restaurant summary:
    - Today’s reservations count.
    - Upcoming (next 7 days).
    - Number of waitlist entries.
  - Upcoming reservations table with date/time, guest, party size, status badge, source.

---

## 5. ContactLead CRM

### 5.1 Data & status

- Table: `contact_leads`
  - Fields include:
    - Basic: name, email, phone, restaurant_name, city, country, type, website_url, services (JSON array), budget, message.
    - Meta: source_url, ip_address, user_agent, locale.
    - CRM: `status` (enum), `assigned_to_user_id` (FK to users), `internal_notes`, `restaurant_id` (if converted to customer).
- Enum: `ContactLeadStatus`
  - new → contacted → qualified → proposal_sent → won
  - Alternative endings: lost, spam
  - Helpers: `label()`, `color()`, `icon()`, `isOpen()`, `canConvert()`, etc.
- Model: `App\Models\ContactLead`
  - Relationships: `assignedTo()`, `restaurant()`, `emails()`.
  - Scopes: byStatus, open, closed, etc.
  - Helpers for location & services summary.

### 5.2 Filament – ContactLeadResource

- Navigation:
  - Group: “Sales”.
  - Badge with count of new leads.
- List:
  - Columns: created date, name, email, business name, type, services summary (badge with tooltip), status (colored badge with icon), assigned_to, converted flag.
  - Filters: status, type, assigned, converted.
  - Search: name, email, restaurant_name, city.
  - Row actions:
    - View
    - Edit
    - Status transitions (mark contacted, qualified, proposal sent, won, lost, spam).
    - “Email” → goes to dedicated email page.

- Edit form:
  - Left side:
    - Contact & Business: name, email, phone, restaurant_name, city, country, type (dropdown).
    - Request Details: services (read-only badges), budget, website link, message.
  - Right sidebar:
    - Internal: status select, assigned_to (platform admins only), internal_notes, converted restaurant info.
    - Meta (collapsed): created_at, locale, source_url, ip.

- View page:
  - Similar layout as edit, read-only.
  - Email history section.

### 5.3 Email-from-CRM

- Table: `contact_lead_emails`
  - Fields: contact_lead_id, sent_by_user_id, to_email, subject, body, status (sent/failed), error_message, sent_at.
- Model: `ContactLeadEmail`
- Mailable: `App\Mail\ContactLeadReply`
- Templates helper: `App\Support\ContactLeadEmailTemplates`
  - `for(ContactLead $lead, string $key): array`
  - Keys: `initial_reply`, `follow_up`, `proposal_sent`.
  - Subject/body with placeholders: `{name}`, `{restaurant}`, `{city}`, `{country}`.

- Dedicated page:
  - Route: `/admin/contact-leads/{record}/email`
  - Class: `App\Filament\Resources\ContactLeadResource\Pages\EmailContactLead`
  - Views: `resources/views/filament/resources/contact-lead-resource/pages/email-contact-lead.blade.php`
  - Behavior:
    - Shows a lead summary card.
    - Full page form (no modals, no Livewire/Alpine collisions).
    - On submit:
      - Sends email via Laravel mailer.
      - Logs to `contact_lead_emails`.
      - If lead is still `new`, auto-update to `contacted`.
      - Redirect back to View page with success notification.

- Email history display:
  - Blade: `resources/views/filament/infolists/components/email-history.blade.php`
  - Inline expandable area using `x-collapse` (no global keydown listeners, no modals).
  - Shows status badges + subject + sent_at; full body on expand.

---

## 6. Demo Data / Test Accounts

Seeder command:

```bash
php artisan app:seed-demo --fresh
```

Creates demo agencies, restaurants, tables, reservations, waitlist, etc.

Demo users (all with password `Demo123!`):

- `admin@in.today.test` – Platform Admin (`/admin`)
- `owner.single@in.today.test` – Single restaurant owner (`/business`)
- `owner.multi@in.today.test` – Multi-restaurant owner/manager (`/business`)
- `staff@in.today.test` – Restaurant staff (`/business`)

---

## 7. Timezone & Mailer Notes

- DB timestamps are stored in UTC (standard).
- Display in admin/Biz panels should respect the venue’s timezone (often Europe/Berlin). Some places still show server/UTC times and need refinement.
- Mail:
  - Uses Laravel mailers; dev environment may be configured via Plesk/localhost or real SMTP.
  - For dev: we often set `MAIL_MAILER=log` to avoid actually sending.

---

## 8. Guidelines for AI Code Assistants (VERY IMPORTANT)

When editing this project (Claude Code, ChatGPT, etc.):

1. **Never break the multi-tenant / multi-role logic**
   - Always respect `CurrentRestaurant` / `CurrentTenant` where relevant.
   - Admin panel is global; Business panel is scoped to one restaurant.

2. **Respect existing structure & naming**
   - Keep using enums for states instead of string literals sprinkled everywhere.
   - Add new enums/resources consistently in `App\Enums`, `App\Models`, `App\Filament\Resources`.

3. **Frontend rules**
   - Use existing Tailwind design tokens and classes from `BRANDING_v2.0.md`.
   - Don’t introduce random color hex codes; map to the palette.
   - Prefer simple, accessible patterns. Avoid complex Alpine hacks that conflict with Filament modals.

4. **Backend rules**
   - When adding migrations, keep them atomic and append-only (no destructive changes without explicit instruction).
   - Whenever you add DB changes or important features:
     - Run `php artisan migrate`.
     - Run `php artisan test` if any tests exist.
     - Run `npm run build` if frontend assets changed.
   - Always finish with a **short plain-text SUMMARY** that can be copy-pasted into this chat.

5. **SEO & UX**
   - Don’t add query parameters to public URLs unless absolutely necessary.
   - Preserve URL hash fragments when switching locales.
   - Keep error pages, legal pages, and the landing under the same marketing layout.

---

## 9. Next Possible Steps (Roadmap Sketch)

This is not a hard plan, just a list of likely next steps:

1. **CRM / Sales**
   - Pipeline board for ContactLeads (kanban by status).
   - Better email templates & follow-up sequences.
   - Notes & activity timeline per lead (emails, status changes, conversions).

2. **Reservations & Widgets**
   - Public widget for bookings that restaurants can embed on their sites.
   - Availability engine (time slot search, table combinations).
   - End-user flow for making and managing reservations.

3. **Billing & Plans**
   - Subscription plans per restaurant (starter/pro/business).
   - Stripe integration for recurring payments.
   - Billing history, invoices, and seat/feature limits.

4. **White-Label for Agencies**
   - Agency-level branding settings (logo, colors, subdomain).
   - Agency portals to manage their own restaurant clients.

5. **Affiliates**
   - Deep link integration with landing pages and signup.
   - Commission calculation and payout exports.

Whenever you implement any of these, **update this file** briefly so future work stays aligned.
