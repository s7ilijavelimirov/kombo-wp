# Kombo (Parent) — Improvement Plan

**Policy:** Suggestions only. Do not apply on production without testing. This site is live.

---

### SAFE Improvements (can be applied safely)

- **Cache-busting for `update-cart-count.js`** — Use `filemtime` like `checkout-billing-pak.js` to avoid stale browser cache (no behavior change).
- **Documentation** — Keep this `docs/` folder updated when adding hooks in `functions.php` vs `src/theme/`.
- **Clarify `woocommerce/_templates-backup/`** — Add a short README inside that folder explaining it is not loaded by WooCommerce (documentation-only change to a new file if desired — optional).

---

### MEDIUM Risk (needs testing)

- **Scope `update-cart-count.js`** to templates that render the cart counter (may break header counter if scoped wrong).
- **Consolidate `add_theme_support('woocommerce')`** into one `after_setup_theme` callback to reduce redundancy and clarify gallery options.
- **Review `yourtheme_wc_template_part` filter** — Confirm it does not interfere with child overrides or WooCommerce template debugging; remove or simplify only after staging tests.
- **Google Fonts** — If fonts are self-hosted or swapped, update `Actions::WpHead()` preconnects accordingly.

---

### HIGH Risk (do not apply without full regression)

- **Removing or relocating procedural WooCommerce code** from `functions.php` (currency, PAK, shop loop, image dimensions) — affects checkout, admin orders, and shop layout.
- **Deleting `woocommerce/_templates-backup/`** without backup and diff review.
- **Changing `Core::TemplateHierarchy` remapping** — affects every theme template resolution.
- **Disabling Phinx migrations / Eloquent boot** — can break admin or theme options that depend on DB layer.

---

### Performance

- Safe: script versioning for cart count JS.
- Medium: conditional enqueue for cart-related scripts.
- CDN removal for Slick is **child theme** concern (see child `docs/IMPROVEMENTS.md`).

---

### WooCommerce structure

- Prefer new overrides in **child** only.
- Parent should eventually hold only theme support and generic hooks, not store-specific checkout fields — **high-effort migration**, coordinate with child `billing_pak` removal story.

---

### CDN removal strategy (Slick)

- Implemented in **child** `enqueue_slick_slider()` today; moving to local vendor files is a **child** change with front-page QA (see child improvements doc).

---

*End of parent improvements plan.*
