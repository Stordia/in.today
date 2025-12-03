# TODO – Admin Directory & Settings

> Scope: Directory (Countries & Cities) + Settings Panel στο /admin

Χρησιμοποιούμε αυτό το αρχείο ως checklist για τον Claude Code (ή άλλο dev).

---

## Part 1 – Country Directory

- [ ] Δημιουργία migration για `countries` table
  - [ ] Πεδία: id, name, code, slug, is_active (bool, default true), timestamps
- [ ] Δημιουργία `App\Models\Country`
  - [ ] `hasMany(City::class)`
  - [ ] `hasMany(Restaurant::class)` (optional but χρήσιμο)
- [ ] Επέκταση `cities` table
  - [ ] Προσθήκη `country_id` (nullable με FK)
- [ ] Ενημέρωση `App\Models\City`
  - [ ] `belongsTo(Country::class)`
- [ ] Δημιουργία Filament `CountryResource`
  - [ ] List: name, code, is_active, cities_count, restaurants_count
  - [ ] Filters: active/inactive
  - [ ] Form: name, code, slug (auto from name), is_active toggle
  - [ ] Τοποθέτηση στο group **Directory**

---

## Part 2 – Cities & Restaurants Wiring

- [ ] CityResource – προσθήκη country select:
  - [ ] Field: `country_id` ως Select (relationship)
  - [ ] Filter by country
  - [ ] Column show: `country.name`
- [ ] Restaurant model – σύνδεση με χώρα
  - [ ] Προσθήκη `country_id` (nullable FK) στο restaurants table (αν δεν υπάρχει)
  - [ ] `belongsTo(Country::class)`
  - [ ] Helper accessors: `country_name`, `city_name`
- [ ] RestaurantResource – προβολή χώρας
  - [ ] Column για χώρα στην λίστα (toggleable)
  - [ ] Filter by country (προαιρετικό)

---

## Part 3 – Settings Infrastructure

- [ ] Δημιουργία migration για `settings` table
  - [ ] Πεδία: id, group (string), key (string), value (text/json), timestamps
  - [ ] Unique index σε (group, key)
- [ ] Δημιουργία `App\Models\Setting`
- [ ] Δημιουργία helper `App\Support\AppSettings`
  - [ ] Method: `get(string $key, $default = null)`
  - [ ] Method: `set(string $key, $value)`
  - [ ] Namespaces keys τύπου `general.platform_name`, `bookings.default_min_lead_time`
- [ ] Basic seeder (optional αλλά χρήσιμο)
  - [ ] Δημιουργία μερικών βασικών keys με sensibly defaults

---

## Part 4 – Settings Admin UI

- [ ] Δημιουργία Filament Settings Page/Resource στο /admin
  - [ ] Group: **Settings**
  - [ ] Tabs: General, Email & Notifications, Bookings, Affiliates
- [ ] Tab: General
  - [ ] Fields για: platform_name, default_locale, timezone
- [ ] Tab: Email & Notifications
  - [ ] Fields για: default_from_name, default_from_email
  - [ ] Default reservation notification email
  - [ ] Default proposals email (π.χ. proposals@in.today)
- [ ] Tab: Bookings
  - [ ] Booking default min_lead_time_minutes
  - [ ] Booking default max_lead_time_days
  - [ ] Booking default duration_minutes
- [ ] Tab: Affiliates
  - [ ] Default commission rate (%)
  - [ ] Minimum payout amount (EUR)

---

## Part 5 – Wiring Existing Logic to Settings

- [ ] PublicBookingController
  - [ ] Αν υπάρχει `settings(bookings.notification_email)` → να αντικαθιστά/συμπληρώνει το fallback από config.
  - [ ] Fallback ασφαλές αν δεν βρεθεί setting.
- [ ] Reservation mailables (confirmation & restaurant notification)
  - [ ] Χρήση settings για from_name / from_email όπου έχει νόημα.
- [ ] Affiliate defaults
  - [ ] Κατά τη δημιουργία νέου affiliate: προτείνουμε default commission από settings αν είναι κενό το πεδίο.

---

## Part 6 – Cleanup & Notes

- [ ] Έλεγχος ότι τα νέα Resources εμφανίζονται σωστά μόνο στο /admin (όχι στο business panel).
- [ ] Προσθήκη `noindex, nofollow` meta tags όπου χρειάζεται.
- [ ] Σύντομη τεκμηρίωση στο README ή σε ξεχωριστό docs file για:
  - [ ] Πώς προσθέτουμε νέες χώρες/πόλεις.
  - [ ] Πώς δουλεύει το Settings system.
