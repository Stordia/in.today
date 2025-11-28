# TODO-TESTS.md

Tracking manual tests, bugs and QA status for **in.today**  
Environment: `https://dev.in.today` (Laravel 12 + Filament panels)

Legend:
- [ ] open
- [x] done / verified
- [!] bug found / to fix
- [?] needs clarification / decision

---

## 0. Latest findings (2025-11-27)

### 0.1 Marketing site

- [!] **Loader background color on hard refresh**
  - When doing a hard refresh on `/` the page briefly shows a **purple background + loader**.
  - TODO: Change loader background to match current brand gradient / background (BRANDING v2.0).

- [!] **Missing or inconsistent “Who it’s for” in navbar**
  - Expectation: Either have a clear “Who it’s for” item in the nav, _or_ remove it consistently from copy and structure.
  - Currently: No “Who it’s for” in the navbar, but section still exists.
  - TODO: Decide final nav structure, then align sections + anchors.

- [x] **Anchors for all other nav items**
  - Features, Pricing, How it works, FAQ, Contact jump correctly to their sections.

- [!] **Language switcher on error pages does not preserve slug/hash**
  - On 404 page: changing language loses the current URL slug/hash and reloads the default locale.
  - TODO: Align behavior with main landing page (preserve `#anchor` when switching locale).

- [x] **Cookie banner behavior**
  - Banner reappears correctly after clearing localStorage.
  - Accept behavior works as expected.

- [!] **Contact success modal shows raw translation keys**
  - Current modal text: `landing.contact.modal_title`, `landing.contact.modal_close` are shown literally.
  - TODO: Use `__()` helpers / translation keys from `landing.php` so the modal text is localized properly.

- [x] **Honeypot behavior**
  - Filling honeypot field prevents lead creation → OK.

- [!] **Accessibility warning: aria-hidden on focused element (modal)**
  - Browser console warning:
    - “Blocked aria-hidden on an element because its descendant retained focus… Consider using the `inert` attribute instead…”
  - TODO: Adjust modal logic:
    - Don’t keep `aria-hidden="true"` on the active modal container, or
    - Use `inert` on background content instead of `aria-hidden` on the modal while focused.
    - Ensure focus is moved into modal and restored on close.

---

## 1. Marketing Site Tests (`/`, `/{locale}`)

### 1.1 Navigation & Anchors

- [ ] Decide final nav items (e.g. Home, Who it’s for, Features, Pricing, How it works, FAQ, Contact).
- [ ] Make sure nav labels are consistent across all locales (EN/DE/EL/IT).
- [x] Test anchors for Features, Pricing, How it works, FAQ, Contact in all locales.
- [ ] Test nav & anchors on mobile (collapsible menu).

### 1.2 Language Switcher

- [x] Switching language from `/en#pricing` → `/de#pricing` (and other locales) keeps anchor on landing page.
- [ ] Confirm same behavior on other anchored URLs (e.g. `#features`, `#contact`).
- [ ] Ensure language switcher does **not** lose the anchor on 404/500 pages (bug described in 0.1).

### 1.3 Dark Mode / Light Mode

- [x] Check that all sections are readable in both modes (no low-contrast text).
- [ ] Re-check after final color polish (once brand palette is stable).
- [ ] Confirm dark mode state persists across page reloads and language switches.

### 1.4 Contact Form

- [x] Submit valid form → success modal appears without full reload.
- [x] Lead created **once** in `contact_leads` database table.
- [x] Filling honeypot field → no lead created.
- [ ] Verify client-side validation messages appear nicely styled for invalid input.
- [!] Fix modal translations + accessibility per 0.1 notes.

### 1.5 Cookie Banner & Analytics

- [x] Clear localStorage → banner reappears.
- [x] Accept → banner stays hidden on subsequent reloads.
- [ ] After we plug real analytics IDs: verify scripts only load after consent (if we add stricter consent logic).
- [ ] Confirm analytics partials appear correctly in `<head>` and do not break CSP (when defined).

### 1.6 Error Pages (404 / 500)

- [x] 404 shows full layout (nav, language switcher, footer, cookie banner).
- [ ] 500 page: simulate error and verify same layout.
- [ ] CTA on 404 and 500 correctly returns to default locale home (`/en` or localized route).
- [ ] Language change on 404/500 preserves slug/hash where possible.

---

## 2. Business Panel Tests (`/business`)

### 2.1 Authentication & Roles

- [ ] Login as single-venue owner: `owner.single@in.today.test / Demo123!`
  - [ ] User lands directly in the one restaurant dashboard.
- [ ] Login as multi-venue owner: `owner.multi@in.today.test / Demo123!`
  - [ ] First see “switch restaurant” page.
  - [ ] After selecting a restaurant, dashboard data is correctly scoped.
- [ ] Login as staff: `staff@in.today.test / Demo123!`
  - [ ] Has limited access (no configuration they shouldn’t see).

### 2.2 Tenancy / Scoping

- [ ] Ensure all Tables, OpeningHours, BlockedDates, Reservations, Waitlist entries are scoped to current restaurant.
- [ ] Switch restaurant (multi-owner) and verify data changes appropriately in all resources.

### 2.3 Reservations & Waitlist

- [ ] Change reservation status (confirmed, completed, no_show) and verify updated in DB.
- [ ] Convert Waitlist entry to Reservation via provided action.
- [ ] Check that converted waitlist entries update their status correctly (`converted`, etc.).

### 2.4 Configuration

- [ ] Create a new Table and confirm it appears only in current restaurant.
- [ ] Edit Opening Hours for a specific day and verify result.
- [ ] Create a Blocked Date and confirm it is respected in future availability logic (later).

### 2.5 UX & Localization

- [ ] Check if panel needs localization (EN-only or multilingual UI).
- [ ] Verify labels, status badges and error messages are understandable for restaurant owners.

---

## 3. Admin Panel Tests (`/admin`)

### 3.1 Access Control

- [ ] Login as `admin@in.today.test / Admin123!` → can access `/admin`.
- [ ] Try to access `/admin` as non-admin (owner/staff) → access denied.
- [ ] Confirm `<meta name="robots" content="noindex, nofollow">` is present.

### 3.2 Core Resources

- [ ] Cities: list, create, edit, delete; filters by country/active.
- [ ] Cuisines: list, create, multilingual fields.
- [ ] Agencies: list, create, attach restaurants, check counts.
- [ ] Restaurants: list, edit plan/status; verify relationships.
- [ ] Reservations (read-only): list & view; verify relationship to restaurant and user.
- [ ] Waitlist (read-only): list & view; verify relationship to restaurant and reservation.

### 3.3 Affiliates

- [ ] Affiliates: create new affiliate, with default commission rate.
- [ ] Affiliate Links: create link with slug, target URL; verify counters initially 0.
- [ ] Affiliate Conversions: create sample conversion record linked to affiliate + link + restaurant.
- [ ] Verify that stats in list views (clicks, conversions) update when records change.

### 3.4 White-label / Agencies

- [ ] Create a test Agency with white-label settings.
- [ ] Attach a Restaurant to an Agency.
- [ ] Ensure future agency-specific logic can use these relationships (for later phases).

---

## 4. Technical / Non-functional Tests

### 4.1 Performance

- [ ] Basic Lighthouse test on landing page (mobile & desktop).
- [ ] Check asset sizes (CSS/JS) after more features are added.

### 4.2 Security & Hardening

- [ ] Confirm `APP_DEBUG=false` in non-local environments before going public.
- [ ] Verify no sensitive info is leaked in error pages.
- [ ] Double-check CORS, rate limiting and basic brute-force protection (later phase).

### 4.3 Backups & Migrations

- [ ] Verify `php artisan migrate` runs cleanly on fresh DB.
- [ ] Test `php artisan app:seed-demo --fresh` on dev to ensure demo data can be reset.

---

## 5. Notes / Daily Log

Use this section as a running log when you test:

- **2025-11-27**  
  - Initial marketing QA: loader color issue, modal translation + aria warning, 404 behavior & language switch, contact form OK, honeypot OK.
- **YYYY-MM-DD**  
  - … add next testing sessions here …
