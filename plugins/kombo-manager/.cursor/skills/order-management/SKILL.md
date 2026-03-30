---
name: order-management
description: Implement order creation, manager order-on-behalf-of, reference number generation, payment tracking, or activity logging. Use when working on any order-related feature in kombo-manager plugin.
---

# Order Management — Business Logic Reference

## Read first
@wp-content/plugins/kombo-manager/docs/sastanak-notes.md — full business requirements

---

## Reference number
- Format: KM-XXXX (zero-padded, e.g. KM-0042)
- Generated once at order creation
- Stored as order meta: _km_reference_number
- Used only for bank transfer (uplatnica) orders
- Never regenerate after creation

## Order creation rules
- Self-ordered (customer): store customer ID, no manager fields
- Manager-ordered: store manager user_id + display_name + timestamp + contact_channel
- Contact channel options: viber | whatsapp | phone | sms
- 3rd party customer data: name, address, phone, payment_method (optionally saved to km_saved_customers)

## Activity log — write on every manager action
```php
// Every manager action must call this pattern
KomboManager\Core\ActivityLog::write([
    'user_id'     => get_current_user_id(),
    'action_type' => 'order_created', // order_edited, payment_entered, label_printed
    'object_type' => 'order',
    'object_id'   => $order_id,
    'old_value'   => $old_data,   // JSON-encoded or null
    'new_value'   => $new_data,   // JSON-encoded
]);
```

## Payment tracking logic
- Order created (bank transfer) → insert row in km_payments with status = 'pending', amount = order_total
- Manager enters payment (amount + reference_number) → find matching km_payments row → update paid_amount
- Customer debt = SUM(order_total) - SUM(paid_amount) for all their orders
- Card payment → insert km_payments row with status = 'paid' immediately, no reference number needed

## Label printing rules
- Group all items per customer together (never split one customer)
- Each label: full_name, address, phone, dish_name, portion_size, payment_status
- Payment status on label: "Plaćeno" or "Pouzećem: XXXX RSD"
- Output: printable HTML page (window.print()) or PDF via WP (confirm method in Phase 3)

## Kitchen view data
- Query: for a given date, sum quantities grouped by (product_name, portion_size)
- Output format: Naziv jela — Veličina porcije — Broj komada
- PDF output: include day name + date in header