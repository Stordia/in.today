να κάνουμε ένα μικρό internal CRM για τα leads:

να βλέπεις όλα τα Contact Leads σε ένα admin dashboard,

να αλλάζεις status (New / Contacted / Proposal sent / Won / Lost),

να κρατάς internal notes & follow-up dates,

και να είναι κλειδωμένο πίσω από ένα απλό admin login.

Παρακάτω σου δίνω έτοιμο prompt για τον Claude Code, σε μορφή για καθαρό copy-paste.

---

You are working on the `in.today` Laravel 12 + Tailwind v4 project.

The public marketing site is in good shape: localization, branding, dark mode, contact form with DB persistence, GDPR cookie banner, legal pages, error pages, and analytics hooks are all implemented.

Next step: build a VERY SIMPLE internal “Leads Inbox” so the team can review and manage `ContactLead` entries without touching the database manually.

⚠️ IMPORTANT CONSTRAINTS

-   Do NOT change or break any existing public routes, copy, forms, or localization.
-   Keep the admin area minimal and secure, suitable for a small internal team (no multi-user system needed yet).
-   Reuse existing Tailwind + branding tokens (from BRANDING_v2.0) for a clean, professional look.

---

## A. Extend the ContactLead model & migration

1. Create a new migration to extend the existing `contact_leads` table with:

-   `status` (string, default 'new')
    -   valid values for now: 'new', 'contacted', 'proposal_sent', 'won', 'lost'
-   `internal_notes` (text, nullable)
-   `follow_up_at` (nullable datetime)

2. Update `app/Models/ContactLead.php`:

-   Add the new fields to `$fillable`.
-   Add a simple `STATUS_OPTIONS` constant or similar helper for valid statuses.

Run the migration locally (but DO NOT hard-code anything environment-specific).

---

## B. Simple admin authentication (HTTP Basic Auth)

For now, we only need a very simple admin protection, not a full auth system.

1. Create middleware `app/Http/Middleware/AdminBasicAuth.php` that:

-   Reads `ADMIN_USER` and `ADMIN_PASSWORD` from `.env`.
-   If they are not set, abort(403).
-   Uses HTTP Basic Auth to protect admin routes.
-   On wrong credentials, returns a proper 401 with `WWW-Authenticate` header.

2. Register the middleware in `bootstrap/app.php` as e.g. `admin.basic`.

3. Update `.env.example` with:

-   `ADMIN_USER=`
-   `ADMIN_PASSWORD=`

(Do NOT set real values; just show the keys.)

---

## C. Admin routes & controller

1. Add routes in `routes/web.php` under an `/admin` prefix.

-   Group them with:
    -   middleware: ['web', 'admin.basic']
    -   NO locale prefix (admin is English only for now).

2. Routes:

-   GET /admin/leads → index list
-   GET /admin/leads/{lead} → show details
-   POST /admin/leads/{lead} → update (status, internal_notes, follow_up_at)

3. Create `app/Http/Controllers/Admin/ContactLeadAdminController.php` with:

-   `index()`:

    -   Paginates leads (e.g. 20 per page), ordered by `created_at` desc.
    -   Allows optional filters via query string:
        -   `status` (one of the valid statuses)
        -   `locale`
        -   `search` (applied to restaurant_name, city, email)
    -   Passes these filters back to the view.

-   `show(ContactLead $lead)`:

    -   Shows full details for a single lead.

-   `update(Request $request, ContactLead $lead)`:
    -   Validates:
        -   `status` (in: new,contacted,proposal_sent,won,lost)
        -   `internal_notes` (nullable, string)
        -   `follow_up_at` (nullable, date)
    -   Saves changes and redirects back with a success flash message.

---

## D. Admin views (Tailwind, branded)

Create two Blade views and keep the design simple but polished:

1. `resources/views/admin/layout.blade.php` (base layout)

-   Minimal HTML layout (no localization needed, admin is English-only).
-   Top bar with:
    -   `in.today` wordmark (text only)
    -   Link to `/admin/leads`
    -   A small badge showing the total number of “new” leads if passed in (optional).
-   Use existing Tailwind classes and semantic tokens from app.css:
    -   background similar to dark mode page background
    -   white card backgrounds, subtle borders, focus states using accent color.

2. `resources/views/admin/leads/index.blade.php`

-   Uses the admin layout.
-   Page title: “Leads inbox”.
-   Filters section at the top:
    -   dropdown for status (All / New / Contacted / Proposal sent / Won / Lost)
    -   dropdown for locale (All / EN / DE / EL / IT)
    -   simple search input (restaurant name / city / email)
    -   “Apply filters” button.
-   Table listing:
    -   Columns: Created at, Locale, Restaurant name, City, Country, Type, Status, Budget, Actions.
    -   Each row has a “View” button linking to `/admin/leads/{id}`.
-   Status displayed as small colored pills:
    -   new → neutral blue
    -   contacted / proposal_sent → amber
    -   won → green
    -   lost → red

3. `resources/views/admin/leads/show.blade.php`

-   Uses the admin layout.
-   Shows all fields in a two-column layout:
    -   Left: basic info (name, email, phone, restaurant, city, country, locale, type, budget).
    -   Middle: services (list or tags).
    -   Right: metadata (source_url, ip_address, user_agent, created_at).
-   Below the details, an “Internal notes” card with a form:
    -   Select box for `status`.
    -   Date/time input for `follow_up_at` (type="datetime-local").
    -   Textarea for `internal_notes`.
    -   Primary Save button.
-   Show a small, subtle note: “Internal fields – not visible to the customer.”

---

## E. Integration & housekeeping

1. Ensure the admin routes do NOT interfere with the existing locale-based public routes.

2. Consider adding a small link for yourself only (e.g. in the footer as a comment, or no link at all). The admin URL is known and protected by Basic Auth, so it doesn’t need to be discoverable in the public UI.

3. Run:

-   `php artisan migrate`
-   `npm run build` (just to be safe if anything changed in assets)
-   `php artisan view:clear`
-   `php artisan route:clear`

4. Create a single commit with a message like:

-   `feat: add simple admin leads inbox with basic auth`

Finally, please summarize:

-   Which files you created/modified,
-   The exact environment variables that must be set for admin access,
-   Any TODOs or limitations (e.g. if we should later replace Basic Auth with a full user system).
