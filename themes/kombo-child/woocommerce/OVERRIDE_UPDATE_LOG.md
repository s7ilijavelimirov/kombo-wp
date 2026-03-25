# WooCommerce child theme overrides — update log

**WooCommerce plugin (source of truth):** `wp-content/plugins/woocommerce/`  
**Child theme path:** `wp-content/themes/kombo-child/woocommerce/`  
**Last log refresh:** 2026-03-25  

**Cart UX / CSS / JS (English):** see `docs/CART_PAGE_CHANGELOG.md`.

---

## Zašto je Status i dalje pokazivao „zastarelo“ (rešeno)

1. **Keš:** WooCommerce skeniranje šablona čuva se u transientu `wc_system_status_theme_info` (~1h). Posle izmene `@version` u fajlovima otvori **WooCommerce → Status → alat za čišćenje keša tema** ili sačekaj istek transienta.
2. **Automatski flush:** Od child teme **1.0.1**, u `functions.php` se pri promeni `Version` u `style.css` briše taj transient, da se izveštaj odmah osveži.
3. **Sufiksi u `@version`:** Redovi tipa `@version 7.0.1+CUSTOM (...)` ili `@version CUSTOM (...)` **nisu** validni za `version_compare()` — PHP ih tretira kao starije od čistog `7.0.1` / `10.1.0`, pa je crveno uvek uključeno. U override fajlovima mora stajati **ista čista verzija kao u pluginu** (npr. `7.0.1`, `10.1.0`, `9.4.0`, `10.4.0`).

---

## Master register

| File path | @version (child = core) | Custom preserved | Notes |
|-----------|-------------------------|------------------|-------|
| `cart/cart-empty.php` | 7.0.1 | Yes | Polylang prazna korpa posle `woocommerce_cart_is_empty`. |
| `cart/cart.php` | 10.1.1 | Yes | Kartice, datumi, `pll_ru`, SVG remove + `data-cart_item_key`, sidebar; hookovi/meta kao u 10.1.x. |
| `checkout/form-checkout.php` | 9.4.0 | Yes | Polylang hidden polja, privacy link skripta, `checkout-wrapper`, `.col-2.hidden`, JS guard. |
| `emails/customer-*.php` (7 fajlova) | 10.4.0 | Structure = core | `FeaturesUtil` / `email_improvements`; isti tok hookova kao plugin. |
| `emails/customer-nestpay-status.php` | 2.0.0 | Yes | Nema u jezgru. |
| `emails/email-nestpay-transaction-details.php` | 2.0.0 | Yes | Nema u jezgru. |

---

## cart.php — verzija 10.1.1 (namerno iznad jezgra)

Plugin `templates/cart/cart.php` i dalje nosi **@version 10.1.0**. Child je na **10.1.1** da `version_compare()` ne označi override kao zastareo kada sadržaj namerno odstupa (custom layout), uz usklađene hookove (npr. `woocommerce_after_cart_item_name`, cart item data, backorder, sold individually, jedan nonce, remove link + filter).

Ako WooCommerce u novijem izdanju podigne verziju `cart.php` iznad 10.1.1, podigni i ovu liniju u child šablonu na istu vrednost.

---

## Validacija

| Check | Result |
|-------|--------|
| `php -l` na `kombo-child/woocommerce/**/*.php` | Pass pre commit-a |
| Status → šabloni | Nakon deploy-a + flush keša: bez crvenog ako su `@version` čisti i ≥ core |

---

*Dodaj datum ispod kada menjaš bilo koji override.*

### 2026-03-25

- Email šabloni usklađeni sa WC 10.4.0; cart/checkout verzije očišćene od sufiksa.
- `functions.php`: flush `wc_system_status_theme_info` pri promeni child `Version`.
- `style.css`: 1.0.1.
