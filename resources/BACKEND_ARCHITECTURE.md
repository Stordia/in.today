# in.today – Backend Architecture

Version: 1.0  
Status: Draft – baseline for implementation  
Author: ChatGPT & Pantelis  
Date: 2025-11-27

---

## 1. Purpose & Design Goals

This document defines the **backend architecture** for the in.today platform.

Core goals:

1. **Global, multi-tenant SaaS** for restaurants, cafés, bars, clubs.
2. **B2B2C** model:  
   - B2B: restaurant operators & their staff  
   - B2C: end-consumers making reservations, writing reviews, discovering venues
3. **Strong foundation from day one**:
   - Scalable to thousands of restaurants and high traffic
   - Secure, auditable and compliant (GDPR-ready)
   - Extensible for new modules (reviews, loyalty, campaigns, etc.)
4. **One codebase, clear separation of concerns**:
   - Marketing site
   - Platform Admin (in.today team)
   - Restaurant Dashboard (operators)
   - Public / Consumer-facing frontend

This is **not** a throwaway MVP. We aim for something that can safely go to production and grow.

---

## 2. High-Level Architecture

### 2.1 Stack

- **Backend framework:** Laravel 12.x (PHP 8.4)
- **Admin UI:** Filament v4 (multiple panels)
- **Frontend (marketing / early B2C):** Blade + Tailwind CSS 4 + Vite
- **Future frontend:** Next.js / React app consuming the Laravel API (API-first design)
- **Database:** MySQL 8.x (single logical DB, multi-tenant via foreign keys)
- **Cache / queues:** Redis
- **Search (future):** Elasticsearch 8.x (discovery, geo search)
- **Storage:** Local + S3-compatible object storage for assets
- **Deployment:** Git → Plesk → dev.in.today (automated deploy script with composer/npm/artisan)

### 2.2 Monolith, Not Microservices

We start with a **modular monolith**:

- Single Laravel app
- Clear module boundaries (domains) inside `/app`
- APIs + Panels exposed from the same codebase

If future scale requires it, we can split certain domains (e.g. search, analytics, emails) into services, but we do **not** prematurely distribute the system.

---

## 3. Domains & Actors

### 3.1 Main actors

1. **Platform Admins (in.today team)**
   - Manage restaurants, subscriptions, global settings, leads
2. **Restaurant Accounts**
   - Restaurant owner
   - Manager
   - Staff
3. **End-Consumers (Customers)**
   - Make reservations
   - Manage their bookings
   - Write reviews, save favorites

### 3.2 Core domains

- **Identity & Access**
  - Users, roles, permissions
  - Authentication, sessions, tokens
- **Tenancy**
  - Restaurants, restaurant staff, restaurant configuration
- **Leads & CRM (internal)**
  - ContactLeads from the marketing site
  - Internal notes / statuses (later, via Filament)
- **Reservations**
  - Availability, reservations, deposits, status timeline
- **Content & Discovery (future)**
  - Cities, cuisines, features, restaurant profiles, search
- **Reviews & Reputation (future)**
  - Reviews, ratings, moderation
- **Billing & Plans (future)**
  - Plans, subscriptions, payments, invoices

---

## 4. Tenancy & Data Isolation

### 4.1 Tenancy model

- **Single database**, multi-tenant via `restaurant_id` foreign key.
- Every restaurant-related record references a **Restaurant** row:
  - `reservations.restaurant_id`
  - `tables.restaurant_id`
  - `opening_hours.restaurant_id`
  - `restaurant_users.restaurant_id`
- End-consumers (`customers`) are **global**: one `users` entry can book multiple restaurants.

This gives:

- Simpler operations & reporting
- Easier maintenance (migrations, backups)
- Clear scoping in policies & Filament resources

### 4.2 Future scalability

If the platform grows massively, we can:

- Shard by `restaurant_id` range or region
- Promote read-heavy domains (e.g. search, logs) to dedicated services
- Introduce read replicas for MySQL

The domain model is designed to allow sharding without massive refactors.

---

## 5. Authentication & Authorization

### 5.1 Users table

Single `users` table for **all people** in the system:

- Platform admins (in.today)
- Restaurant owners / managers / staff
- End-consumers

Proposed base schema (simplified):

```sql
users
- id (PK)
- uuid (string, unique)
- name
- email (unique)
- password (nullable for OAuth-only accounts)
- role (enum: platform_admin, platform_ops, restaurant_owner, restaurant_manager, restaurant_staff, customer)
- restaurant_id (nullable FK → restaurants.id)  -- only for restaurant_* roles
- phone (nullable)
- language (locale, e.g. 'en', 'de', 'el')
- last_login_at
- created_at, updated_at
```

For more granular permissions (later), we can introduce:

- `spatie/laravel-permission` (roles + permissions)
- Policy classes for each aggregate root

### 5.2 Auth flows

- **Admins & restaurant staff:**
  - Laravel session auth via `web` guard
  - Filament panels protected by role + policies
- **End-consumers:**
  - Start with session auth (Laravel Breeze-like flow)
  - Future: issue API tokens / JWT for mobile apps

### 5.3 Authorization strategy

- **Gates / Policies**:
  - `RestaurantPolicy` – admins vs owners vs managers
  - `ReservationPolicy` – staff can only see their own restaurant’s data
- **Filament Panels** use:
  - `can()` checks per panel (panel registration)
  - Per-resource policies to enforce `restaurant_id` scoping

---

## 6. Panels & Frontends

### 6.1 Public / Marketing site

- URL: `/{locale}` (e.g. `/en`, `/de`, `/el`, `/it`)
- Implemented with Blade, Tailwind, dark mode, SEO optimized
- Features:
  - Landing page
  - Multilingual content
  - Contact form → `ContactLead` DB + email
  - Legal pages (Imprint, Privacy)
  - Cookie banner + analytics placeholders (GTM, etc.)

### 6.2 Platform Admin Panel (Filament)

- URL: `/admin`
- Audience: in.today internal team
- Guard: `web`
- Accessible roles: `platform_admin`, `platform_ops`, `platform_readonly`
- Responsibilities:
  - Manage `restaurants` (create, verify, deactivate)
  - Manage `restaurant_users` (owners, managers, staff)
  - View & manage `contact_leads` (internal mini-CRM)
  - View global analytics & system health (later)
  - Configure plans, billing settings, feature flags (later)

### 6.3 Restaurant Panel (Filament)

- URL: `/dashboard` (or `/app`)
- Audience: restaurant owners, managers, staff
- Guard: `web`
- Context: scoping by `restaurant_id`
- Responsibilities:
  - Daily operations:
    - Reservations calendar (day/week views)
    - Accept / reject / modify bookings
    - Mark no-shows & walk-ins
  - Configuration:
    - Tables, opening hours, blocked dates
    - Deposit policy
    - Restaurant profile (media, description, features)

### 6.4 Consumer-facing app (future)

- Implementation options:
  - Next.js SPA/SSR using Laravel as an API backend
  - Or continue with Blade for initial version
- Features:
  - City/area discovery
  - Restaurant profile pages
  - Reservation flow
  - User account (favorites, reservations, reviews)

The marketing code we have today will be reusable as the “top layer” of the B2C app.

---

## 7. Data Model – Key Entities

### 7.1 Restaurants

- `restaurants` table (already roughly defined in the platform spec)
- Core attributes:
  - Name, slug, tagline, description
  - City, address, geo-coordinates
  - Contact info, website, social profiles
  - Price range, cuisine, features
  - Settings JSON (opening rules, deposits, etc.)
  - Plan & subscription status
  - Status flags: active, verified, featured

### 7.2 Restaurant Users

- `restaurant_users` as a linking table:
  - `restaurant_id`, `user_id`
  - Role within the restaurant: owner / manager / staff
  - (Allows a user to be associated with multiple restaurants if needed)

### 7.3 Reservations

- `reservations` table according to initial spec:
  - Restaurant, user (nullable), customer details
  - Date, time, guests, duration
  - Status (pending, confirmed, canceled, no-show, completed)
  - Deposit fields (amount, paid, refunded)
  - Source (platform, widget, phone, walk_in)
  - Metadata (IP, user-agent, language)
- Supporting tables:
  - `tables`
  - `opening_hours`
  - `blocked_dates`
  - `notification_log`

### 7.4 Leads (internal mini-CRM)

- `contact_leads` (already implemented):
  - Locale, name, email, phone
  - Restaurant name, city, country, website
  - Type (restaurant, group, agency, other)
  - Services (JSON array)
  - Budget
  - Message
  - Metadata: IP, user-agent, URL referrer
  - Internal fields (future): status, owner, next_action_at, notes

Filament will provide the UI to treat this as a **lightweight CRM**.

---

## 8. Application Layers & Modules

We keep a **layered architecture** inside the Laravel app:

1. **HTTP layer**
   - Controllers, Requests, Resources (API)
   - Blade views (marketing)
2. **Domain / Application layer**
   - Service classes for business logic (e.g. ReservationService, AvailabilityService)
   - Responsible for orchestration, not DB details
3. **Infrastructure / Persistence**
   - Eloquent models
   - Repositories (where it makes sense)
   - External integrations (Stripe, SMS, Mail, Maps)

Key principles:

- Controllers stay thin
- Business rules live in services/use-cases
- Eloquent is used, but not everywhere in controllers (avoid fat controllers)

---

## 9. Integrations & Async Processing

### 9.1 Integrations (current / planned)

- **Email**: Mailgun / SMTP
- **SMS**: 46elks (confirmation, reminders)
- **Payments**: Stripe (Stripe Connect for deposits per restaurant)
- **Maps**: Google Maps or Mapbox for geocoding & map embeds
- **Analytics**: Google Tag Manager, Meta Pixel, more via cookie consent

### 9.2 Queues & async

All “slow” or external tasks should go to queues:

- Sending emails
- Sending SMS
- Syncing with external services
- Heavy reports

We use:

- Redis as queue driver
- Laravel Horizon for monitoring & scaling workers

---

## 10. Logging, Monitoring & Observability

### 10.1 Logging

- Laravel’s default logging to rotating files
- Structured logs for key events: reservations, payments, errors
- Error tracking (future): Sentry/Bugsnag/etc.

### 10.2 Health checks & metrics (future)

- Basic health endpoints for uptime monitoring
- Aggregated metrics (reservations per minute, failed jobs, etc.)

---

## 11. Security & Compliance

### 11.1 Security

- HTTPS everywhere (in production)
- `APP_KEY` properly set, encryption where needed
- Input validation & sanitization for all user-facing forms
- Authorization policies on every sensitive resource
- Rate limiting for public endpoints (login, reservation creation, etc.)
- CSRF protection for forms
- Safe file uploads (MIME & extension checks, store outside web root / in object storage)

### 11.2 GDPR & Privacy

- Cookie banner + consent management
- Clear Privacy Policy (already started)
- Ability to export & delete user data (future)
- Minimization of stored PII (store only what is necessary)
- IP / user-agent usage documented for security/fraud prevention

---

## 12. Extensibility & Roadmap

The architecture is intentionally **modular** so we can add:

- Reviews & reputation system
- Loyalty / points & customer profiles
- Campaigns & messaging (email/SMS to customers)
- Marketplace features (e.g. paid placements, promotions)
- White-label solutions for agencies & chains

Each new feature should:

1. Introduce its own domain model (tables, services)
2. Integrate with existing roles & panels (Admin / Restaurant)
3. Expose APIs when needed (for partners or external apps)

---

## 13. Implementation Order (Backend-Side)

Suggested backend order (high level):

1. **Finalize & migrate core tables**:
   - users, restaurants, restaurant_users, contact_leads
2. **Set up Filament Panels**:
   - Platform Admin panel with login for platform_admin
   - Add ContactLeadResource (read-only at first)
3. **Restaurant tenancy foundation**:
   - RestaurantPanel skeleton with proper `restaurant_id` scoping
4. **Reservations domain**:
   - Schema, models, services, basic Filament UI
5. **Public B2C flows**:
   - Simple reservation flow, connected to Restaurant settings
6. **Payments, SMS, advanced features** (incrementally)

This way, we keep the system **coherent, secure, and ready to scale** before opening the doors to many restaurants.


---

## 14. White‑label & Agency Tenants

Even if we start as a single brand (**in.today**), the backend should be ready for **white‑label** and **agency** scenarios without a rewrite.

### 14.1 Tenant Types

We already distinguish:

- **Platform tenant** → the global in.today brand (marketing site, master admin)
- **Restaurant tenants** → individual restaurants / bars / cafés

We add a logical third layer:

- **Agency tenants** → digital agencies, consultants or resellers who onboard restaurants on our platform under their own “umbrella”.

This does *not* mean a fully separate database per agency. Instead, we model it like this:

- `agencies` table (or `partners`) with:
  - name, slug, contact info
  - branding options (logo, colors, domain/subdomain)
  - billing configuration (who pays for what)
  - status flags (active, suspended)
- `agency_users` table:
  - maps users to an agency (`agency_id`, `user_id`, role: owner/manager/staff)
- Link to restaurants:
  - `restaurants.agency_id` (nullable) → which agency “owns” this restaurant relationship

This keeps the core **multi‑tenant layer simple** (restaurant scoped) while enabling extra grouping and branding on top.

### 14.2 White‑label Branding

Per agency we allow:

- **Custom domain**: e.g. `booking.foodmedia.de` or `restaurant-tool.myagency.com`
- **Custom logo**: header logo in B2B dashboard & embeddable widgets
- **Custom colors**: a small theme palette overriding defaults
- **Powered by in.today**: optional / reduced branding in white‑label mode

Technically:

- Agency config is cached (per `agency_id`)
- Restaurants inherit branding from their agency when present, otherwise from global defaults
- Widgets / dashboard panels read “effective branding” via a small `BrandingService`

### 14.3 Panels & Roles for Agencies

We extend the panel matrix:

- **Platform Admin Panel**
  - Manages agencies (create, suspend, billing overview)
  - Sees all restaurants
- **Agency Panel**
  - Scope: `agency_id`
  - Can create / manage restaurants under that agency
  - Can invite restaurant users and agency staff
  - See metrics across their portfolio (MRR, active restaurants, reservations)
- **Restaurant Panel**
  - Scope: `restaurant_id`
  - Unchanged, but knows whether it belongs to an agency (for support contact, branding, invoices)

Roles at agency level:

- `agency_owner` → full control over agency & billing
- `agency_manager` → manage restaurants, no billing changes
- `agency_staff` → limited operational access (support, configuration for clients)

### 14.4 Billing Models

We should support multiple commercial models without changing core logic every time:

1. **Direct billing (default)**  
   - Restaurant has a subscription with in.today (Stripe customer)
   - Agency only has a “referral” or commission agreement

2. **Agency‑billed**  
   - Agency pays a global invoice to in.today
   - Agency then charges their restaurants however they want (we don’t need to know)
   - In our system: restaurants under that agency have `billing_mode = agency`, no direct Stripe customer

3. **Hybrid / reseller**  
   - Agency uses our Stripe Connect setup
   - Commissions and revenue share are handled via Connect transfers

The important part is to design **billing metadata** so that we can easily recognize:

- Who pays us (restaurant or agency)
- Who is allowed to manage plan changes
- How we calculate commissions

We can model this with a `subscriptions` table that has:

- `billable_type` / `billable_id` polymorphic (restaurant or agency)
- `plan`, `status`, `renews_at`, `cancels_at`
- `external_id` (Stripe subscription id)

### 14.5 White‑label Emails & Legal

For serious white‑label use we should consider:

- From address & reply‑to per agency (e.g. `noreply@myagency.com` with appropriate DNS setup)
- Agency logo in email templates
- Country‑specific legal text / footer per agency (imprint, privacy link)
- Optional “Powered by in.today” footer with link to our own site (configurable per contract)

Implementation‑wise this means:

- Email templates accept a **branding payload** (logo, colors, links)
- Branding payload is resolved at send time, based on restaurant → agency → platform chain

---

## 15. Affiliate & Referral System

We will almost certainly want partners, influencers and agencies to promote in.today. Instead of bolting this on later, we can design a **clean, minimal affiliate core** now.

### 15.1 Affiliate Actors

Basic entities:

- `affiliates` table:
  - name, email
  - type: `individual`, `company`, `agency`
  - payout method metadata (IBAN, Stripe Connect account, etc.)
  - status: pending, active, blocked
- `affiliate_links`:
  - `affiliate_id`
  - `code` (e.g. `PANTELIS2025` or hash)
  - optional `landing_url` override
  - tracking parameters (utm_source, utm_campaign)

This gives us a **unique trackable code** without polluting the main user model.

### 15.2 Tracking Model

We can start simple:

1. Visitor clicks an affiliate link →  
   We set a **tracking cookie** with the affiliate code & timestamp.
2. Visitor signs up a restaurant / creates a paid subscription →  
   We link the restaurant (or subscription) to that affiliate if the cookie is present and not expired.

Tables:

- `affiliate_clicks`:
  - `affiliate_id`, `code`
  - timestamp, ip, user_agent, landing_url
- `affiliate_conversions`:
  - `affiliate_id`
  - `restaurant_id` or `subscription_id`
  - commission amount (calculated at time of event)
  - status: pending, approved, paid
  - metadata (plan, term)

The **commission logic** (percentage, fixed amounts, recurring vs one‑time) should live in a dedicated `AffiliateService`, not hard‑coded in controllers.

### 15.3 Commission Strategies

We should support at least:

- **One‑time bounty**: e.g. 50€ when a restaurant upgrades to a paid plan
- **Revenue share (recurring)**: e.g. 20% of monthly fee for 12 months
- **Hybrid**: small one‑time + smaller recurring share

Configuration ideas:

- A global default commission profile
- Optional custom profile per affiliate (for large partners)
- Stored in a table like `affiliate_plans` referenced by `affiliate_id`

Later we can expose this via Filament:

- Admin panel → manage affiliates, see their performance
- Affiliate portal → see clicks, conversions, payouts

### 15.4 Technical Integration

On the technical side:

- **Front‑end**: A small JS snippet that reads an `?ref=` or `?aff=` parameter and stores it in a cookie/localStorage.
- **Back‑end**: Middleware that reads the tracking cookie and includes `affiliate_id` when creating a `Restaurant`, `Subscription`, or `Agency`.
- **Security**: Codes should be opaque (not guessable), and we should avoid sensitive data in URLs.

We do *not* need to implement this fully now, but designing routes and data structures with this in mind avoids breaking changes later (for example, making sure subscriptions and restaurants have a clear “origin” field that can be linked to an affiliate).

---

## 16. Ecosystem & Extensibility

Because in.today will likely grow into a small ecosystem, it’s smart to explicitly design **extension points**:

### 16.1 Webhooks

Allow restaurants, agencies or partners to subscribe to events like:

- `reservation.created`
- `reservation.confirmed`
- `reservation.cancelled`
- `review.created`
- `subscription.activated`
- `subscription.cancelled`

Core model:

- `webhook_endpoints`:
  - owner (restaurant, agency)
  - target URL, secret, status
- `webhook_events`:
  - queued payloads, retry logic, status

We can later expose this via a simple Filament resource in relevant panels.

### 16.2 Public Partner API

The public API we already sketched for search & reservations can be extended for:

- **Agency integrations**: sync a restaurant list from an external CRM
- **POS vendors**: push no‑shows / walk‑ins / actual covers back to in.today
- **Marketing tools**: pull booking statistics for reporting

Design principles:

- REST + API keys or OAuth for larger partners
- Clear rate limiting
- Versioned endpoints (`/api/v1`, `/api/v2`) to allow evolution without breaking existing integrations

### 16.3 Feature Flags & Config

To roll out big features safely (e.g. new dashboard, new map search, new review system), we should have:

- A `feature_flags` table (or config JSON) with:
  - flag name
  - status: global on/off
  - optional targeting: by restaurant, by agency, by country
- A small `FeatureService` or helper `feature('name')` used in both Blade and PHP.

This keeps the platform stable even as we experiment with new capabilities.

---

## 17. How This Influences Our Next Steps

Adding agencies, white‑label, and affiliates at the **architecture level** changes how we approach upcoming work:

1. When we create the **Filament panels**, we design them with:
   - Platform Admin Panel
   - Agency Panel (even if minimal at first)
   - Restaurant Panel
2. When we build **subscriptions & billing**, we keep `billable_type` polymorphic from day one (restaurant or agency).
3. When we store **restaurants**, we include optional `agency_id` and `affiliate_id` fields (or a generic `origin` model).
4. When we define **emails & branding**, we resolve branding via a hierarchy: restaurant → agency → platform.

We don’t have to implement every single feature right now, but documenting them in `BACKEND_ARCHITECTURE.md` ensures that:

- Future decisions don’t clash with the long‑term vision.
- Claude Code, future teammates, or your future self can see the “big picture” and implement new modules in a compatible way.

This extended document now covers:

- Core multi‑tenant model (platform, agencies, restaurants)
- Back‑office panels & roles
- Reservation & lead data
- White‑label capabilities
- Affiliate & referral system
- Extension points (webhooks, APIs, feature flags)

Ready for **serious, scalable growth**—without painting us into a corner later.
