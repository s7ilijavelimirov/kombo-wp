# AI / Developer Guidelines — Kombo (Parent Theme)

Use this file when modifying or extending the **parent** theme (`kombo`).

---

## Structure summary

- **Boot:** `functions.php` → Composer autoload → `\WpTheme\Main::Init()`.
- **Namespaced code:** `src/theme/` (`WpTheme\Core`, `WpTheme\Providers`, etc.).
- **Rendered templates:** `views/` (selected via `Core::TemplateHierarchy` when a matching file exists under `views/`). Use **`get_header()`** / **`get_footer()`** in those templates — root **`header.php`** / **`footer.php`** load `views/partials/header.php` and `views/partials/footer.php` (standard WordPress flow; plugins can hook `get_header` / `get_footer` actions).
- **Built assets:** `public/css/style.css`, `public/js/script.js` (do not hand-edit if the build pipeline generates them).

---

## Where to add functionality

- **Store-specific, client, or WooCommerce customization:** add in **`kombo-child`** (active child), not here — keeps updates mergeable and avoids losing changes on parent updates.
- **Parent-appropriate changes:** shared utilities, new CPTs that belong in the base theme, global theme supports, or fixes to `WpTheme` providers — still prefer a **child** hook if the change is site-specific.

---

## WooCommerce rules

- **Store schema / meta (child):** canonical reference is `kombo-child/docs/WOOCOMMERCE-DATA-SUMMARY.md`; full theme audit report: `kombo-child/docs/theme-full-audit-report.md`.
- **Do not** add new `woocommerce/` template overrides in the parent while **`kombo-child` is active** for store templates — use the child `woocommerce/` directory.
- Parent `woocommerce/_templates-backup/` is **not** a live override path; do not treat it as authoritative runtime code.
- Parent `functions.php` already contains WooCommerce hooks; before adding more here, check whether **`kombo-child/inc/woocommerce.php`** already addresses the same concern (avoid duplicate filters).

---

## Enqueuing scripts and styles

- Parent frontend bundle: registered in `src/theme/Providers/Assets.php`, enqueued from `Actions::WpEnqueueScripts()`.
- For **one-off** scripts in parent, follow WordPress APIs: `wp_enqueue_script` with explicit dependencies, prefer `filemtime` or theme version for cache busting, load in footer when possible (`true`).
- Do not bypass the build system for assets that belong in the webpack/vite pipeline without team agreement.

---

## Coding standards

- Prefer **WordPress PHP Coding Standards** for new code (spacing, Yoda conditions where team uses them, escaping output).
- New classes should live under `src/theme/` with `WpTheme\` namespace and PSR-4 autoloading (see `composer.json`).
- Escape output: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post` as appropriate.

---

## What must NOT be changed without explicit approval

- `Core::TemplateHierarchy` — remapping `views/*.php` for hierarchy names; changing it breaks template resolution across the theme.
- Database migrations (`PhinxWrapper`) and Eloquent connection — data integrity.
- Production behavior of WooCommerce hooks in `functions.php` (currency, checkout fields, shop loop) — financial and legal impact.

---

## Live site discipline

- No experimental refactors on production branches.
- Test WooCommerce flows (cart, checkout, payment gateways, emails) on staging after any hook change.

---

*Guidelines for human and AI contributors.*
