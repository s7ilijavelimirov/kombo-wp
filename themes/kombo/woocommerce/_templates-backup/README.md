# WooCommerce template backups (not loaded by WooCommerce)

PHP files in this directory live under the non-standard path `_templates-backup/`. **WooCommerce only loads overrides from `woocommerce/<path>`** (for example `woocommerce/cart/cart.php`), not from `_templates-backup/`.

These copies are kept for reference or historical comparison. They do not affect the storefront unless something manually `include`s them (none do in the audited theme).

**Active cart/checkout overrides** for this site live in the **child theme**: `kombo-child/woocommerce/`.

## Remaining backup files (differ from current WooCommerce core)

These were kept because their contents **do not** match `plugins/woocommerce/templates/` (reference only):

- `cart/cart.php`
- `cart/mini-cart.php`
- `cart/cross-sells.php`
- `cart/shipping-calculator.php`

Files that were **byte-identical** to core at the time of cleanup were removed (see project `docs/CHANGELOG.md`).
