# WooCommerce data reference — Kombo + Kombo child

<!--
  Location: wp-content/themes/kombo-child/docs/WOOCOMMERCE-DATA-SUMMARY.md
  (Grouped with other developer docs; cross-referenced from kombo-child/docs/theme-full-audit-report.md.)

  Snapshot source: row/key frequencies were computed from the repository mysqldump
  `sql/local.sql` (same schema as Local by Flywheel: DB `local`, typical MySQL port 10091).
  wp-config.php uses DB_HOST `localhost` (no port) for the Local site container; external
  tools often use 127.0.0.1:10091, user root, password root.
-->

## Overview

| Topic | Detail |
|-------|--------|
| Themes | Parent `kombo`, active child `kombo-child` |
| Table prefix | `wp_` |
| **HPOS** | **Enabled.** Orders live in `wp_wc_orders`; operational meta in `wp_wc_orders_meta`. Billing/shipping names and contact data are in `wp_wc_order_addresses`. **Line items** still use `wp_woocommerce_order_items` + `wp_woocommerce_order_itemmeta` (unchanged). |
| Dump stats | `wp_wc_orders`: **4706** rows in `sql/local.sql`. `wp_posts` type `product`: **1** row (meal-plan base). No `shop_order` rows in `wp_posts` in this export (orders fully on HPOS). |
| Parent custom order meta | `PAK` (from `billing_pak`) — **not present** in this dump; child theme removes the checkout field, so nothing posts `billing_pak`. |
| Payment methods observed | `cod`, `nestpay` (+ meta `_nestpay_status`, `_nestpay_callback_id`) |

---

## 1. Products (`wp_posts` + `wp_postmeta`)

### 1.1 Meal-plan base product (theme code)

| Item | Detail |
|------|--------|
| Product ID | **181** — `get_meal_plan_base_product()` in `inc/meal-plan.php` |
| Name / slug | e.g. “Plan ishrane” / `plan-ishrane` |
| Pricing | Dynamic from option `meal_plan_prices`; cart uses `_price` on the line |

### 1.2 `wp_postmeta` keys on `product` posts (from dump)

> One published product in export; counts are occurrences of each meta row.

| meta_key | rows in dump |
|----------|----------------|
| `_backorders` | 1 |
| `_download_expiry` | 1 |
| `_download_limit` | 1 |
| `_downloadable` | 1 |
| `_edit_lock` | 1 |
| `_manage_stock` | 1 |
| `_price` | 1 |
| `_product_version` | 1 |
| `_regular_price` | 1 |
| `_sold_individually` | 1 |
| `_stock` | 1 |
| `_stock_status` | 1 |
| `_tax_class` | 1 |
| `_tax_status` | 1 |
| `_virtual` | 1 |
| `_wc_average_rating` | 1 |
| `_wc_review_count` | 1 |
| `_wp_old_slug` | 1 |
| `total_sales` | 1 |

### 1.3 Variations

No `product_variation` postmeta rows matched in this dump (empty catalog of variations).

### 1.4 Theme / option data (not `postmeta`)

| Key / location | Purpose |
|----------------|---------|
| `meal_plan_prices` (`wp_options`) | Nested prices: `slim|fit|protein|vege` → calories → `dnevni|nedeljni5|…` |

---

## 2. Orders (HPOS)

### 2.1 `wp_wc_orders` — core columns (who ordered, money, payment)

Each row is one order. Important columns:

| Column | Meaning |
|--------|---------|
| `id` | Order ID (used by addresses + line items) |
| `status` | e.g. `wc-processing`, `wc-cancelled` |
| `currency` | `RSD` |
| `type` | `shop_order` |
| `tax_amount`, `total_amount` | Totals |
| `customer_id` | WP user ID (`0` = guest) |
| `billing_email` | Primary email on the order |
| `date_created_gmt`, `date_updated_gmt` | Timestamps |
| `payment_method` | `cod`, `nestpay`, … |
| `payment_method_title` | Localized title |
| `transaction_id` | Gateway reference (often Nestpay) |
| `ip_address`, `user_agent` | Client metadata |
| `customer_note` | Checkout order note (`order_comments`) |

### 2.2 `wp_wc_order_addresses` — “Who ordered” & billing fields

One row per `(order_id, address_type)` (e.g. `billing`). Columns include: `first_name`, `last_name`, `company`, `address_1`, `address_2`, `city`, `state`, `postcode`, `country`, `email`, `phone`.  
Child theme copies billing into shipping on creation when shipping is empty — shipping row may be blank placeholders.

### 2.3 `wp_wc_orders_meta` — distinct keys (row counts in dump)

| meta_key | approx. rows |
|----------|----------------|
| `_billing_address_index` | 4706 |
| `_shipping_address_index` | 4706 |
| `is_vat_exempt` | 4706 |
| `_wc_order_attribution_user_agent` | 4779 |
| `_wc_order_attribution_session_start_time` | 4779 |
| `_wc_order_attribution_session_pages` | 4779 |
| `_wc_order_attribution_session_entry` | 4779 |
| `_wc_order_attribution_session_count` | 4779 |
| `_wc_order_attribution_source_type` | 4779 |
| `_wc_order_attribution_device_type` | 4779 |
| `_wc_order_attribution_utm_source` | 4774 |
| `_wc_order_attribution_utm_medium` | 2772 |
| `_wc_order_attribution_referrer` | 3102 |
| `_wc_order_attribution_utm_content` | 1606 |
| `_wc_order_attribution_utm_campaign` | 212 |
| `_wc_order_attribution_utm_id` | 204 |
| `_wc_order_attribution_utm_term` | 199 |
| `_nestpay_status` | 2435 |
| `_nestpay_callback_id` | 2435 |
| `_googlesitekit_ga_purchase_event_tracked` | 209 |
| `_edit_lock` | 4582 |
| `_wp_trash_meta_status` | 1 |
| `_wp_trash_meta_time` | 1 |
| *(other single-digit-count utm_* keys)* | ≤ 7 each |

> **Not in dump:** `_billing_first_name` style keys on HPOS — those live in **`wp_wc_order_addresses`**, not in `wp_wc_orders_meta`.

---

## 3. Order line items (`wp_woocommerce_order_items` + `wp_woocommerce_order_itemmeta`)

### 3.1 Product **181** (meal plan) — cart variables, JS → PHP, and persisted order line meta

> **Scope:** Logic that applies when customers use the meal-plan flow: base product ID from `get_meal_plan_base_product()` in `inc/meal-plan.php` (currently **181**). Parent theme renders the form in `views/template-parts/meal-plan-form.php`; child theme loads `assets/js/meal-plan-form.js` and `inc/meal-plan.php` / `inc/woocommerce.php`.

#### 3.1.1 Custom keys on the **cart line** (`$cart_item` in session)

These are built in `handle_meal_plan_add_to_cart()` and passed as the fifth argument to `WC()->cart->add_to_cart()`. WooCommerce stores them on the cart line (session) alongside core keys such as `product_id`, `quantity`, and `data` (`WC_Product`).

| Cart key | Meaning | Set in PHP | Value shape / notes |
|----------|---------|------------|---------------------|
| `menu_type` | Standard vs vege menu | `sanitize_text_field( $_POST['menu_type'] )` | `standard` or `vege` |
| `gender` | Plan slug (also used for pricing lookup) | `sanitize_text_field( $_POST['gender'] )` | `slim`, `fit`, `protein`, or `vege` (vege flow sets this in JS without Slim/Fit/Protein buttons) |
| `calories` | Calorie tier | `intval( $_POST['calories'] )` | e.g. `1300`, `1600`, `2000`, `1400`, `1900`, … |
| `package_type` | Package duration key | `sanitize_text_field( $_POST['package'] )` | `dnevni`, `nedeljni5`, `nedeljni6`, `mesecni20`, `mesecni24` |
| `delivery_dates` | Selected delivery date(s) | `$_POST['dates']` (array or single, as submitted) | **Array** of strings `d-m-Y` (e.g. `19-02-2025`) for `dnevni` (multi-select); **single** string same format for fixed-length packages (one start date in JS) |
| `unique_key` | Line identity | `md5( microtime() . rand() )` | Arbitrary string so duplicate configurations stay separate lines |
| `_price` | Calculated unit price for totals | `get_package_price()` × days for `dnevni`, else base package price | Float; reapplied on cart via `adjust_cart_item_price` (`woocommerce_before_calculate_totals`, `inc/meal-plan.php`) |

**Cart mutations after add:** `inc/woocommerce.php` — `handle_update_cart_delivery_date` (AJAX) replaces `delivery_dates` and, for `package_type === 'dnevni'`, updates `_price` and product `set_price`. `preserve_cart_delivery_dates` (`woocommerce_update_cart_item_data`) keeps `delivery_dates` when the cart form is saved.

#### 3.1.2 Parent theme markup — **data attributes & hidden fields** (`kombo/views/template-parts/meal-plan-form.php`)

These drive the JS state; only fields sent in AJAX are persisted (see §3.1.4).

| Source | Attribute / field | Typical values | Used for |
|--------|---------------------|----------------|----------|
| `.menu-type-btn` | `data-menu-type` | `standard`, `vege` | `state.menuType` → POST `menu_type` |
| `.gender-buttons .form-button` | `data-gender` | `slim`, `fit`, `protein` | `state.gender` → POST `gender` |
| `.package-buttons .form-button` | `data-package` | `dnevni`, `nedeljni5`, … | `state.package` → POST `package` |
| `.calories-buttons .form-button` | `data-calories` (set in JS) | e.g. `1300`, `1600` | `state.calories` → POST `calories` |
| Hidden `#selectedMenuType` | `name="menu_type"` | mirrors menu type | Synced by JS; AJAX uses explicit object keys |
| Hidden `#selectedGender`, `#selectedCalories`, `#selectedPackage` | — | Mirrors selections | Updated for consistency; AJAX payload is built from `this.state` |

#### 3.1.3 Child theme JavaScript (`kombo-child/assets/js/meal-plan-form.js`)

| Variable / object | Role | Maps to cart / order |
|-------------------|------|----------------------|
| `MealPlanState.menuType` | `standard` / `vege` | → POST `menu_type` → cart `menu_type` |
| `MealPlanState.gender` | Plan slug | → POST `gender` → cart `gender` → order meta **`Paket`** (via `ucfirst`) |
| `MealPlanState.calories` | Integer | → POST `calories` → cart `calories` → order meta **`Kalorije`** as `"{n} kcal"` |
| `MealPlanState.package` | Package key | → POST `package` → cart `package_type` → order meta **`Tip paketa`** (via `ucfirst`) |
| `MealPlanState.dates` | Array of date strings | → POST `dates` → cart `delivery_dates` → order **`Datum dostave`** + **`delivery_dates`** |
| `meal_plan_vars` | `ajax_url`, `nonce` (`meal_plan_nonce`) | `check_ajax_referer( 'meal_plan_nonce', 'nonce' )` on add-to-cart / price AJAX |
| `mealTranslations` | UI strings only | Not sent to cart |

**Add to cart AJAX** (`addToCart()`): `action: add_meal_plan_to_cart`, `menu_type`, `gender`, `calories`, `package`, `dates`, `buy_now` (boolean; **not** read in PHP for cart data — only used client-side for redirect behavior).

**Price preview AJAX** (`get_meal_plan_price` / `updateAllPrices`): includes `menu_type`, `gender`, `calories`, `package`, and for line total preview `days_count` when `package === 'dnevni'` (length of `state.dates`).

**Date format:** jQuery UI Datepicker `dateFormat: "dd-mm-yy"` → strings like `05-03-2025` (`d-m-Y`). For non-`dnevni` packages, JS sets `state.dates` to a **single-element** array after computing the range display.

#### 3.1.4 Server handler — **POST → cart** (`handle_meal_plan_add_to_cart`, `inc/meal-plan.php`)

1. Verifies nonce `meal_plan_nonce`.  
2. Reads `menu_type`, `gender`, `calories`, `package`, `dates`.  
3. Resolves product: `get_meal_plan_base_product()` → **181**.  
4. Computes `days_count` = `count( dates )` if `package === 'dnevni'`, else `1`.  
5. Builds `$cart_item_data` (table in §3.1.1) and calls `WC()->cart->add_to_cart( $product_id, 1, 0, array(), $cart_item_data )`.

#### 3.1.5 Cart page DOM (`kombo-child/woocommerce/cart/cart.php`)

| Attribute | Value | Purpose |
|-----------|-------|---------|
| `data-cart-key` | WooCommerce `$cart_item_key` | Passed to `update_cart_delivery_date` AJAX as `cart_item_key` |
| `data-package-type` | `(string) $cart_item['package_type']` | Client-side context for daily vs other packages |

Hidden calendar-related field `#cart-delivery-{key}` (in same template) supplies updated date arrays to `assets/js/cart.js`.

#### 3.1.6 Cart JavaScript — **date updates** (`kombo-child/assets/js/cart.js`)

AJAX action `update_cart_delivery_date`: sends `cart_item_key`, `new_date` (array of `d-m-Y` strings), `security` (nonce `woocommerce-cart`). Handler: `handle_update_cart_delivery_date` in `inc/woocommerce.php` — updates session `delivery_dates`, `_price`, and recalculates totals for `dnevni` lines.

#### 3.1.7 Checkout / cart **display only** (not saved as order line meta)

Filter `woocommerce_get_item_data` → `display_cart_item_custom_meta` (`inc/meal-plan.php`). Adds human-readable rows for **cart and checkout** review:

| Display key (Polylang-wrapped) | Source cart key | Shown when |
|--------------------------------|-----------------|------------|
| Tip menija | `menu_type` | `standard` → “Standardni paketi”, `vege` → “Vege paketi” |
| Plan | `gender` | If `gender !== 'vege'` — Slim / Fit / Protein labels |
| Kalorije | `calories` | Always if set |
| Izaberi paket | `package_type` | Translated package labels (Dnevni, Nedeljni 5, …) |
| Datum dostave | `delivery_dates` | Sorted, comma-separated `d-m-Y` |

> **Plugin note:** These rows are **not** copied to order line meta by this filter. Only the keys in §3.1.8 are persisted via `woocommerce_checkout_create_order_line_item`.

Additional checkout label tweaks: `translate_checkout_variations`, `translate_delivery_date` in `inc/woocommerce.php` (still display-only).

#### 3.1.8 **Persisted** order line item meta (`wp_woocommerce_order_itemmeta`)

Both callbacks use `woocommerce_checkout_create_order_line_item` at priority **10**. In `functions.php`, `meal-plan.php` is loaded **before** `woocommerce.php`, so **`add_cart_item_data_to_order_items` runs first**, then **`add_cart_item_data_to_order_item`**.

| meta_key | Meaning | PHP | Storage format | Example |
|----------|---------|-----|----------------|---------|
| **`Paket`** | Plan / `gender` slug, title-cased | `add_cart_item_data_to_order_items` — `$item->add_meta_data( 'Paket', ucfirst( $values['gender'] ) )` | Plain string | `Slim`, `Fit`, `Protein`, `Vege` |
| **`Kalorije`** | Calorie tier | Same — `$values['calories'] . ' kcal'` | Plain string | `1300 kcal` |
| **`Tip paketa`** | Package duration | Same — `ucfirst( $values['package_type'] )` | Plain string | `Dnevni`, `Nedeljni5`, `Nedeljni6`, `Mesecni20`, `Mesecni24` (no space in slug keys) |
| **`Datum dostave`** | Human-readable dates | Same — if array: `sort` + `implode( ', ', … )`; else single string | Comma-separated **`d-m-Y`** | `19-02-2025` or `19-02-2025, 20-02-2025` |
| **`delivery_dates`** | Same dates for machines / plugins | `add_cart_item_data_to_order_item` — `$item->add_meta_data( 'delivery_dates', $values['delivery_dates'] )` | WooCommerce serializes: **PHP serialized** array of `d-m-Y` strings (or a string if one date) | `a:2:{i:0;s:10:"19-02-2025";i:1;s:10:"20-02-2025";}` |

**“Number of days” for pricing / logistics:** For `dnevni`, use `count( (array) unserialize( … ) )` on `delivery_dates` or count comma-separated segments in `Datum dostave`. For fixed packages (`nedeljni5`, etc.), business rules use one start date in `delivery_dates`; duration is implied by `Tip paketa`, not a separate meta field.

**`add_cart_item_data_to_order_item` (`inc/woocommerce.php`) — line totals:** If `delivery_dates` is set **and** `strtolower( $values['data']->get_name() )` contains `'dnevni'`, it sets `$item->set_total` / `set_subtotal` to `get_price() * days_count * quantity`. The **product post title** for ID **181** is normally **“Plan ishrane”** (no `dnevni`), so this branch **often does not run**; line money still reflects cart pricing from `_price` / core calculations. Documented here as written for maintenance and plugins.

#### 3.1.9 Related cart/order behavior (product 181)

| Concern | Where |
|---------|--------|
| Cart line title | `modify_cart_item_name` (`woocommerce_cart_item_name`, `inc/meal-plan.php`) — does not add DB meta |
| Line price in cart/checkout | `adjust_cart_item_price`, `update_cart_prices`, `woocommerce_cart_item_price` filters (`inc/meal-plan.php`, `inc/woocommerce.php`) |
| Core WC line totals & product link | `_product_id` (181), `_qty`, `_line_subtotal`, `_line_total`, … — see §3.2 |

**Standard vs vege “menu type” on the order:** Cart/session include `menu_type`, but **no** `add_meta_data` writes “Tip menija” to the order. For plugins, infer from `Paket === 'Vege'` or extend `add_cart_item_data_to_order_items` to persist `menu_type`.

### 3.2 WooCommerce core line item meta (from dump)

| meta_key | approx. rows | Role |
|----------|----------------|------|
| `_product_id` | 5527 | Usually **181** for meal plans |
| `_variation_id` | 5527 | `0` for simple |
| `_qty` | 5527 | Quantity |
| `_line_subtotal` | 5527 | Before tax |
| `_line_total` | 5527 | After line discounts/tax logic |
| `_line_subtotal_tax` | 5527 |
| `_line_tax` | 5527 |
| `_line_tax_data` | 5527 | Serialized tax breakdown |
| `_tax_class` | 5527 |

### 3.3 Shipping / coupon / fee rows (same table, different `order_item_type`)

| meta_key | approx. rows | Notes |
|----------|----------------|-------|
| `method_id` | 4706 | e.g. `flat_rate` |
| `instance_id` | 4706 |
| `cost` | 4706 |
| `total_tax` | 4706 |
| `taxes` | 4706 |
| `Artikli` | 4702 | Localized shipping label text |
| `coupon_info` | 78 |
| `discount_amount` | 78 |
| `discount_amount_tax` | 78 |
| `Items` | 4 |
| `Boja` | 22 | Product attribute on a small set of lines |
| `Veličina` | 22 | Product attribute (spelling as UTF-8) |

---

## 4. Cart & session

| Topic | Detail |
|-------|--------|
| Storage | No dedicated cart table. Default handler stores sessions under **`wp_options`** as `\_wc_session\_%` (serialized `cart`, customer, etc.). |
| This dump | **0** `INSERT` rows into `wp_options` containing `_wc_session_` (export taken with empty / cleaned sessions). |
| Custom **cart line** keys (child) | `menu_type`, `gender`, `calories`, `delivery_dates`, `package_type`, `unique_key`, `_price` (+ core keys: `product_id`, `quantity`, `data`, …) |
| Custom **session** keys (child) | `reload_checkout`, `checkout_language` (Polylang), set on `woocommerce_checkout_order_processed` |

---

## 5. Taxonomies (`wp_terms` / `wp_term_taxonomy` / `wp_term_relationships`)

From `INSERT INTO wp_term_taxonomy` rows matched in dump:

| taxonomy | taxonomy rows in dump |
|----------|------------------------|
| `product_cat` | 2 |
| `product_type` | 4 |

(No `product_tag` / `pa_*` attribute taxonomies in matched inserts for this export.)

---

## 6. Legacy `wp_posts` / `wp_postmeta` for orders

With HPOS, **new** orders do not rely on `post_type = shop_order` in `wp_posts`. Refunds and older tools may still reference posts — this dump has **no** `shop_order` postmeta bucketed.

---

## 7. Theme hook reference (where meta is set)

| Data | Storage | Theme location |
|------|---------|----------------|
| Line meta Paket / Kalorije / Tip paketa / Datum dostave | `wp_woocommerce_order_itemmeta` | `inc/meal-plan.php` → `woocommerce_checkout_create_order_line_item` |
| `delivery_dates` + dnevni totals | same + `_line_total` | `inc/woocommerce.php` → same hook |
| Cart line payload for product **181** | Session / `$cart_item` | `handle_meal_plan_add_to_cart` + JS `assets/js/meal-plan-form.js` → AJAX `add_meal_plan_to_cart` |
| Cart date edits | Session | `assets/js/cart.js` → AJAX `update_cart_delivery_date` (`inc/woocommerce.php`) |
| Checkout/cart extra rows (Tip menija, Plan, …) | Display only | `display_cart_item_custom_meta` → `woocommerce_get_item_data` (`inc/meal-plan.php`) |
| `PAK` on order | Would be `wp_wc_orders_meta` or postmeta if legacy | Parent `kombo/functions.php` — **blocked from typical checkout** by child removing `billing_pak` |
| Prices in admin | `meal_plan_prices` | `inc/meal-plan.php` admin screen |

---

## 8. Appendix — machine-readable summary (JSON)

```json
{
  "schema_version": 3,
  "meal_plan_code_reference_section": "3.1",
  "snapshot_source": "sql/local.sql",
  "database": {
    "name": "local",
    "typical_local_host": "127.0.0.1",
    "typical_local_port": 10091,
    "user": "root"
  },
  "hpos": true,
  "meal_plan_product_id": 181,
  "cart_item_custom_keys": [
    "menu_type", "gender", "calories", "delivery_dates",
    "package_type", "unique_key", "_price"
  ],
  "order_line_meta_meal_theme": [
    "Paket", "Kalorije", "Tip paketa", "Datum dostave", "delivery_dates"
  ],
  "order_line_meta_not_persisted_from_cart_display": [
    "Tip menija (menu_type standard|vege)"
  ],
  "wc_orders_meta_keys_sampled_in_dump": [
    "_billing_address_index", "_shipping_address_index", "is_vat_exempt",
    "_wc_order_attribution_user_agent", "_wc_order_attribution_utm_source",
    "_nestpay_status", "_nestpay_callback_id", "_edit_lock"
  ],
  "dump_row_counts": {
    "wp_wc_orders": 4706,
    "order_itemmeta_rows_by_key": {
      "Paket": 5461,
      "Kalorije": 5461,
      "Tip paketa": 5461,
      "Datum dostave": 5457,
      "delivery_dates": 5457,
      "_line_total": 5527,
      "_product_id": 5527
    }
  }
}
```
