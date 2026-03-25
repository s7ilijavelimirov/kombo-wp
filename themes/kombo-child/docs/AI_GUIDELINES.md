# AI / Developer Guidelines — Kombo Child Theme

The **child theme is the primary logic source** for this site’s customizations.

---

## Structure summary

- **Entry:** `functions.php` loads `inc/setup-assets.php`, `inc/meal-plan.php`, `inc/cpt-menus.php`, `inc/polylang-strings.php`, `inc/woocommerce.php` (which pulls in `inc/woocommerce/checkout-coupon.php` for checkout coupon UI + Woo `apply_coupon` flow).
- **WooCommerce views:** `woocommerce/` mirrors WooCommerce template paths (these override core when present).
- **Front-end JS:** `assets/js/`.
- **Styles:** `style.css` (metadata + rules).

---

## Where to add new functionality

- **Default:** Add PHP to a new file under `inc/` and `require_once` from `functions.php`, or extend an existing `inc/` file if tightly related. For Woo checkout/cart-only logic, prefer a dedicated file under `inc/woocommerce/` (see `checkout-coupon.php`) and `require_once` from `inc/woocommerce.php` to avoid a single huge file.
- **Template markup:** Add or edit files under `kombo-child/woocommerce/` or parent `views/` only when necessary — prefer **child** for WooCommerce; use parent `views/` only for non-Woo page structure shared across sites.

---

## WooCommerce rules

- **Data reference (HPOS, cart keys, line-item meta, hooks):** `docs/WOOCOMMERCE-DATA-SUMMARY.md` — use this for plugin integration and audits (`docs/theme-full-audit-report.md`).
- **NEVER** duplicate template overrides in the parent if the child already owns that path — WordPress loads **child** templates first.
- New overrides: copy from WooCommerce core **or** current child file, then modify; note WooCommerce template version headers in file docblocks.
- When adding filters, document **priority** relative to existing callbacks in `inc/woocommerce.php` (many filters stack).

---

## Enqueuing scripts and styles

- Use `wp_enqueue_script` / `wp_enqueue_style` on `wp_enqueue_scripts`.
- Prefer **`filemtime()`** for child assets under `assets/` for cache busting.
- Avoid loading heavy scripts globally — match conditions used elsewhere (`is_cart()`, `is_checkout()`, `is_front_page()`, custom page slug helpers like `kombo_child_needs_meal_plan_assets()`).
- For localized data, use `wp_localize_script` after `wp_register_script` when possible.

---

## Coding standards

- Sanitize input: `sanitize_text_field`, `absint`, etc.
- Escape output in templates: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.
- AJAX: `check_ajax_referer` or `wp_verify_nonce` as appropriate; never trust `$_POST` without validation.
- Avoid `error_log(print_r($_POST))` on production code paths.

---

## What must NOT be changed casually

- Polylang-specific URL filters and `PLL()->curlang` assignments — multilingual checkout breaks easily.
- Cart price calculation hooks for meal plans / “dnevni” packages — revenue impact.
- NestPay / email templates under `woocommerce/emails/` — legal and customer communications.

---

## Live site discipline

- Stage all WooCommerce and payment changes.
- After enqueue changes, test **cart**, **checkout**, **thank you**, and **my account** in each active language.

---

*Guidelines for human and AI contributors.*
