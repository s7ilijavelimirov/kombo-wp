# Kombo Child — Improvement Plan

**Policy:** Suggestions only. Production requires staged testing.

---

### SAFE Improvements (can be applied safely)

- **Remove unused import** — `use Illuminate\Support\Arr;` from `functions.php` if no other file in child relies on it (grep first).
- **Reduce debug noise** — Remove or guard `error_log` / `print_r($_POST)` in `meal-plan.php` and `woocommerce.php` behind `WP_DEBUG` checks (behavior neutral on production if debug already off; still reduces risk when debug is on).
- **Slick local assets** — Add vendor files under `assets/vendor/slick/` and replace CDN URLs in `enqueue_slick_slider()` with `get_stylesheet_directory_uri()` + `filemtime` (test front page slider thoroughly).

---

### MEDIUM Risk (needs testing)

- **Deduplicate `cart.js` registration** — Remove either `enqueue_cart_script` or `enqueue_cart_scripts`, or merge into one registered handle with correct conditional enqueue; **full cart and mini-cart QA**.
- **Scope `kombo_child_enqueue_context_scripts`** — Load NestPay / cart-widget scripts only on pages that need them; risk breaking side cart or order views if mis-scoped.
- **Resolve dual `woocommerce_cart_item_price` filters** — Merge into one function to avoid redundant work and ambiguous HTML; **compare cart line totals** before/after.
- **jQuery UI CSS CDN** (`meal-plan.php`) — Host locally or bundle with theme for CSP and reliability; test datepicker styling.
- **Verify or implement `get_cart_totals` AJAX handler** — If unused, remove `add_action` lines; if used by JS, implement handler with proper nonce and capability checks.

---

### HIGH Risk (do not apply lightly)

- **Refactor `inc/woocommerce.php` into classes/namespaces** — Large diff, easy to miss hook priority interactions (Polylang + Woo + NestPay + coupons).
- **Change Polylang `PLL()->curlang` manipulation** during checkout — can break language consistency or caching.
- **Bulk changes to checkout field filters** — Legal/business implications for required fields and order meta.

---

### Performance

- Consolidate cart script enqueue (medium).
- Conditional loading for global context scripts (medium).
- Local Slick + optional local jQuery UI theme (safe to medium).

---

### WooCommerce structure fixes (incremental)

1. Document every `add_filter` / `add_action` in `woocommerce.php` in a single internal reference (spreadsheet or markdown) with priority and purpose.
2. Avoid adding new template overrides in parent theme.
3. After consolidating cart JS, run Lighthouse on cart/checkout.

---

### CDN removal strategy

1. **Slick:** Vendor under child `assets/vendor/slick/`, enqueue CSS/JS with dependencies `array('jquery')`, version = `filemtime`.
2. **jQuery UI theme:** Download theme CSS matching `1.12.1` or align WP’s bundled jQuery UI version with theme CSS version.
3. **Optional:** Subresource Integrity if keeping any CDN.

---

*End of child improvements plan.*
