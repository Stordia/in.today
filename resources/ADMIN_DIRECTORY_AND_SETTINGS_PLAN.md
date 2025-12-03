# Admin Directory & Settings – Plan

## 1. Στόχος

Να ολοκληρώσουμε ένα «κομμάτι» του admin backend πλήρως (κώδικας, DB, UX και tests) πριν προχωρήσουμε στο επόμενο:

- **Directory**: Χώρες & πόλεις για in.today (γεωγραφικό scope πλατφόρμας).
- **Settings**: Κεντρικές ρυθμίσεις πλατφόρμας μέσα από το /admin (χωρίς να πειράζουμε .env απευθείας).

Με αυτά κλείνουμε ένα καθαρό, δομημένο «Admin Foundation v1».

---

## 2. Directory – Countries & Cities

### 2.1. Data Model

- Προσθέτουμε **Country** entity, με στόχο:
  - Καθαρή λίστα χωρών που υποστηρίζει η πλατφόρμα.
  - Σύνδεση με **cities** (City belongsTo Country, Country hasMany Cities).
  - Δυνατότητα ενεργοποίησης/απενεργοποίησης χώρας (active flag).
- Τυπικά πεδία Country:
  - `id`
  - `name` (π.χ. "Germany")
  - `code` (π.χ. "DE")
  - `slug` (π.χ. "germany")
  - `eu_member` (bool – optional, μόνο αν μας χρειαστεί αργότερα)
  - `is_active` (bool)
  - timestamps

### 2.2. Admin UI (Filament)

- Νέα **CountryResource** στο group **Directory** μαζί με Cities/Cuisines/Restaurants.
- CRUD με:
  - Λίστα: name, code, is_active, πόσες πόλεις, πόσα restaurants.
  - Φόρμα: name, code, slug (auto), active toggle.
- **CityResource**:
  - Προσθήκη `country_id` (select με σχέση).
  - Φίλτρα: by country, by active.
  - Προβολή country στην λίστα.

### 2.3. Σύνδεση με Restaurants

- Restaurant:
  - Optional σχέση με `country_id` (εκτός από city).
  - Helper methods για εύκολη ανάγνωση `country_name`, `city_name`.
- Στα admin views (RestaurantResource, dashboards) εμφανίζουμε χώρα/πόλη όπου έχει νόημα.

---

## 3. Settings – Admin Settings Panel

### 3.1. Φιλοσοφία

- **.env** = περιβάλλον (secrets, hostnames, SMTP κτλ.) – δεν το πειράζουμε από UI.
- **DB Settings** = πράγματα που πρέπει να αλλάζει ο admin χωρίς deploy:
  - Γενικές πληροφορίες πλατφόρμας.
  - Default emails για ειδοποιήσεις.
  - Booking defaults.
  - Affiliate / commission defaults.

Άρα υλοποιούμε **πίνακα settings** ή structured settings model, και το UI διαχειρίζεται αυτά τα values.

### 3.2. Data Model / Architecture

Ελάχιστη viable λύση:

- Πίνακας `settings` με πεδία:
  - `id`
  - `group` (π.χ. `general`, `bookings`, `email`, `affiliate`)
  - `key` (π.χ. `platform_name`, `bookings.notification_email`)
  - `value` (json/text)
  - timestamps
- Helper class π.χ. `App\Support\AppSettings` με:
  - `get(string $key, $default = null)`
  - `set(string $key, $value)`
  - Methods τύπου `notificationEmail()`, `platformName()` κτλ.

Αργότερα μπορούμε (αν θέλεις) να πάμε σε package τύπου **spatie/laravel-settings**, αλλά για την ώρα κρατάμε custom, απλό και ελεγχόμενο.

### 3.3. Admin UI – Settings Section

Στο /admin:

- Νέο group **Settings** στο sidebar.
- Μια Filament Resource ή Panel Page:
  - Tabbed UI, με ενότητες:
    1. **General**
       - Platform name (in.today)
       - Default locale / fallback
       - Timezone (π.χ. Europe/Berlin)
    2. **Email & Notifications**
       - Default `from_name`, `from_email`
       - Default reservation notification email (fallback για restaurants)
       - Default proposal email (proposals@in.today)
    3. **Bookings Defaults**
       - Default booking_min_lead_time_minutes (global fallback)
       - Default booking_max_lead_time_days
       - Default booking_default_duration_minutes
    4. **Affiliates**
       - Default commission rate (%) suggestion
       - Minimum payout amount (π.χ. 50 EUR)
- Validation + safe defaults (αν δεν υπάρχει value, διαβάζουμε από config).

### 3.4. Wiring με Υπάρχον Κώδικα

- PublicBookingController + Reservation mailables:
  - Αν υπάρχει DB setting για emails/timeouts → το χρησιμοποιούμε.
  - Αν όχι → fallback σε config/services.php ή .env όπως είναι τώρα.
- Affiliate flows:
  - Default commission rate μπορεί να έχει global fallback από settings όταν δημιουργούμε νέο affiliate.

---

## 4. Phases Υλοποίησης (για Claude Code)

**Phase 1 – Country Directory**
1. Migration + Model Country.
2. Σχέση City ↔ Country.
3. Filament CountryResource.
4. Adjustment σε CityResource + RestaurantResource (εμφάνιση χώρας).

**Phase 2 – Settings Infrastructure**
1. Migration για `settings` table.
2. App\Support\AppSettings helper.
3. Basic seeder με βασικά default keys.

**Phase 3 – Settings Admin UI**
1. Filament Settings page/group.
2. Tabs: General, Email & Notifications, Bookings, Affiliates.
3. Validation, display of current effective values.

**Phase 4 – Wiring Existing Logic**
1. Booking emails να διαβάζουν notification addresses από settings (fallback σε config).
2. Booking defaults (lead time, duration) να διαβάζουν από settings αν δεν έχουν τιμή στο restaurant.
3. Affiliate default commission από settings (αν δεν setάρεις κάτι στο affiliate).

**Phase 5 – Tests & Cleanup**
1. Manual tests (βλ. χωριστό TESTS markdown).
2. Code cleanup / comments όπου χρειάζεται.
