---
name: translations
description: Handle Polylang string registration, i18n setup, or any translation-related task in kombo-menager plugin. Use when adding new user-facing strings, setting up language files, or working with multilingual content.
---

# Translation Workflow — kombo-menager

## Languages
- Serbian (sr_RS) — default language
- Russian (ru_RU)
- English (en_US)

## Where strings live
All Polylang string registrations → includes/class-i18n.php → register_strings()
All .po/.mo files → languages/

## When adding a new feature with user-facing strings

### Step 1 — Use __() in PHP
```php
__( 'Your order has been placed', 'kombo-menager' )
```

### Step 2 — Register in class-i18n.php if it's a frontend string
```php
pll_register_string( 'order-placed', 'Your order has been placed', 'Kombo Manager' );
```

### Step 3 — Retrieve with pll__() on frontend output
```php
echo esc_html( pll__( 'Your order has been placed' ) );
```

## class-i18n.php structure
```php
namespace KomboManager;

class I18n {
    public function load_plugin_textdomain(): void {
        load_plugin_textdomain(
            'kombo-menager',
            false,
            dirname( plugin_basename( KM_FILE ) ) . '/languages'
        );
    }

    public function register_strings(): void {
        if ( ! function_exists( 'pll_register_string' ) ) {
            return; // Polylang not active, skip
        }
        // Register all frontend strings here
        pll_register_string( 'key', 'Default string', 'Kombo Manager' );
    }
}
```

## Important: always guard Polylang functions
```php
if ( function_exists( 'pll_register_string' ) ) { ... }
if ( function_exists( 'pll__' ) ) { ... } else { __( '...', 'kombo-menager' ); }
```
Plugin must work even if Polylang is deactivated.

## .po file workflow
- After adding new strings → update languages/kombo-menager.pot
- Translator provides kombo-menager-ru_RU.po and kombo-menager-sr_RS.po
- Compile to .mo with: msgfmt kombo-menager-ru_RU.po -o kombo-menager-ru_RU.mo
- Or use Poedit

## String categories for this plugin
Frontend (needs pll_register_string):
- Order status labels
- Payment method labels  
- User dashboard messages
- Email notifications
- Error/success messages shown to customers

Admin only (standard __() sufficient):
- Manager dashboard labels
- Kitchen view labels
- Admin notices
- Settings field labels