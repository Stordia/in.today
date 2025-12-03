# TESTS – Admin Directory & Settings

> Manual test plan για όταν είσαι έτοιμος να κάνεις QA σε αυτό το κεφάλαιο.

---

## 1. Country Directory

### 1.1 Δημιουργία Χώρας

1. Μπες στο **/admin** ως platform admin.
2. Πήγαινε στο **Directory → Countries**.
3. Πάτησε **Create**.
4. Συμπλήρωσε:
   - Name: `Germany`
   - Code: `DE`
5. Αποθήκευσε.

**Expected:**  
- Η χώρα εμφανίζεται στη λίστα με `is_active = true`.
- Το slug έχει δημιουργηθεί αυτόματα (π.χ. `germany`).

### 1.2 Σύνδεση Πόλης με Χώρα

1. Πήγαινε στο **Directory → Cities**.
2. Δημιούργησε/επεξεργάσου μια πόλη (π.χ. Berlin).
3. Διάλεξε Country = `Germany` από το dropdown.
4. Αποθήκευσε.

**Expected:**  
- Στη λίστα Cities να φαίνεται η χώρα (Germany).
- Το filter by country να φιλτράρει σωστά.

### 1.3 Σύνδεση Εστιατορίου με Χώρα

1. Πήγαινε στο **Directory → Restaurants**.
2. Άνοιξε ένα restaurant demo.
3. Διάλεξε Country = `Germany`, City = `Berlin`.
4. Αποθήκευσε.

**Expected:**  
- Στη λίστα Restaurants να εμφανίζεται χώρα/πόλη (αν έχουμε column).
- Δεν εμφανίζεται κανένα error από foreign keys.

---

## 2. Settings Panel

### 2.1 Πρόσβαση στο Settings

1. Μπες ως platform admin.
2. Βεβαιώσου ότι στο sidebar υπάρχει group **Settings**.
3. Άνοιξε τη σελίδα Settings.

**Expected:**  
- Βλέπεις tabs: General, Email & Notifications, Bookings, Affiliates (ή όπως τελικά ονομάζονται).
- Δεν υπάρχουν Laravel/JS errors στην console.

### 2.2 General Settings

1. Στο tab General άλλαξε:
   - Platform Name → `in.today – Demo`
   - Timezone → `Europe/Berlin` (αν υπάρχει επιλογή).
2. Αποθήκευσε.

**Expected:**  
- Μετά το save, τα ίδια values εμφανίζονται ξανά.
- Σε όποιο σημείο χρησιμοποιείται το platform name στο admin, να ενημερώνεται (αν έχει υλοποιηθεί).

### 2.3 Email & Notifications

1. Στο tab Email & Notifications όρισε:
   - Default from name → `in.today Team`
   - Default from email → `noreply@in.today`
   - Reservation notification email → ένα δικό σου test email.
2. Αποθήκευσε.

**Expected:**  
- Τα values αποθηκεύονται και εμφανίζονται σωστά μετά από refresh.
- (Προαιρετικό test αργότερα) Νέες κρατήσεις που δεν έχουν custom email στο restaurant να χρησιμοποιούν αυτό το notification email.

### 2.4 Bookings Defaults

1. Στο tab Bookings βάλε:
   - Min lead time: π.χ. `120` λεπτά.
   - Max lead time days: π.χ. `30`.
   - Default duration: π.χ. `90` λεπτά.
2. Αποθήκευσε.

**Expected:**  
- Τα values αποθηκεύονται.
- Αργότερα, όταν ένα restaurant δεν έχει δικές του booking ρυθμίσεις, ο AvailabilityService και η public booking page να συμπεριφέρονται με αυτά τα defaults.

### 2.5 Affiliates Defaults

1. Στο tab Affiliates όρισε:
   - Default commission rate: π.χ. `20` (%).
   - Minimum payout amount: π.χ. `50` EUR.
2. Αποθήκευσε.

**Expected:**  
- Τα values αποθηκεύονται.
- (Όταν γίνει το wiring) νέο Affiliate χωρίς custom rate να προτείνεται με 20%.

---

## 3. Wiring – Functional Tests

### 3.1 Booking Notification Email Fallback

_Precondition:_ Ένα restaurant **χωρίς** δικό του notification email.

1. Ρύθμισε στο Settings → Email & Notifications:
   - Reservation notification email = δικό σου δοκιμαστικό email.
2. Πήγαινε σε public booking page ενός τέτοιου restaurant.
3. Κάνε μια δοκιμαστική κράτηση (με σωστό email).

**Expected:**  
- Στο mail log ή στο πραγματικό inbox να έρθει email προς το notification email που έβαλες στα Settings.
- Αν υπάρξει σφάλμα SMTP, να καταγράφεται στα logs αλλά να μην σπάει το UX του χρήστη.

### 3.2 Affiliate Default Commission

_Precondition:_ Το wiring έχει υλοποιηθεί._

1. Ρύθμισε στο Settings → Affiliates:
   - Default commission rate = 25.
2. Δημιούργησε νέο Affiliate στο /admin.
3. Άφησε το commission rate κενό (αν το UI το επιτρέπει) ή δες την default τιμή που προτείνεται.

**Expected:**  
- Να προτείνεται 25% ως default (ή να συμπληρώνεται αυτόματα).

---

## 4. Regression Checks

### 4.1 Admin Navigation

1. Βεβαιώσου ότι στο sidebar εμφανίζονται:
   - Directory → Countries, Cities, Cuisines, Restaurants
   - Settings (όχι στο /business panel)
2. Μπες στο /business panel ως restaurant user.

**Expected:**  
- Στο /business ΔΕΝ πρέπει να φαίνεται το Settings group του admin.
- Οι νέες προσθήκες (Countries, Settings) δεν σπάνε το business panel.

### 4.2 Console / Logs

1. Κάνε μερικά random click γύρω από /admin (Countries, Cities, Restaurants, Settings).
2. Παρακολούθησε browser console + storage/logs/laravel.log.

**Expected:**  
- Καμία νέα προειδοποίηση / exception σχετική με Countries ή Settings.
