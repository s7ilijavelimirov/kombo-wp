# wp-config.php — Debug setup (reference only)

**This file documents the audited `wp-config.php` in the WordPress root (`app/public/wp-config.php`).**  
**No changes were made to `wp-config.php` during the audit.**

---

## Current state (audited)

The following constants are defined:

- `WP_DEBUG` — `true`
- `WP_DEBUG_LOG` — `true`
- `WP_DEBUG_DISPLAY` — `true`
- `SCRIPT_DEBUG` — `true`
- `WP_ENVIRONMENT_TYPE` — `'production'`

**Observation:** Comments in `wp-config.php` describe production debugging as disabled, but `WP_DEBUG` and related flags are **enabled**. `WP_DEBUG_DISPLAY` set to `true` can expose notices and errors to visitors — **high risk on a public production URL**. `WP_ENVIRONMENT_TYPE` is `production` while debug display is on — **inconsistent**.

---

## Recommended pattern for local / staging development

Add or adjust **only in non-production** environments (or behind your deployment policy):

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );
```

Optional during asset debugging:

```php
define( 'SCRIPT_DEBUG', true );
```

For **production**, typical practice is:

```php
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG', false );
```

**Do not apply automatically** — coordinate with hosting and the team.

---

## Where `debug.log` is written

When `WP_DEBUG_LOG` is `true`, WordPress writes to:

**`wp-content/debug.log`**

For this project, that resolves to:

`app/public/wp-content/debug.log`

(path relative to the WordPress root containing `wp-config.php`).

Ensure the web server can write to `wp-content/`; restrict public HTTP access to `debug.log` (it should not be web-readable).

---

## Safe use of logs in development

1. Enable `WP_DEBUG_LOG` and set `WP_DEBUG_DISPLAY` to `false` so errors are not shown to users but are recorded.
2. Reproduce the issue once, then inspect `debug.log` (tail via SSH or local file open).
3. Rotate or delete large log files periodically; never commit `debug.log` to git.
4. Remove verbose `error_log()` / `print_r($_POST)` from theme code when finished debugging (see child theme audit — meal plan / WooCommerce AJAX).

---

*Documentation only — edit `wp-config.php` manually when your environment policy allows.*
