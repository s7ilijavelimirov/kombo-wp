# Kombo + Kombo child — full theme audit report

**Report version:** 1.5 (child Woo refactor + fragments + audit)  
**Last reviewed:** 2026-03-25  
**Last re-scan:** 2026-03-26  
**Last code/doc alignment:** 2026-03-25 — **v1.5:** child **`wc-cart-fragments` gated**, **`inc/woocommerce/checkout-coupon.php`**, **duplicate hook cleanup**, **PLL / terms / cart-date** hardening; **v1.4** parent escaping + nonce (below).

**Scope:** Parent `kombo` + child `kombo-child` PHP/JS/CSS. **v1.5** completes the **child** hardening pass started in v1.3–v1.4 (meal-plan + parent).

> **Companion references:** `docs/WOOCOMMERCE-DATA-SUMMARY.md` (Woo meta / cart keys), `docs/AI_GUIDELINES.md`, `docs/IMPROVEMENTS.md`, `docs/CART_PAGE_CHANGELOG.md`.

---

## v1.3 changelog (2026-03-25) — meal plan → WooCommerce session


| Change           | Detail                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
| ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Problem**      | Server logs showed `add_to_cart` OK and `cart_contents_count: 1`, but the next page (cart/checkout) often showed an **empty cart** — typical of **admin-ajax `Set-Cookie` + immediate JS redirect** not sticking to the browser session the same way as a full navigation.                                                                                                                                                                                                                                                          |
| **Primary fix**  | **Form POST** from the ordering page (same URL), handled on `**template_redirect`** (`kombo_meal_plan_handle_cart_post`, priority 15): verifies `**wp_verify_nonce( $_POST['meal_plan_nonce'], 'add_meal_plan_to_cart' )**` to match parent `wp_nonce_field` in `views/template-parts/meal-plan-form.php`, then `**kombo_meal_plan_process_add_to_cart_internal()**`, then `**wp_safe_redirect()**` to `**wc_get_cart_url()**` or `**wc_get_checkout_url()**`. Matches the **classic WooCommerce product form → redirect** pattern. |
| **JS**           | `meal-plan-form.js`: `**form[0].submit()`** after injecting hidden fields: `kombo_meal_plan_submit`, `buy_now`, `dates[]`, `pll_lang`. Avoids relying on XHR for the cart mutation.                                                                                                                                                                                                                                                                                                                                                 |
| **Shared logic** | `**kombo_meal_plan_process_add_to_cart_internal( $trace_source )`** centralizes parse, price, `WC()->cart->add_to_cart()`, session save, cookies.                                                                                                                                                                                                                                                                                                                                                                                   |
| **Polylang**     | `**pll_lang`** still sent (price AJAX + POST); `**kombo_meal_plan_ajax_set_polylang_language()**` before processing.                                                                                                                                                                                                                                                                                                                                                                                                                |
| **Legacy**       | `**admin-ajax` `add_meal_plan_to_cart`** kept for compatibility; `**check_ajax_referer( 'meal_plan_nonce', 'nonce' )**` unchanged.                                                                                                                                                                                                                                                                                                                                                                                                  |
| **Dates**        | POST path normalizes `**dates`** to an array and runs `**array_map( 'sanitize_text_field', … )**` in the shared processor (mitigates **SEC-3** / **SEC-D** for this entry point).                                                                                                                                                                                                                                                                                                                                                   |
| **Trace**        | `KOMBO_TRACE_MEAL_PLAN`: look for `**form_post_start`**, `**source":"form_post"**` in `parsed` / `before_redirect_or_json`.                                                                                                                                                                                                                                                                                                                                                                                                         |


---

## v1.4 changelog (2026-03-25) — parent `kombo` hardening


| Area                                          | Change                                                                                                                                                                                                                                                                                                                   |
| --------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `**get_cart_count` AJAX**                     | `**check_ajax_referer( 'get_cart_count', 'nonce' )`**; localized `**wp_create_nonce( 'get_cart_count' )**`; `**update-cart-count.js**` sends `**nonce**`. Guard if `**!WC()->cart**`. **Enqueue** `update-cart-count` only when `**function_exists( 'WC' )`**. Closes parent **SEC-C** for this endpoint.                |
| `**views/front-page.php`**                    | ACF/Carbon output wrapped: `**esc_url**` (images, links, Polylang ordering URLs), `**esc_attr**` (alts via `**get_bloginfo( 'name' )**` — fixes incorrect `**bloginfo()` inside attributes**), `**esc_html`** (titles, labels, `**pll__()**`), `**wp_kses_post**` (WYSIWYG-style body copy, FAQ, paketi text).           |
| `**views/template-parts/side-food-menu.php**` | `**esc_html**` on weekly titles, day names, meal lines.                                                                                                                                                                                                                                                                  |
| `**views/price-list.php**`                    | `**esc_html**` / `**esc_attr**` on group names, package labels, responsive column class modifier.                                                                                                                                                                                                                        |
| `**functions.php` (Woo)**                     | Removed duplicate `**theme_setup`** + bare `**add_theme_support( 'woocommerce' )**`; merged `**gallery_thumbnail_image_width**` into `**yourtheme_add_woocommerce_support()**`; dropped redundant `**my_custom_woocommerce_theme_support**` hook. Behavior equivalent, single declaration path when WC plugin is active. |
| **Deferred**                                  | **No** change to global `**wptheme-frontend`** bundle or `**script.js` defer** (avoid 1:1 layout/JS order risk). Child coupon/fragments deferred to **v1.5** (done there). Remaining parent **views** (e.g. header) may still be swept later.                                                           |


---

## v1.5 changelog (2026-03-25) — child `kombo-child` Woo cleanup

| Area | Change |
|------|--------|
| **`wc-cart-fragments`** | **`kombo_child_should_enqueue_cart_fragments()`** — učitava samo na **cart / checkout / `is_woocommerce()`** (shop, proizvod, taksonomije) + **meal-plan** stranice. Filter **`kombo_child_enqueue_cart_fragments`** za forsiranje (npr. mini-cart plugin na početnoj). |
| **Checkout kupon** | Izdvojeno u **`inc/woocommerce/checkout-coupon.php`**. Primena i dalje **Woo core `wc-ajax` `apply_coupon`** + **`wc_checkout_params.apply_coupon_nonce`** (nema custom PHP coupon handlera). |
| **SEC-A / SEC-B (zastarelo)** | U kodu **nema** “proceed without nonce” coupon handlera; audit redovi uklonjeni kao **neprimeljivi** na trenutni kod. |
| **Duplikati** | Spojeni **`woocommerce_get_endpoint_url`** u **`kombo_child_wc_endpoint_url`**; **jedan** `**woocommerce_cart_item_price**` filter **`kombo_child_cart_item_price_html`** (dnevni + ostalo). |
| **PLL** | **`set_checkout_language`** / **`set_checkout_language_before_fragments`**: **`function_exists('PLL')`**, **`get_language`**, dodela **`curlang`** samo ako jezik postoji. |
| **Uslovi / privatnost** | Checkout checkbox linkovi: **`esc_url`**, **`rel="noopener noreferrer"`** na spoljnim linkovima. |
| **Korpa datumi (AJAX)** | **`handle_update_cart_delivery_date`**: **`new_date`** kao niz + **`sanitize_text_field`** po elementu (**SEC-D** delimično za ovaj endpoint). |

---

## Updated Findings / Improvements

This section records **what changed in the audit narrative** since report **v1.1** after a full re-scan of both themes, **plus v1.3–v1.5** code changes. Older rows marked “documentation-only” refer to **v1.1→v1.2**; **v1.3+** include **real code** changes.

### Re-scan coverage


| Area                | Notes                                                                                                                                                 |
| ------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------- |
| Parent `kombo`      | `functions.php`, `src/theme/**/*.php`, `views/**/*.php`, theme scripts under `src/assets/scripts/`, `public/js` / `public/css` usage via `Assets.php` |
| Child `kombo-child` | `functions.php`, `inc/*.php`, `inc/woocommerce/*.php`, `woocommerce/**/*.php`, `assets/js/*.js`                                                      |
| Patterns            | `wp_ajax_*`, `check_ajax_referer`, `wp_verify_nonce`, `wp_create_nonce`, `$_POST` handling, `echo`/escaping hotspots in `views/front-page.php`        |


### Backend security — verified strengths (updated through v1.5)


| Item                   | Detail                                                                                                                                                                                                                                                          | Location                                                               |
| ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------- |
| Meal plan (public)     | **Primary:** HTTP POST + `**wp_verify_nonce( …, 'add_meal_plan_to_cart' )`** on ordering page (`template_redirect`). **Secondary:** `add_meal_plan_to_cart` AJAX still uses `**check_ajax_referer('meal_plan_nonce', 'nonce')`**. Price/package AJAX unchanged. | `inc/meal-plan.php`, `assets/js/meal-plan-form.js`                     |
| Parent cart count AJAX | `**check_ajax_referer( 'get_cart_count', 'nonce' )**` + localized nonce (**v1.4**).                                                                                                                                                                             | `kombo/functions.php`, `kombo/src/assets/scripts/update-cart-count.js` |
| Cart delivery dates    | `**check_ajax_referer('woocommerce-cart', 'security')**`; **`new_date`** sanitized array (**v1.5**).                                                                                                                                                          | `handle_update_cart_delivery_date` in `inc/woocommerce.php`            |
| Checkout coupon (UI)   | **No custom apply handler** — browser calls Woo **`apply_coupon`** with **`apply_coupon_nonce`**; markup/JS in **`inc/woocommerce/checkout-coupon.php`** (**v1.5**).                                                                                         | `inc/woocommerce/checkout-coupon.php`                                  |
| Cart quantity/update   | `**check_ajax_referer(..., false)**` then `**wp_die('Invalid nonce')**` on failure.                                                                                                                                                                             | `handle_cart_update_action` in `inc/woocommerce.php`                   |
| Pricing admin          | Form save uses `**wp_verify_nonce**` + `**current_user_can('manage_options')**`.                                                                                                                                                                                | `meal_plan_pricing_page` in `inc/meal-plan.php`                        |


### Backend security — gaps and inconsistencies (still open)


| ID        | Severity | Topic                | Detail                                                                                                                                                                                                                    |
| --------- | -------- | -------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| ~~SEC-A~~ | —        | Coupon apply (legacy audit) | **Not applicable (v1.5):** tema ne implementira paralelni coupon AJAX; koristi **Woo `apply_coupon` + core nonce**.                                                                                                |
| ~~SEC-B~~ | —        | Coupon remove (legacy)      | **Not applicable (v1.5):** isto — uklanjanje ide kroz **Woo checkout / ukloni kupon** tokove.                                                                                                                         |
| ~~SEC-C~~ | —        | Parent cart count           | **Resolved (v1.4):** `**get_cart_count`** + **`check_ajax_referer( 'get_cart_count', 'nonce' )**`.                                                                                                                  |
| SEC-D     | **P3**   | Delivery dates (ostalo)     | **Poboljšano (v1.5)** za `**update_cart_delivery_date**`. Ostali ulazi (ako ih ima) i dalje vredni brzog pregleda.                                                                                                  |


### Performance — corrected finding (v1.2)


| Previous statement (v1.1)                                            | Correction                                                                                                                                                                                                                                                                             |
| -------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Implied `wc-cart-fragments` tied to “Woo pages” / `is_woocommerce()` | **v1.2–v1.4:** `**add_cart_fragments_scripts()**` ran whenever `**is_woocommerce**` exists → praktično **ceo front**. **v1.5:** učitavanje samo kad **`kombo_child_should_enqueue_cart_fragments()`** (vidi v1.5 changelog). |


### Score movement (v1.1 → v1.2)


| Metric                            | v1.1 | v1.2    | Rationale                                                                                      |
| --------------------------------- | ---- | ------- | ---------------------------------------------------------------------------------------------- |
| Child — Performance               | 62   | **60**  | Stricter reading: site-wide `wc-cart-fragments` enqueue.                                       |
| Child — Security                  | 58   | **60**  | Credit strict meal-plan + cart AJAX; **coupon path unchanged** (P0/P1 remain).                 |
| Child subtotal (avg of 3 pillars) | ~62  | **~62** | Perf down, security up slightly.                                                               |
| Stack — Security                  | 66   | **67**  | Parent/child average reflects child security nudge.                                            |
| Stack — Performance               | 68   | **67**  | Child correction pulls combined performance down slightly.                                     |
| Stack — Code quality              | 71   | **71**  | No code change; same technical debt profile.                                                   |
| Stack — Woo integration           | 67   | **68**  | Clearer mapping of AJAX to documented flows.                                                   |
| **Overall theme health**          | 68   | **69**  | Net small improvement from documented controls; **coupon backlog** still blocks higher scores. |


### Score movement (v1.2 → v1.3)


| Metric                            | v1.2 | v1.3    | Rationale                                                                                                               |
| --------------------------------- | ---- | ------- | ----------------------------------------------------------------------------------------------------------------------- |
| Child — Code quality              | 66   | **67**  | Meal-plan **shared processor** + clearer **POST vs AJAX** split; less brittle client flow.                              |
| Child — Performance               | 60   | **60**  | Unchanged (fragments / global JS as before).                                                                            |
| Child — Security                  | 60   | **62**  | Form path uses **template-aligned nonce**; **dates** normalized/sanitized in shared add handler; **SEC-A/B** unchanged. |
| Child subtotal (avg of 3 pillars) | ~62  | **~63** | Small uplift from meal-plan hardening.                                                                                  |
| Stack — Security                  | 67   | **68**  | Child security nudge.                                                                                                   |
| Stack — Performance               | 67   | **67**  | No change.                                                                                                              |
| Stack — Code quality              | 71   | **72**  | Slight lift from child structure improvement.                                                                           |
| Stack — Woo integration           | 68   | **71**  | Meal plan now follows **native WC cart session + redirect**; fewer session/cookie edge cases.                           |
| **Overall theme health**          | 69   | **70**  | **+1** after verified cart/checkout behavior; **P0 coupon** still caps security ceiling.                                |


### Score movement (v1.3 → v1.4)


| Metric                   | v1.3        | v1.4        | Rationale                                                                                                           |
| ------------------------ | ----------- | ----------- | ------------------------------------------------------------------------------------------------------------------- |
| Parent — Code quality    | 76          | **79**      | Woo `**add_theme_support`** consolidation; high-traffic templates use `**esc_*` / `wp_kses_post**`.                 |
| Parent — Performance     | 73          | **74**      | `**update-cart-count`** enqueued only when `**WC**` exists (micro savings + clarity).                               |
| Parent — Security        | 73          | **81**      | **SEC-C** closed; **front-page**, **side-food-menu**, **price-list** escaping reduces XSS surface on key templates. |
| Parent subtotal          | ~74         | **~78**     | Average of three parent pillars.                                                                                    |
| Child pillars            | (unchanged) | (unchanged) | No child code in this pass.                                                                                         |
| Stack — Security         | 68          | **72**      | Parent security lift dominates average with child.                                                                  |
| Stack — Code quality     | 72          | **73**      | Parent template + Woo cleanup (~avg with unchanged child).                                                          |
| Stack — Performance      | 67          | **67**      | Unchanged (global bundle / child fragments).                                                                        |
| Stack — Woo integration  | 71          | **72**      | Minor credit for parent Woo bootstrap cleanup.                                                                      |
| **Overall theme health** | 70          | **73**      | **+3**; **P0 coupon (SEC-A)** still primary security ceiling.                                                       |


### Score movement (v1.4 → v1.5)


| Metric                   | v1.4 | v1.5 | Rationale                                                                                              |
| ------------------------ | ---- | ---- | ------------------------------------------------------------------------------------------------------ |
| Child — Code quality     | 67   | **69** | Modul **`checkout-coupon.php`**; jedan **`woocommerce_get_endpoint_url`**; jedan **cart_item_price** filter. |
| Child — Performance      | 60   | **63** | **`wc-cart-fragments`** uslovljen; filter **`kombo_child_enqueue_cart_fragments`** za izuzetke.        |
| Child — Security         | 62   | **68** | SEC-A/B zatvoreni kao neprimeljivi; **PLL** guard; **terms** `esc_url`; **new_date** sanitize.          |
| Child subtotal           | ~63  | **~67** |                                                                                                        |
| Stack — Security         | 72   | **75** | Child + jak parent.                                                                                    |
| Stack — Performance      | 67   | **69** | Fragments gate.                                                                                        |
| Stack — Code quality     | 73   | **74** | Child struktura.                                                                                       |
| Stack — Woo integration  | 72   | **73** | Woo core coupon path eksplicitno dokumentovan.                                                         |
| **Overall theme health** | 73   | **76** | **+3**; sledeći korak: smanjiti **`woocommerce.php`**, global JS, parent ostatak **`views`**.           |


---

### Historical note: documentation-only deltas (pre–v1.1)


| Item                   | Update                                                                                                |
| ---------------------- | ----------------------------------------------------------------------------------------------------- |
| File name              | Prior file `theme-combined-audit.md` replaced by `theme-full-audit-report.md`.                        |
| Parent cart script     | `update-cart-count.js` uses **filemtime** cache busting in `functions.php`.                           |
| Child cart enqueue     | Duplicate global `cart-scripts` pattern **not** present; `enqueue_cart_script()` on `is_cart()` only. |
| AJAX `get_cart_totals` | **Not** registered in child PHP (verify plugins/JS if calls exist).                                   |
| Slick                  | Child prefers **local** `assets/vendor/slick-carousel/1.8.1/` when present.                           |


Scores in the sections below show **v1.5 (updated)**. Use the score movement tables through **v1.4→v1.5**.

---

## Parent Theme Audit

### 1. Executive summary


| Pillar           | Score (0–100) v1.4 | Grade | Notes                                                                                                                                                                                                                              |
| ---------------- | ------------------ | ----- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Code quality** | **79**             | C+    | Strong `WpTheme\*` structure; **v1.4** escaping on **front-page**, **side-food-menu**, **price-list**; Woo support declarations **consolidated** in `functions.php`. Large `views/front-page.php` still a maintainability hotspot. |
| **Performance**  | **74**             | C+    | Global `wptheme-frontend` bundle unchanged (**v1.4** deferred defer/split for 1:1 safety). **Cart-count** script only when `**WC`** active; filemtime versioning.                                                                  |
| **Security**     | **81**             | B−    | PAK checkout validation intact; **v1.4** `**get_cart_count` nonce**; major **XSS reduction** on home + price list + side menu templates. Residual XSS possible in **other** `views/` not touched this pass.                        |


**Subtotal (parent): ~78 / 100**

### 2. Architecture snapshot


| Layer        | Path                             | Role                                                                                              |
| ------------ | -------------------------------- | ------------------------------------------------------------------------------------------------- |
| Bootstrap    | `functions.php`                  | Composer, `\WpTheme\Main::Init()`, Gutenberg off, procedural Woo (RSD, PAK, shop loop, cart AJAX) |
| Application  | `src/theme/`                     | Core, Providers, DB/migrations, Carbon Fields                                                     |
| Templates    | `views/`                         | Hierarchy remap via `Core::TemplateHierarchy`                                                     |
| Built assets | `public/css`, `public/js`        | `WpTheme\Providers\Assets`                                                                        |
| WooCommerce  | `woocommerce/_templates-backup/` | **Not** auto-loaded by WooCommerce                                                                |


### 3. Code quality — issues & recommendations


| ID   | Sev. | Topic         | Detail                                                                                                                                                                                            | Where                            |
| ---- | ---- | ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------- |
| PQ-1 | Med. | Split style   | Woo split between namespaced core and `functions.php`.                                                                                                                                            | `functions.php`                  |
| PQ-2 | Med. | Escaping      | **Improved v1.4:** `front-page.php`, `side-food-menu.php`, `price-list.php` now use `**esc_*` / `wp_kses_post`**. Remaining raw echoes may exist in **other** `views/` (header/footer, examples). | `views/*`                        |
| PQ-3 | Low  | Redundancy    | Repeated `add_theme_support('woocommerce')`.                                                                                                                                                      | `functions.php`                  |
| PQ-4 | Low  | Backup folder | `_templates-backup` confuses override expectations.                                                                                                                                               | `woocommerce/_templates-backup/` |


**Recommendations:** Escaping pass on high-traffic views; consolidate Woo supports; document backup folder as non-runtime.

### 4. Performance — issues & recommendations


| ID   | Sev. | Topic         | Detail                                       | Where                  |
| ---- | ---- | ------------- | -------------------------------------------- | ---------------------- |
| PF-1 | Med. | Global bundle | Main CSS/JS on every front request.          | `Assets.php`           |
| PF-2 | Med. | Front page    | Single huge template; watch meta/query cost. | `views/front-page.php` |
| PF-3 | Low  | Font Awesome  | Optional `node_modules` enqueue.             | `Assets.php`           |


**Recommendations:** Measure CWV; defer/split non-critical JS; lazy media below the fold.

### 5. Security — issues & recommendations


| ID   | Sev. | Topic        | Detail                                                                                                   | Where                   |
| ---- | ---- | ------------ | -------------------------------------------------------------------------------------------------------- | ----------------------- |
| SC-1 | Med. | XSS surface  | **Reduced v1.4** on **front-page**, **side-food-menu**, **price-list**; other templates not yet swept.   | `views/*`               |
| SC-2 | Low  | PAK vs child | Parent still registers PAK hooks; child removes field — keep validation if anything posts `billing_pak`. | `functions.php` + child |


**Recommendations:** Continue `**esc_*`** sweep on remaining `views/`; `**get_cart_count**` nonce **done (v1.4)**.

### 6. WooCommerce integration (parent)


| Item               | Behavior                                                                                                                 |
| ------------------ | ------------------------------------------------------------------------------------------------------------------------ |
| Template overrides | None under live `woocommerce/` tree.                                                                                     |
| Hooks              | RSD currency, PAK field + order meta + emails, shop loop, image sizes, `checkout-billing-pak.js`, `get_cart_count` AJAX. |
| With child active  | Child supplies WC templates and many filters; parent hooks still run — align PAK behavior with product owner.            |


### 7. Supplementary findings (from former narrative audit)

- **Boilerplate / examples:** `views/shortcodes/example-shortcode.php`, `views/widgets/example.php`, `templates/example-template.php` — confirm unused before removal.
- `**yourtheme_*` naming:** Legacy boilerplate in `functions.php`; consider rename in a dedicated refactor (no UX change).
- `**yourtheme_wc_template_part`:** Nonstandard `locate_template` + `WC()->template_path()` — verify interaction with child overrides (Woo normally prefers child files).

---

## Child Theme Audit

### 1. Executive summary


| Pillar           | Score (0–100) v1.5 | Grade | Notes                                                                                                                          |
| ---------------- | ------------------ | ----- | ------------------------------------------------------------------------------------------------------------------------------ |
| **Code quality** | **69**             | C−    | **`checkout-coupon.php`**; manje duplikata u **`woocommerce.php`**; i dalje veliki monolit za budući split po domenima.          |
| **Performance**  | **63**             | D+    | **`wc-cart-fragments`** samo WC + meal-plan kontekst (+ filter); global NestPay/cart URL i dalje u **`setup-assets`**.         |
| **Security**     | **68**             | D+    | Meal-plan POST; cart AJAX; **Woo native coupon**; **PLL** guard; checkout **terms** `esc_url`; **new_date** sanitize (**v1.5**). |


**Subtotal (child): ~67 / 100**

### 2. Architecture snapshot


| Path                                        | Role                                                                            |
| ------------------------------------------- | ------------------------------------------------------------------------------- |
| `functions.php`                             | Loads `inc/*`; WC template scan cache bust                                      |
| `inc/setup-assets.php`                      | Styles, menus, Slick (local vendor when present), global contextual JS          |
| `inc/meal-plan.php`                         | Meal plan **POST + redirect** (primary), legacy AJAX, pricing admin, datepicker |
| `inc/woocommerce.php`                       | Checkout/cart/Polylang (large); **`inc/woocommerce/checkout-coupon.php`** checkout kupon UI |
| `inc/cpt-menus.php`, `polylang-strings.php` | CPTs + strings                                                                  |
| `woocommerce/`                              | **Active** overrides                                                            |
| `assets/js/`                                | Cart, checkout, NestPay, meal plan                                              |


### 3. Code quality — issues & recommendations


| ID   | Sev. | Topic             | Detail                                         | Where                                      |
| ---- | ---- | ----------------- | ---------------------------------------------- | ------------------------------------------ |
| CQ-1 | High | File size         | `woocommerce.php` hard to review/test.         | `inc/woocommerce.php`                      |
| ~~CQ-2~~ | — | Duplicate filters | **Resolved (v1.5):** jedan **`kombo_child_cart_item_price_html`** @ 100. | `inc/woocommerce.php` |
| CQ-3 | Med. | Inline assets     | Large inline CSS/JS in PHP.                    | `inc/woocommerce.php`                      |
| CQ-4 | Low  | Debug / PII       | `print_r($_POST)` in logs when `WP_DEBUG_LOG`. | `inc/meal-plan.php`, `inc/woocommerce.php` |


**Recommendations:** Split by domain (checkout / cart / i18n); **`checkout-coupon.php`** kao obrazac za sledeće izdvajanje; externalizovati inline blokove.

### 4. Performance — issues & recommendations


| ID   | Sev.     | Topic              | Detail                                                                            | Where                                              |
| ---- | -------- | ------------------ | --------------------------------------------------------------------------------- | -------------------------------------------------- |
| PF-1 | Med. | Fragments          | **Improved (v1.5):** `kombo_child_should_enqueue_cart_fragments()` + filter `kombo_child_enqueue_cart_fragments`. | `inc/meal-plan.php` |
| PF-2 | Med.     | Global JS          | Cart URL + NestPay scripts on every front request.                                | `inc/setup-assets.php`                             |
| PF-3 | Med.     | jQuery UI CSS      | CDN fallback when local CSS missing.                                              | `inc/meal-plan.php`                                |
| PF-4 | Low      | Checkout inline JS | Debounce logic adds weight.                                                       | `inc/woocommerce.php`                              |


**Recommendations:** Ako mini-cart mora na čistoj početnoj, dodati **`add_filter('kombo_child_enqueue_cart_fragments', '__return_true');`** u child/plugin; ostali globalni JS i dalje meriti.

### 5. Security — issues & recommendations


| ID    | Sev.     | Topic       | Detail                                                                                                                                                             | Where                                      |
| ----- | -------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------ |
| ~~SEC-1~~ | — | Coupon AJAX | **Resolved / N/A (v1.5):** Woo **`apply_coupon`** + core nonce; nema paralelnog handlera. | `inc/woocommerce/checkout-coupon.php` |
| SEC-2 | Low | Polylang | **Poboljšano (v1.5)** na checkout dodeli `curlang` (guard oko `PLL()` / jezika); ostale `PLL()` tačke po želji isti obrazac. | `inc/woocommerce.php` |
| SEC-3 | Low | Dates | Meal-plan POST (**v1.3**); korpa **`update_cart_delivery_date`** (**v1.5**); ostalo po potrebi. | `inc/meal-plan.php`, `inc/woocommerce.php` |


**Recommendations:** Nastaviti **podelu** `woocommerce.php`; po potrebi **`PLL()`** guard na svim dodelama; **`print_r` u log** samo iza debug zastavica.

### 6. WooCommerce integration (child)


| Area             | Detail                                                                                 |
| ---------------- | -------------------------------------------------------------------------------------- |
| Overrides        | `cart/`, `checkout/`, `emails/`, `myaccount/`, `notices/`                              |
| Meta / cart keys | See `docs/WOOCOMMERCE-DATA-SUMMARY.md`                                                 |
| Parent overlap   | Child removes `billing_pak`; parent PAK hooks remain — documented intentional conflict |
| HPOS             | Compatible with documented order/line-item tables                                      |


### 7. Parent vs child dependency


| Topic        | Note                                                                                                                                                                             |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Templates    | Child `woocommerce/` wins for overridden paths.                                                                                                                                  |
| Hooks        | Load order + priority merge parent + child behavior.                                                                                                                             |
| Meal plan UI | Markup: parent `views/template-parts/meal-plan-form.php`; logic: child JS — **POST + server redirect** for add/buy; AJAX retained for **price preview** (+ legacy add endpoint). |


### 8. Supplementary findings (from former narrative audit)

- **Slick:** Prefer **integrity** attributes if any CDN fallback is reintroduced; current path prefers **local vendor**.
- **Order-received templates:** If using `phpcs:ignore` on filtered HTML, keep filters trusted.
- `**get_cart_totals`:** Not registered in child PHP at last scan — confirm JS/plugins do not call a missing action.

---

## General Summary / Scores

### Final summary table (stack: parent + active child) — v1.5


| Metric                               | Score (0–100) | Notes                                                                                                                         |
| ------------------------------------ | ------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| **Code quality**                     | **74**        | Parent **v1.4**; child **v1.5** — `checkout-coupon.php`, manje duplikata u `woocommerce.php` (~avg parent **79** + child **69**). |
| **Security**                         | **75**        | Parent **v1.4**; child **v1.5** — Woo `apply_coupon` + core nonce, PLL/terms/datumi ojačani; **SEC-A/B** zatvoreni kao N/A.      |
| **Performance**                      | **69**        | Child **v1.5:** uslovljeni `wc-cart-fragments`; parent global bundle i dalje glavni PF fokus.                                  |
| **WooCommerce / custom integration** | **73**        | Meal-plan POST + child overrides; eksplicitno mapiran na Woo core za kupon.                                                   |
| **Overall theme health**             | **76**        | **+3 vs v1.4**; sledeći korak: podela `woocommerce.php`, global JS (`setup-assets`), parent ostatak `views/`.                  |


### Score history (quick reference)


| Metric                  | v1.1 | v1.2   | v1.3   | v1.4   | v1.5   |
| ----------------------- | ---- | ------ | ------ | ------ | ------ |
| Overall theme health    | 68   | **69** | **70** | **73** | **76** |
| Security (stack)        | 66   | **67** | **68** | **72** | **75** |
| Performance (stack)     | 68   | **67** | **67** | **67** | **69** |
| Code quality (stack)    | 71   | **71** | **72** | **73** | **74** |
| Woo integration (stack) | 67   | **68** | **71** | **72** | **73** |


### Grade interpretation


| Range      | Meaning                                                                                                                                                                                                 |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 76 overall | **B−** — Parent **v1.4** escaping + cart AJAX; child **v1.5** uklanja dupli kupon tok i sužava fragmente; dalje poboljšanje: podela child `woocommerce.php`, sužavanje globalnog JS, parent `views/`. |


### Prioritized recommendations (status)


| Priority | Owner  | Action                                                                                                                                                            | Status                                                                                             |
| -------- | ------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------- |
| —        | Child  | Meal-plan **empty cart / lost session** after “Dodaj u korpu” / “Naruči odmah” (AJAX + JS redirect).                                                              | **Closed (v1.3)** — HTTP POST + `wp_safe_redirect`; trace `form_post_start` / `source: form_post`. |
| **P0**   | Child  | Coupon apply/remove: **Woo core** `apply_coupon` + nonce (**SEC-A/B** zastareli u audit-u).                                                                       | **Closed (v1.5)**                                                                                  |
| **P1**   | Child  | Split `inc/woocommerce.php` po domenima (checkout / cart / i18n).                                                                                                | Open                                                                                               |
| **P1**   | Parent | Template escaping sweep (header/footer i ostali `views/`).                                                                                                        | Open (delimično **v1.4** na home/price list/side menu)                                             |
| **P2**   | Child  | Uslovni `wc-cart-fragments` + filter za mini-cart na “čistoj” početnoj.                                                                                           | **Improved (v1.5)** — i dalje meriti; **NestPay** / cart URL JS globalno                                              |
| **P2**   | Parent | Optional nonce for `get_cart_count` AJAX (**SEC-C**).                                                                                                             | **Closed (v1.4)**                                                                                  |
| **P2**   | Child  | Sanitize `new_date` / `dates` (**SEC-D**, SEC-3).                                                                                                                  | **Partial (v1.5)** — meal-plan POST + `update_cart_delivery_date`; ostali ulazi po potrebi          |
| **P3**   | Both   | Remove or archive confirmed-dead boilerplate after repo-wide grep + staging test.                                                                                 | Open                                                                                               |


### Already in good shape (through v1.5)


| Item                           | Note                                                                                                                                                                |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Meal plan — add to cart        | **Primary:** POST + `**wp_verify_nonce( …, 'add_meal_plan_to_cart' )`** + `**wp_safe_redirect**`. **Legacy AJAX:** `check_ajax_referer` on `add_meal_plan_to_cart`. |
| Meal plan — price preview      | AJAX nonces on `get_meal_plan_price` / `get_all_package_prices`.                                                                                                    |
| Parent — cart count            | `**check_ajax_referer( 'get_cart_count', 'nonce' )`** + localized nonce (**v1.4**).                                                                                 |
| Parent — high-traffic escaping | `**front-page.php`**, `**side-food-menu.php**`, `**price-list.php**` use `**esc_*` / `wp_kses_post**` (**v1.4**).                                                   |
| Child — checkout coupon (**v1.5**) | UI u `checkout-coupon.php`; primena preko Woo **`wc-ajax` `apply_coupon`** + **`apply_coupon_nonce`**.                                                          |
| Child — cart fragments (**v1.5**)  | `kombo_child_should_enqueue_cart_fragments()` + filter `kombo_child_enqueue_cart_fragments`.                                                                     |
| Child — cart line price (**v1.5**) | Jedan filter `kombo_child_cart_item_price_html` umesto dva @ 100.                                                                                                  |
| Cart AJAX (non-coupon)         | Delivery-date i cart update: Woo cart nonce + fail closed; **`new_date`** sanitize (**v1.5**).                                                                      |
| Meal plan admin pricing        | Nonce + `manage_options`.                                                                                                                                           |
| Parent cart script versioning  | `filemtime` on `update-cart-count.js`.                                                                                                                              |


### What this report replaced

- Supersedes `theme-combined-audit.md` (removed).  
- Narrative-only `THEME_AUDIT.md` files under parent and child **docs** were merged into earlier revisions of this file and removed. **This file** is the single scored + narrative reference.

