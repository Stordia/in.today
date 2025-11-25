# in.today – Digital Platform for Restaurants, Cafés, Bars & Clubs

**in.today** is a next-generation digital platform for the hospitality industry.

In Phase 1, this repository hosts the **marketing website** for in.today:
- Explaining what we do
- Who we serve (restaurants, cafés, bars, clubs)
- What is included in our service (websites, hosting, domains, photos, reservations)
- Transparent pricing for different plans

Future phases will extend this project into a full **B2B2C platform**:
- Restaurant discovery and search
- Real-time table reservations
- Restaurant dashboard
- API & embeddable widgets

---

## Tech Stack

- **Backend:** Laravel 12 (PHP 8.3+)
- **Frontend:** Blade templates + Tailwind CSS (via Vite)
- **Database:** MySQL (later, for the platform features)
- **Environment:** Local dev (Laravel Herd / Valet / Sail), production on Plesk / Ubuntu

---

## Phase 1 – Marketing Landing Page

### Goals

- One public landing page at `/` explaining:
  - What in.today is
  - Core features & benefits
  - Pricing plans
  - Roadmap & trust signals (case studies, testimonials – later)
  - Contact / CTA (e.g. “Request a call” or “Get a quote”)

- No authentication, no admin, no bookings yet.
- Only in.today branding (no mention of Stordia).

### Sections (draft)

1. **Hero**
   - Headline (value proposition)
   - Subheadline (for restaurants, cafés, bars, clubs)
   - Primary CTA (View Pricing / Request a Call)

2. **Who it’s for**
   - Small local restaurants
   - Casual dining & bistros
   - Bars & clubs
   - Fine dining & premium concepts

3. **What we deliver**
   - Custom website design (multi-language, up to 15 languages)
   - Domain (.de / .com / .gr)
   - Hosting & SSL
   - Menu & per-dish pages (SEO/AEO optimized)
   - Professional food & location photography (as add-on)
   - Reservation platform integration
   - Ongoing support & updates

4. **Pricing**
   - Clear plan comparison (Base / Pro / Prime)
   - One-time launch fee + monthly subscription

5. **How it works**
   - Step 1: Choose a plan
   - Step 2: Briefing & content
   - Step 3: Design & implementation
   - Step 4: Launch & continuous support

6. **FAQ**
   - Common questions about contracts, languages, timelines, domains, etc.

7. **Contact**
   - Simple form (in Phase 1 this can even be a mailto: link or very basic form)

---

## Getting Started (dev)

```bash
git clone git@github.com:YOUR-ACCOUNT/intoday-platform.git
cd intoday-platform

composer install
cp .env.example .env

# Configure DB and APP_URL in .env if needed
php artisan key:generate

npm install
npm run dev   # or build

php artisan serve
