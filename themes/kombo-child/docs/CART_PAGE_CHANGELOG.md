# Cart page — implementation changelog (Kombo Child + Kombo parent)

English summary of cart-related work. Template version audit lives in `woocommerce/OVERRIDE_UPDATE_LOG.md`.

---

## Child theme — `kombo-child`

### WooCommerce template overrides

| File | Purpose |
|------|---------|
| `woocommerce/cart/cart.php` | Custom card layout, `data-package-type`, delivery date block (`.product-date` / `.date` / `.date-display`), hidden `.cart-datepicker`, Polylang strings, totals sidebar. `@version` aligned with core + bump for custom layout where needed. |
| `woocommerce/cart/cart-empty.php` | Polylang empty state (title + two CTAs). **`remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 )`** so the default WC “Your cart is currently empty” block is not printed (avoids duplicate copy next to custom UI). |
| `woocommerce/checkout/form-checkout.php` | Unchanged in this doc scope; Polylang + layout wrappers. |

### Scripts & styles (PHP)

- **`inc/woocommerce.php` — `enqueue_cart_script()`**  
  - Enqueues `assets/js/cart.js` on `is_cart()` with `filemtime`.  
  - Calls **`kombo_child_enqueue_jquery_ui_datepicker_base_css()`** so the cart datepicker popup matches the ordering page (jQuery UI base CSS + parent `_calendar.scss`).  
  - **`wp_localize_script( 'cart-script', 'komboCartI18n', … )`** for the “no date selected” string.

- **`inc/meal-plan.php`**  
  - **`kombo_child_enqueue_jquery_ui_datepicker_base_css()`** — shared enqueue for ordering + cart (local vendor path with CDN fallback).

- **`functions.php`**  
  - Clears WooCommerce system status theme transient when child **`style.css` Version** changes (avoids stale “outdated template” notices after updates).

### `assets/js/cart.js`

- Single delegated handlers for form submit / update cart / qty change (not duplicated per line item).  
- AJAX remove from cart → full reload (**no** `block()` overlay on the row).  
- Datepicker per `.product-wrapper`: `data-package-type` for daily vs weekday-only packages, `beforeShow` adds **`weekdays-only`** on `dpDiv` when relevant.  
- Click / keyboard on **`.date`** opens picker; updates **`.date-display`** after successful `update_cart_delivery_date` AJAX.  
- **`$("#apply_coupon").trigger("click")`** (not `.click()`) for Enter key in coupon field — jQuery Migrate–friendly.

### Other (related sessions)

- **Slick** (`assets/vendor/slick-carousel/.../slick.min.js`): `jQuery.type` usages replaced with `Array.isArray` / `typeof` checks to silence Migrate 3.x warnings on the front page (not cart-specific).

---

## Parent theme — `kombo`

### `src/assets/styles/templates/_cart.scss`

- **`.woocommerce-cart .woocommerce`** (empty cart + notices): **`flex-direction: column`**, **`align-items: stretch`**, **`width: 100%`**, **`gap`**, full-width **`.woocommerce-notices-wrapper`** and notices — fixes notice vs empty content sitting in a row.  
- **`.wc-empty-cart-message`** remains hidden as a safety net if markup appears.

### `src/assets/styles/templates/_empty_cart.scss`

- **`.empty-cart-buttons`**: **`display: flex`**, **`flex-direction: column`**, **`align-items: center`** — title on top, button row centered below.

### `views/front-page.php`

- Instagram Slick init: **`.on('resize' / 'click', …)`** instead of deprecated `.resize()` / `.click()` shorthands (Migrate).

---

## Build note

After editing parent SCSS, compile:

```bash
cd wp-content/themes/kombo
npx sass ./src/assets/styles/style.scss ./public/css/style.css --style=expanded --no-source-map
```

Or use `npm run dev` / `npm run build` in the same theme folder.

---

*Last updated: 2026-03-25*
