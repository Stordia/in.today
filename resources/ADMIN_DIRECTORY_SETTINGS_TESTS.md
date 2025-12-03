# ADMIN_DIRECTORY_SETTINGS_TESTS.md

Manual test checklist for the **Admin Directory + Platform Settings** chapter of in.today.

## 0. Meta

- Environment: `dev.in.today`
- User: `admin@in.today.test` (Platform Admin), password: `Demo123!`
- Browser: Test in latest Chrome (desktop)
- Panels used:
  - Platform Admin Panel → `/admin`
  - Business Panel (only where explicitly mentioned) → `/business`

---

## 1. Login & General Admin Checks

1.1 **Admin login works**
- [ ] Open `/admin/login`
- [ ] Log in with `admin@in.today.test` / `Demo123!`
- [ ] You land on the Filament admin dashboard without errors.

1.2 **Directory + Settings groups visible**
- [ ] In the sidebar, you see a group **“Directory”** with:
  - [ ] Countries
  - [ ] Cities
  - [ ] Restaurants
- [ ] You also see a group **“Settings”** with:
  - [ ] “Platform Settings” entry

---

## 2. Countries Management (Directory → Countries)

2.1 **Countries list renders**
- [ ] Go to `/admin/countries`
- [ ] Table loads without console errors.
- [ ] Columns visible include at least: Name, Code (if set), Active, Cities count, Restaurants count.

2.2 **Create a new country**
- [ ] Click **Create Country**.
- [ ] Fill:
  - Name: `Testland`
  - Code: `TL`
  - Slug: leave empty (auto-generate)
  - Active: ON
- [ ] Save.
- [ ] New row `Testland` appears in the table.
- [ ] Slug auto-generated (e.g. `testland`).

2.3 **Edit country**
- [ ] Open the row action **Edit** for `Testland`.
- [ ] Change **Name** to `Testlandia` and keep code `TL`.
- [ ] Save.
- [ ] Table shows updated name `Testlandia`.

2.4 **Deactivate country**
- [ ] Edit `Testlandia`, toggle **Active** OFF.
- [ ] Save.
- [ ] In the table, the “Active” indicator reflects the inactive state.

2.5 **Filters & search**
- [ ] Use the search box with `Testlandia` → row is found.
- [ ] Use any “Active” filter (if available) to hide/show inactive countries.

---

## 3. Cities Management (Directory → Cities)

3.1 **Cities list renders**
- [ ] Go to `/admin/cities`.
- [ ] Table loads without console errors.
- [ ] Columns show at least: Name, Country, Active status.

3.2 **Create a city linked to Testlandia**
- [ ] Click **Create City**.
- [ ] Fill:
  - Name: `Test City`
  - Slug: leave empty for auto-generation
  - Country: select `Testlandia`
  - Active: ON
- [ ] Save.
- [ ] Table shows `Test City` with country `Testlandia`.

3.3 **City filters**
- [ ] Use the Country filter (if available) and filter by `Testlandia`.
- [ ] Only cities from `Testlandia` should show (including `Test City`).

3.4 **City → Restaurant link (later verification)**
- [ ] Just note: this city will be used in the next step when creating a Restaurant.

---

## 4. Restaurants (Directory → Restaurants)

4.1 **Restaurants list renders**
- [ ] Go to `/admin/restaurants`.
- [ ] Table loads without console errors.
- [ ] Columns show: Name, Country, City, Plan, Status indicators, etc.

4.2 **Create a test restaurant for Testlandia**
- [ ] Click **Create Restaurant**.
- [ ] Fill minimally:
  - Name: `Test Bistro`
  - Country: `Testlandia`
  - City: `Test City`
  - (Keep other fields minimal/valid: time zone, plan, booking_enabled, etc.)
- [ ] Save.
- [ ] Table shows `Test Bistro` with country `Testlandia` and city `Test City`.

4.3 **Filters by country/city**
- [ ] Apply the **Country** filter: select `Testlandia`.
- [ ] The restaurant `Test Bistro` appears.
- [ ] If a **City** filter is available, select `Test City` → still see `Test Bistro`.

4.4 **Tenancy sanity check in Business Panel (optional)**
- [ ] Ensure there is a `RestaurantUser` linking a user to `Test Bistro` (this may be done via seeder or manually).
- [ ] Log in as that restaurant user in `/business/login`.
- [ ] Check that the Business panel loads without errors (no need to test booking features here yet).

---

## 5. App Settings Infrastructure (quick sanity)

Here ελέγχουμε μόνο ότι το σύστημα ρυθμίσεων δουλεύει και δεν σπάει κάτι. Δεν χρειάζεται Tinker, όλα μέσω UI.

5.1 **Settings group visible only to platform admin**
- [ ] While logged in as `admin@in.today.test`, you see **Settings → Platform Settings**.
- [ ] Log out.
- [ ] Log in as a **non-admin** user (e.g. `owner.single@in.today.test`).
- [ ] You do **not** see the Settings group in `/admin` (or you cannot access `/admin` at all, depending on configuration).

---

## 6. Platform Settings Page – Load & General UX

6.1 **Open page**
- [ ] Log in again as `admin@in.today.test`.
- [ ] Go to `/admin/platform-settings` (or click “Platform Settings” in the sidebar).
- [ ] Page loads without errors.
- [ ] You see a single large form with sections:
  - Email
  - Bookings & Reservations
  - Affiliates
  - Technical

6.2 **Default values visible**
- [ ] **Email section** shows pre-filled values (from AppSettings or config fallbacks).
- [ ] **Bookings & Reservations** toggles have an ON/OFF state (not all empty).
- [ ] **Affiliates** numeric fields show current defaults (commission %, threshold, cookie days).
- [ ] **Technical** fields show a current log level and maintenance toggle state.

---

## 7. Email Settings – Functional Check

7.1 **Update email from name/address**
- [ ] In **Email** section, change:
  - From address: e.g. `noreply+test@in.today`
  - From name: e.g. `in.today Dev Test`
- [ ] Click **Save** (or equivalent button).
- [ ] A success notification appears.

7.2 **Verify AppSettings persistence (visible)**
- [ ] Refresh the page (`Cmd+R` / `Ctrl+R`).
- [ ] The Email section still shows the updated values.

7.3 **(Optional) Verify via real email**
*(Only if mail config is correctly set up and you want to test end-to-end)*

- [ ] On the public booking page, create a test reservation with a real email (e.g. yours).
- [ ] Confirm that the received email has:
  - From: `in.today Dev Test <noreply+test@in.today>`

If mail is not configured, skip this step and just rely on the UI persistence.

---

## 8. Booking Settings – Functional Check

8.1 **Toggle booking confirmation emails**
- [ ] In **Bookings & Reservations** section:
  - Toggle **Send confirmation to customer** OFF.
  - Toggle **Send notification to restaurant** OFF.
- [ ] Click **Save**.
- [ ] Refresh page → both toggles should remain OFF.

8.2 **Behavior check (optional, if mail is configured)**
- [ ] Make a booking on a test restaurant (`/book/{slug}`).
- [ ] Verify that **no emails** are sent to customer or restaurant for this booking.
  - (You can re-enable the toggles later.)

8.3 **Default notification email**
- [ ] Set **Default restaurant notification email** to e.g. `bookings+test@in.today`.
- [ ] Save and refresh.
- [ ] The field retains this value.
- [ ] (Optional) Create a booking for a restaurant that does not have a specific notification email and verify the fallback is used.

---

## 9. Affiliate Settings – Functional Check

9.1 **Change commission defaults**
- [ ] In **Affiliates** section:
  - Set **Default commission rate (%)** to `25`.
  - Set **Payout threshold (EUR)** to `200`.
  - Set **Cookie lifetime (days)** to `45`.
- [ ] Save and refresh page.
- [ ] Values remain as entered.

9.2 **Create a new Affiliate to verify defaults (admin panel)**
- [ ] Go to `/admin/affiliates`.
- [ ] Create a new Affiliate, leaving commission fields at defaults (if such exist).
- [ ] Check that new affiliate uses the updated default commission rate where applicable
  (this depends on how defaults were wired; if not visible, you can skip this check and only verify persistence).

---

## 10. Technical Settings – Functional Check

10.1 **Maintenance flag toggle**
- [ ] In **Technical** section:
  - Toggle **Logical maintenance flag** ON.
- [ ] Save and refresh.
- [ ] The toggle remains ON.
- [ ] Note: this does *not* run `artisan down/up`, it’s just a logical flag for future use.

10.2 **Log level select**
- [ ] Change **Log level** from current value to `warning`.
- [ ] Save and refresh.
- [ ] The select still shows `warning`.
- [ ] Confirm that nothing in the system breaks after this change (no errors when navigating admin / business / public pages).

---

## 11. Cleanup

11.1 **Test data cleanup (optional but recommended)**
- [ ] Delete test entities if you don’t need them:
  - Country `Testlandia`
  - City `Test City`
  - Restaurant `Test Bistro`
  - Any test affiliate or test bookings created during checks.

11.2 **Reset Platform Settings (optional)**
- [ ] In Platform Settings, restore production-like values:
  - Email addresses you actually plan to use.
  - Booking toggles to your desired defaults.
  - Affiliate values (e.g. 20% / 100 EUR / 30 days).
  - Technical maintenance flag OFF, log level back to your desired default.

---

When all boxes above are ✅, the **Admin Directory + Platform Settings** chapter can be considered stable and ready for real usage.
