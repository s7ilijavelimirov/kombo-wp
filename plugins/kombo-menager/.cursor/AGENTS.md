# Kombo Manager Plugin — Cursor Agent Context

## Project overview
WordPress + WooCommerce plugin for kombomeals.rs.
Meal delivery service (Belgrade, Novi Sad, Stara i Nova Pazova).
This plugin adds order management, manager workflows, kitchen views,
label printing, payment tracking, and subscription management.

Plugin slug: kombo-menager
Plugin prefix (functions/options): km_
Plugin prefix (constants): KM_
Root namespace: KomboManager
Text domain: kombo-menager

---

## Workspace layout
WordPress root is open in Cursor. Key paths:

| What | Path |
|---|---|
| This plugin | wp-content/plugins/kombo-menager/ |
| Active theme (parent) | wp-content/themes/kombo/ |
| Active theme (child) | wp-content/themes/kombo-child/ |
| WooCommerce data map | wp-content/themes/kombo-child/docs/WOOCOMMERCE-DATA-SUMMARY.md |
| Theme audit | wp-content/themes/kombo-child/docs/theme-full-audit-report.md |
| Business requirements | wp-content/plugins/kombo-menager/docs/sastanak-notes.md |

---

## MANDATORY: Read before writing any plugin code

1. @wp-content/themes/kombo-child/docs/WOOCOMMERCE-DATA-SUMMARY.md
   → Understand existing WC product structure, meta fields, order data
   → Never duplicate or conflict with existing WC data structures

2. @wp-content/themes/kombo-child/docs/theme-full-audit-report.md
   → Understand active hooks, enqueued scripts/styles, template overrides
   → Never register hooks already used by the theme

3. @wp-content/plugins/kombo-menager/docs/sastanak-notes.md
   → Business requirements — fully updated with wireframe details, migration plan, module breakdown, and open questions. Always read before writing any feature.

---

## Tech stack
- PHP 8.1+
- WordPress 6.x native APIs only (no Composer, no external frameworks)
- WooCommerce — existing installation, do not modify WC core or WC templates
- Admin UI: WordPress native (Settings API, WP_List_Table, admin_menu)
- No React, no Vue, no Gutenberg blocks (unless explicitly requested)
- JavaScript: vanilla JS or jQuery (already loaded by WP)
- CSS: plain CSS in admin, scoped with .km- prefix

---

## Plugin folder structure (enforce this always)
```
kombo-menager/
├── kombo-menager.php           ← plugin header + bootstrap only
├── includes/
│   ├── class-kombo-manager.php     ← main plugin class, singleton
│   ├── class-autoloader.php        ← maps namespaces to files
│   ├── class-activator.php         ← activation: DB tables, roles, options
│   ├── class-deactivator.php       ← deactivation logic
│   ├── Core/                       ← core logic classes
│   ├── Admin/                      ← admin pages, menus, list tables
│   ├── Api/                        ← REST endpoints (if needed)
│   └── Frontend/                   ← shortcodes, user dashboard
├── templates/
│   ├── admin/                      ← admin page templates
│   └── frontend/                   ← frontend templates
├── assets/
│   ├── css/
│   └── js/
├── languages/
└── docs/
    └── sastanak-notes.md
```

---

## User roles (register on activation)
| Role slug | Label | Capabilities |
|---|---|---|
| km_customer | Naručilac | order, view own orders, view own payments |
| km_manager | Menadžer | all customer caps + manage all orders + print + log payments |
| km_kitchen | Kuhinja | read-only daily order view |
| administrator | unchanged | full access |

---

## Custom DB tables (register on activation via $wpdb)
- `{prefix}km_orders` — plugin orders (linked to WC or standalone TBD)
- `{prefix}km_payments` — payment entries with reference numbers
- `{prefix}km_activity_log` — all manager actions
- `{prefix}km_saved_customers` — 3rd party customers saved by managers
- `{prefix}km_subscriptions` — subscription packages per user

---

## Key business rules (always respect)
- Every order → unique reference number format: KM-XXXX (auto-generated)
- Reference numbers used only for bank transfer orders
- Manager actions → always write to km_activity_log (who, what, when, old/new value)
- Labels → group all items per customer sequentially, never split
- Payment entry: amount + reference number → system matches and reduces debt
- Subscription expiry → flag in manager dashboard and user dashboard

---

## Phase 1 scope (current)
Scaffold only:
- kombo-menager.php (plugin header, constants, bootstrap)
- class-autoloader.php
- class-kombo-manager.php (main class, singleton, hook registration stub)
- class-activator.php (stub — DB table creation and role registration will be added Phase 2)
- class-deactivator.php (stub)
- Empty folder structure as defined above

No DB tables yet. No admin pages yet. No functionality yet.
Clean, extensible foundation only.

---

## Rules for Cursor agent behavior
- Always read the 3 mandatory files above before writing plugin code
- Never modify files outside wp-content/plugins/kombo-menager/
- Never modify theme files — plugin extends, never overwrites theme
- Never modify WooCommerce core or WC template files
- If unsure about existing WC/theme structure → read WOOCOMMERCE-DATA-SUMMARY.md first
- When adding a new class → register it in autoloader
- When adding a new DB table → add to activator only, never create tables on every load
- When adding manager functionality → always include activity log write
- Ask before creating REST endpoints — confirm if needed for that feature