# Affiliate System – Manual QA Checklist

This document helps you test the **Affiliate system** in `in.today` step by step, without needing a second screen.  
Work through the sections in order and tick the boxes as you go.

---

## 0. Prerequisites

- [ ] You can log in to the **Admin panel** at `/admin` as `admin@in.today.test` (password `Demo123!` or current).
- [ ] Database migrations are up to date on this environment.
- [ ] Mail is configured so that Contact Lead emails can be sent (or at least logged).

---

## 1. Create a Test Affiliate + Link

1. Go to: `/admin/affiliates`.
2. Click **Create** and enter:
   - [ ] Name: e.g. `Stordia`
   - [ ] Type: `agency` or `blogger` (anything is fine for testing)
   - [ ] Status: `active`
   - [ ] Default commission rate: e.g. `100` (EUR)
3. Save the affiliate.
4. On the **View Affiliate** page:
   - [ ] In the **Links** relation, click **Create**.
   - [ ] Slug: e.g. `stordia`
   - [ ] Target URL: the landing page, e.g. `https://dev.in.today/en`
   - [ ] Active: ✅ enabled
5. After saving the link:
   - [ ] Confirm the **share URL** column shows something like `/go/stordia`.
   - [ ] Copy this URL for the next steps.

---

## 2. Basic Affiliate → Lead → Conversion Flow

Use an incognito/private browser window for this section.

### 2.1. Visit the affiliate link

- [ ] Open an **incognito window**.
- [ ] Navigate to: `https://dev.in.today/go/stordia` (or your test slug).
- [ ] Confirm that you are redirected to `/en` (or the default locale home).

### 2.2. Submit the contact form

1. Scroll to the **contact** section.
2. Fill out the form with a clearly test-specific email, e.g.:
   - [ ] Name: `Test Affiliate Lead`
   - [ ] Email: `info@stordia.com`
   - [ ] Restaurant name: `Stordia`
   - [ ] Type: `Café/Bistro` (or similar)
   - [ ] Services: at least one checked (e.g. Website)
   - [ ] Budget: choose any option
   - [ ] Message: some test text
3. Submit the form.
4. Confirm:
   - [ ] The success modal appears.
   - [ ] No JavaScript errors appear in the browser console.

### 2.3. Check the Contact Lead

Back in a **normal (non-incognito) window**, logged in as admin:

- [ ] Go to `/admin/contact-leads`.
- [ ] Find the newly created lead (look for the email you used, e.g. `info@stordia.com`).
- [ ] Confirm:
  - [ ] Source column shows **Affiliate: {Affiliate Name}** (e.g. “Affiliate: Stordia”).
  - [ ] View the lead and confirm there is a **Source** section with affiliate details.

### 2.4. Check the Affiliate Conversion

- [ ] Go to `/admin/affiliates` → open the **View** page for your test affiliate.
- [ ] In the **Conversions** relation, confirm a new **conversion** exists:
  - [ ] Linked to the correct affiliate.
  - [ ] Linked to the correct contact lead (email matches).
  - [ ] Status is `pending`.
  - [ ] Commission amount may be `null` initially – this will be filled when approving.

---

## 3. Approve Conversion & Default Commission

From the **View Affiliate** page:

1. In the **Conversions** relation:
   - [ ] Locate the conversion created in section 2.
   - [ ] Use the **Approve** action.
2. Confirm after approval:
   - [ ] Status has changed to `approved`.
   - [ ] `commission_amount` is set to the affiliate’s default commission rate (e.g. 100 EUR), if it was previously `null`.
   - [ ] Currency is set to `EUR` (unless configured otherwise).

Optional:
- [ ] Try setting a custom `commission_amount` before approval and ensure it is **not** overwritten.

---

## 4. Create and Manage Payouts

Still on the **View Affiliate** page:

### 4.1. Create payout

1. At the top of the page, find the **“Create Payout from Approved”** button.
2. Click it and check the modal text:
   - [ ] It shows the **number of approved conversions** that will be included.
   - [ ] It shows the **total commission amount** to be paid.
3. Confirm / submit the modal.

### 4.2. Verify payout

1. In the **Payouts** relation:
   - [ ] A new payout entry should exist.
   - [ ] Status should initially be `pending`.
   - [ ] Amount equals the sum of approved conversions’ commission amounts.
2. Open the payout detail page and confirm:
   - [ ] Period start / end fields look reasonable.
   - [ ] Linked conversions are shown.

### 4.3. Mark payout as paid

- [ ] Use the action to mark payout as **Paid**.
- [ ] Confirm:
  - [ ] Payout status = `paid`.
  - [ ] Affiliate’s **Paid Commission** total reflects this amount.
  - [ ] Outstanding commission decreases accordingly.

---

## 5. Cookie & Last-Click Attribution

This section verifies the cookie-based tracking and “last click wins” rule.

### 5.1. Cookie-based return visit

1. In **incognito**, repeat section 2 with the affiliate URL `/go/stordia`.
2. Close the tab **without** submitting the form.
3. Open a new tab in the **same incognito window** and go directly to `/en`.
4. Submit the contact form.
5. Back in admin:
   - [ ] Confirm the new lead is still attributed to the same affiliate (Source: “Affiliate: Stordia”).
   - This proves cookie-based attribution works, not only the session.

### 5.2. Last-click wins

1. Create a second affiliate, e.g. **“Other Partner”**, with its own link `/go/other`.
2. In a fresh incognito window:
   - [ ] Visit `/go/stordia` first.
   - [ ] Then visit `/go/other`.
   - [ ] Finally, submit the contact form.
3. Back in admin:
   - [ ] Confirm the lead is attributed to **“Other Partner”** (the last click), not Stordia.

---

## 6. Edge Cases & Error Handling

### 6.1. Invalid slug

- [ ] Visit `/go/this-does-not-exist`.
- [ ] Confirm:
  - [ ] You are redirected to the landing page (e.g. `/en`).
  - [ ] No errors are shown.
  - [ ] If you submit a contact form after this, the lead is **organic** (no affiliate attribution).

### 6.2. Organic lead (no affiliate)

- [ ] Open a normal window, go directly to `/en`.
- [ ] Submit the contact form.
- [ ] Confirm:
  - [ ] The lead’s Source is “Organic” (or equivalent label).
  - [ ] No AffiliateConversion is created.

### 6.3. Email failure (optional)

If you intentionally misconfigure mail temporarily:

- [ ] Submit the contact form.
- [ ] Confirm the app handles the failure gracefully (no white screen).
- [ ] Check `storage/logs/laravel.log` for a clear error message.

---

## 7. Quick Regression Checklist (After Any Change)

Run this mini-checklist after changes to affiliates, leads, or email code:

- [ ] `/go/{slug}` still redirects correctly.
- [ ] New leads from affiliate links are attributed correctly.
- [ ] New organic leads remain non-attributed.
- [ ] Conversions can be approved and show commission.
- [ ] Payout creation and mark-as-paid still work.
- [ ] No unexpected errors in browser console on:
  - [ ] `/admin/affiliates`
  - [ ] `/admin/contact-leads`
  - [ ] `/admin/affiliate-conversions`
  - [ ] `/admin/affiliate-payouts`
