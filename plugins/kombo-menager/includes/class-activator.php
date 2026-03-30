<?php
/**
 * Plugin activation (Phase 1 stub).
 *
 * @package KomboManager
 */

namespace KomboManager;

defined( 'ABSPATH' ) || exit;

/**
 * Runs on register_activation_hook only.
 */
final class Activator {

	/**
	 * Phase 2: create custom tables with dbDelta, register roles/capabilities, default options.
	 *
	 * @return void
	 */
	public static function activate(): void {
		/*
		 * TODO Phase 2 — custom tables ({$wpdb->prefix}km_*):
		 * - km_orders
		 * - km_payments
		 * - km_activity_log
		 * - km_saved_customers
		 * - km_subscriptions
		 *
		 * TODO Phase 2 — roles: km_customer, km_manager, km_kitchen (capabilities per AGENTS.md).
		 */
	}
}
