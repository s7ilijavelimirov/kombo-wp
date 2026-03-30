---
name: plugin-scaffold
description: Scaffold the kombo-menager plugin skeleton. Use when creating initial plugin structure, main plugin file, autoloader, or base classes from scratch.
---

# Plugin Scaffold — kombo-menager

## Before writing any file
1. Read @wp-content/themes/kombo-child/docs/WOOCOMMERCE-DATA-SUMMARY.md
2. Read @wp-content/themes/kombo-child/docs/theme-full-audit-report.md
3. Read @wp-content/plugins/kombo-menager/docs/sastanak-notes.md

## Files to create in order

### 1. kombo-menager.php (main plugin file)
- WordPress plugin header (Plugin Name, Description, Version, Author, Text Domain)
- Define constants: KM_VERSION, KM_FILE, KM_DIR, KM_URL
- Require autoloader
- Register activation/deactivation hooks
- Initialize main class on plugins_loaded hook
- No logic here — bootstrap only

### 2. includes/class-autoloader.php
- Namespace: KomboManager
- Class: Autoloader
- register() method: spl_autoload_register
- Maps KomboManager\Admin\ClassName → includes/Admin/class-class-name.php
- Maps KomboManager\Core\ClassName → includes/Core/class-class-name.php
- Maps KomboManager\ClassName → includes/class-class-name.php

### 3. includes/class-kombo-manager.php
- Namespace: KomboManager
- Class: KomboManager
- Singleton: private static $instance, public static get_instance()
- Constructor: private, calls $this->load_dependencies() and $this->init_hooks()
- load_dependencies(): instantiate Admin, Frontend subclasses (stubs for now)
- init_hooks(): register_activation_hook, plugins_loaded — stubs only
- Phase 1: no real functionality, just wired structure

### 4. includes/class-activator.php
- Namespace: KomboManager
- Class: Activator
- Static method: activate()
- Phase 1: stub with TODO comments listing all tables to create in Phase 2
- TODO list: km_orders, km_payments, km_activity_log, km_saved_customers, km_subscriptions
- TODO: register custom roles (km_customer, km_manager, km_kitchen)

### 5. includes/class-deactivator.php
- Namespace: KomboManager
- Class: Deactivator
- Static method: deactivate()
- Phase 1: stub only

## Folder structure to create
kombo-menager/
├── kombo-menager.php
├── includes/
│   ├── class-kombo-manager.php
│   ├── class-autoloader.php
│   ├── class-activator.php
│   ├── class-deactivator.php
│   ├── Core/
│   ├── Admin/
│   ├── Api/
│   └── Frontend/
├── templates/
│   ├── admin/
│   └── frontend/
├── assets/
│   ├── css/
│   └── js/
├── languages/
└── docs/
    └── sastanak-notes.md

## Output check
After scaffold, verify:
- Plugin appears in WP admin → Plugins list
- No PHP errors on activation
- No conflicts with kombo or kombo-child theme