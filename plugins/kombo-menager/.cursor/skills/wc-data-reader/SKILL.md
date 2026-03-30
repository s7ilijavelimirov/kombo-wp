---
name: wc-data-reader
description: Read and understand existing WooCommerce and theme data structures before writing plugin code. Use when integrating with WC orders, products, user data, or when unsure how existing data is structured on kombomeals.rs.
---

# WC Data Reader — kombomeals.rs

## Purpose
Before writing any code that touches WooCommerce data, read existing structures.
This prevents duplicate fields, hook conflicts, and data inconsistencies.

## Always read first
1. @wp-content/themes/kombo-child/docs/WOOCOMMERCE-DATA-SUMMARY.md
   - Product structure (meal packages, variations, meta fields)
   - Existing order meta fields
   - Existing customer meta fields
   - WC hooks already in use

2. @wp-content/themes/kombo-child/docs/theme-full-audit-report.md
   - Hooks registered by theme
   - Scripts/styles enqueued
   - Template overrides active

## Rules when integrating with WC
- Never create a meta field that already exists in WOOCOMMERCE-DATA-SUMMARY.md
- Never hook into an action/filter already used by the theme
- Use WC order meta (wc_get_order, $order->get_meta()) — not direct DB queries on WC tables
- Use WC customer functions — not direct wp_usermeta queries where WC already wraps them
- If plugin needs to extend a WC order → add meta keys prefixed with _km_

## Key WC data to be aware of (from kombomeals.rs)
- Products are meal packages with variations (size, type, calorie level, delivery schedule)
- Orders contain meal selection + delivery date meta
- Read WOOCOMMERCE-DATA-SUMMARY.md for exact meta key names before using them