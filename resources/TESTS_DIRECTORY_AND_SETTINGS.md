# TESTS_DIRECTORY_AND_SETTINGS.md

Step-by-step tests for the **Directory (Countries & Cities)** and **Platform Settings** parts of in.today.

> Tip: Do the tests while logged in as `admin@in.today.test` in `/admin`.

---

## 1. Countries – Basic CRUD & Listing

1. Go to **Admin → Directory → Countries** (`/admin/countries`).
2. Confirm at least ~200+ countries exist (check pagination footer).
3. Use the search box and search for:
   - `Germany`
   - `Greece`
   - `United States`
4. For one country (e.g. **Germany**):
   - Open it in **Edit**.
   - Verify:
     - `Name` = `Germany`
     - `Code` is set (e.g. `DE`)
     - `Slug` looks good (e.g. `germany`)
     - `Active` is enabled.
   - Change `Name` slightly (e.g. `Germany (Test)`), click **Save**, confirm list updates.
   - Change it back to `Germany`, **Save** again.

✅ Expected: Countries list works, search works, create/edit works, and no errors appear.

---

## 2. Cities – Basic CRUD & Country Relation

1. Go to **Admin → Directory → Cities** (`/admin/cities`).
2. Use the filters / search to find:
   - `Berlin`
   - `Athens`
   - `New York`
3. For one city (e.g. **Berlin**):
   - Open **Edit**.
   - Verify fields:
     - `Name` = `Berlin`
     - `Country` = `Germany`
     - `Admin name` (e.g. `Berlin`)
     - `Latitude` / `Longitude` are filled.
   - Change `Admin name` (e.g. `Berlin (City Test)`), **Save**, confirm list updates.
   - Change it back and **Save** again.
4. Check **Country filter** in the Cities table:
   - Filter by `Germany` → list should show German cities only.
   - Filter by `Greece` → only Greek cities.

✅ Expected: Each city is correctly linked to a country, filters work, and CRUD is stable.

---

## 3. Restaurant Forms – Country / City Usage

### 3.1 Admin panel – Restaurant Resource

1. Go to **Admin → Directory → Restaurants** (`/admin/restaurants`).
2. Click **Create** (or edit an existing restaurant):
   - Verify there is a **Country** select.
   - Verify there is a **City** select (or relation) influenced by country (if implemented).
3. Create a **test restaurant**:
   - Name: `Test Bistro Directory`
   - Country: `Germany`
   - City: `Berlin` (if available)
   - Fill minimal required fields and **Save**.
4. Back in the list:
   - Use **Country filter** for `Germany` and confirm your test restaurant appears.
   - Use search with `Test Bistro Directory` and confirm it’s found.

✅ Expected: Restaurant form supports countries/cities, and filtering by country/city works.

> Afterwards you can delete the test restaurant.

---

## 4. World Import – Sanity Checks

1. Open **Admin → Directory → Countries** and scroll through a few pages:
   - Check that multiple regions appear (Europe, North America, Asia, etc.).
2. Open **Admin → Directory → Cities**:
   - Use search to quickly test different regions:
     - `Tokyo`
     - `Sydney`
     - `Toronto`
     - `Madrid`
3. Confirm each city you open is linked to the correct **Country**.

✅ Expected: Data quality feels correct and global coverage is present.

---

## 5. Platform Settings – Email & Bookings

### 5.1 Open Settings Page

1. Go to **Admin → Settings → Platform Settings** (`/admin/platform-settings`).
2. Confirm you see 4 main sections:
   - **Email**
   - **Bookings & Reservations**
   - **Affiliates**
   - **Technical**

✅ Expected: Page loads without error, only visible to platform admins.

---

### 5.2 Email Settings

1. In **Email** section:
   - Set `From address` to e.g. `noreply@in.today`.
   - Set `From name` to `in.today Test`.
   - Optionally set `Reply-to` address (e.g. `support@in.today`).
2. Click **Save Settings**.
3. Trigger any email flow (e.g. create a test reservation via public booking page or send a test email from CRM if available).
4. Inspect the received email headers:
   - `From:` must match the values you set.
   - `Reply-To:` (if set) should match that setting.

✅ Expected: Changing email settings in the admin is reflected in outgoing emails.

---

### 5.3 Bookings & Reservations Settings

1. In **Bookings & Reservations** section:
   - Toggle **Send customer confirmation** ON.
   - Toggle **Send restaurant notification** ON.
   - Set **Default notification email** to a test address you control.
2. Click **Save Settings**.
3. Go to a public booking page (e.g. `/book/{slug}` for a restaurant that has bookings enabled).
4. Create a new test reservation.
5. Verify:
   - Customer receives confirmation email.
   - Restaurant / default notification email receives notification.

Then:

6. Turn OFF one of the toggles (e.g. **Send restaurant notification**).
7. Save and create another test reservation.
8. Check that **only** the toggled-on email is sent.

✅ Expected: Booking-related emails fully controlled by settings toggles.

> You can delete test reservations afterwards from the Business Panel or Admin.

---

## 6. Affiliates Settings – Basic Sanity

> Detailed affiliate tests are in `TESTS_AFFILIATES.md`. Here we only verify that settings save.

1. In **Affiliates** section of Platform Settings:
   - Change the **Default commission rate** to a different value (e.g. from 20% to 25%).
   - Adjust **Payout threshold** if needed.
   - Adjust **Cookie lifetime (days)** if needed.
2. Click **Save Settings**.
3. Go to **Admin → Partners → Affiliates** and verify that:
   - Newly approved conversions use the updated default commission rate (if commission was previously null).

✅ Expected: Affiliate-related settings save correctly and affect new conversions.

---

## 7. Technical Settings – Maintenance & Log Level

1. In **Technical** section:
   - Toggle **Maintenance mode** ON.
   - Set **Log level** to `warning`.
2. Click **Save Settings**.

> For now, this is more about persistence. Full integration with Laravel’s native maintenance/logging can be wired later.

3. Reload the **Platform Settings** page and confirm your choices persist.

✅ Expected: Technical settings save and reload correctly (even if they’re not yet fully wired to the framework).

---

## 8. Final Regression Check

1. Visit `/admin` dashboard – ensure:
   - No errors related to Settings or Directory.
2. Visit:
   - `/admin/countries`
   - `/admin/cities`
   - `/admin/restaurants`
   - `/admin/platform-settings`
3. All pages must render without exceptions or JS console errors.

✅ If everything above passes, we can consider the **Directory + Settings** chapter stable and move on to the next feature set.
