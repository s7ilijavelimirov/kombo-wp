# Kombo Manager — Business Requirements (Sastanak Notes)

> Source: client meeting notes
> Status: confirmed requirements for plugin development

---

## User Roles

### 1. Običan korisnik (naručilac / km_customer)
- Must be registered — no guest checkout
- Registration password rules: 8–16 characters, min 1 number, min 1 special character
- Receives activation link via email after registration
- After activation: can log in and complete orders
- Top menu: login icon (logged out) → user info + dropdown (logged in)

**Logged-in user menu leads to:**
- Dashboard — account overview
- All orders — view history; active orders can have date changed
- Financial card (uplata pregled):
  - Total debt (ukupno dug)
  - Total paid (ukupno uplata)
  - Difference (dug - uplata)
- Subscription expiry warning + renewal prompt
- Password change (logged in + forgot password flow)

---

### 2. Menadžer sistema (km_manager)
- Multiple managers can exist
- ALL manager actions must be logged (activity log): who, what, when
- Can order on behalf of a 3rd party (customer), entering all their details:
  - First name, last name
  - Address
  - Phone
  - Payment method
- 3rd party can optionally be saved for reuse (no re-entry needed next time)
- Order must log the contact channel: Viber / WhatsApp / phone call / SMS
- Order must store: manager name + timestamp + type flag (manager-created)
- Any manager can edit an order — but edit must be logged (who changed what)

**Manager daily kitchen view:**
- List format: Dish name — portion size — quantity to prepare
- Printable as PDF with: day name, date, quantities
- Purpose: morning kitchen order sheet

**Manager label printing:**
- Per customer label with:
  - Full name
  - Address
  - Phone number
  - Order description: dish name — portion size
  - Payment status: "plaćeno" / "pouzećem + amount to collect"
- If customer has multiple dishes/portions → labels grouped together (sequential)
- Labels are printed, attached to bags, bags filled and handed to delivery

---

### 3. Kuhinja (km_kitchen) — optional role
- Read-only view of daily order count
- Same data as manager kitchen view, no editing

---

### 4. Administrator
- All manager capabilities + above
- Full system access

---

## User Profile
- Multiple addresses per user: max 4, types: Kuća / Posao / Drugo
- Multiple phones per user: max 2 (Broj telefona, Broj telefona 2)
- Allergies field: free text
- If user has more than max addresses or phones — extra fields not shown (edge case)
- Saved payment cards: shown on profile (payment gateway integration needed)

---

## Registration & Activation
- Registration is custom through plugin (not WP default)
- After registration: system sends activation email automatically
- User clicks activation link in email → account activated
- User then logs in and can complete orders
- Manager does NOT manually approve accounts

---

## Payment System

### Reference number
- Every order gets a unique reference number: format `KM-XXXX` (system generated)
- Used only for bank transfer orders (not card, not cash on delivery)
- Used on uplatnica (payment slip) sent to customer

### Payment methods
1. **Card (kartica)** — instant booking on site, no reference number needed
2. **Bank transfer (uplatnica)** — reference number generated, uplatnica created, manager manually enters payment from bank statement
3. **Cash on delivery (pouzećem)** — amount shown on label

### Manager payment entry flow
1. Manager receives bank statement (izvod)
2. Opens WP admin → enters: amount + reference number
3. System matches payment to order → reduces or clears debt

### Customer financial view
```
Treba da platite: 4000 RSD
Uplata dana XX.XX.XXXX: 3800 RSD
Ostaje dug: 200 RSD
```

### Accounting logic
- Creating order with bank transfer → creates debit (zaduženje) for customer
- Entering payment with reference number → reduces or clears debit
- Card payment → instantly booked, no debit created

---

## Orders

### Order data to store
- Customer info (or 3rd party info if manager-created)
- Ordered items (dish, portion size, quantity)
- Delivery date
- Payment method
- Reference number (if bank transfer)
- Created by (customer self / manager name)
- Contact channel (if manager-created): Viber / WhatsApp / call / SMS
- Timestamps: created_at, updated_at
- Activity log entries (for manager edits)

### Order statuses (to be defined in Phase 2)
- Active (active, future delivery date)
- Completed
- Cancelled

---

## Order — Per Delivery Date
- Each delivery date has:
  - Delivery address (selected from saved addresses)
  - Contact phone (selected from saved phones)
  - Note for delivery driver (optional, free text)
  - Note for kitchen (optional, free text)
- Bulk actions available:
  - "Primeni na sve pakete" — apply selection to all active packages
  - "Primeni na sve dane" — apply selection to all days in current package

---

## Subscriptions
- Subscription = package for X delivery days (e.g. Nedeljni 6 dana)
- Each package has:
  - Package name (e.g. Slim 1300, Fit 1900)
  - Total days in package
  - Remaining days
  - First day, Last day
  - Last payment date
  - Payment status (Plaćeno / Neplaćeno)
- User can have multiple active packages simultaneously
- User can add new package via "+ Dodaj paket" button
- Expiry warning shown when package is running low (exact threshold = OPEN QUESTION)

---

## Data Migration
- ~4,500 existing orders (guest/anonymous) must be migrated
- Migration strategy: to be defined in Phase 3
- Old orders will be linked to new user accounts where possible
- Where linking is not possible: kept as historical archive

---

## Development Modules
Module 1 — Authentication: registration, login, email activation
Module 2 — User panel: dashboard, orders, finances, profile
Module 3 — Manager panel: orders for 3rd party, kitchen view, labels, activity log
Module 4 — Orders core: CRUD, statuses, reference numbers
Module 5 — Finances: debts, payments, tracking
Module 6 — Reports: kitchen PDF, statistics

---

## Open Questions
- OPEN: Deadline for order date change (e.g. before 12:00 previous day?)
- OPEN: Payment gateway — which provider? (Nestpay or other?)
- OPEN: Can customer cancel own order or only manager?
- OPEN: Subscription expiry warning threshold — how many days before?
- OPEN: Cash on delivery — who confirms delivery driver collected payment?
- OPEN: Are delivery zones/routes needed now or later?

---

## Print / PDF Requirements
- Kitchen order sheet: PDF, daily, dish/portion/quantity
- Customer labels: printable list, grouped per customer
- Payment slip (uplatnica): generated per order for bank transfer customers

---

## Activity Log Requirements
- Table: km_activity_log
- Columns: id, user_id, manager_name, action_type, object_type, object_id, old_value, new_value, created_at
- Logged events: order created by manager, order edited, payment entered, label printed