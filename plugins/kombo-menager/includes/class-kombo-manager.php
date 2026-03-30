<?php
/**
 * Main plugin bootstrap (singleton).
 *
 * @package KomboManager
 */

namespace KomboManager;

defined( 'ABSPATH' ) || exit;

/**
 * Core plugin singleton; Phase 1 registers hooks only as stubs.
 */
final class KomboManager {

	private static ?self $instance = null;
	private I18n $i18n;

	/**
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
		load_plugin_textdomain( 'kombo-manager', false, dirname( plugin_basename( KM_FILE ) ) . '/languages' );
	}

	/**
	 * Phase 2+: load Admin, Frontend, Core modules.
	 *
	 * @return void
	 */
	private function load_dependencies(): void {
		$this->i18n = new I18n();
	}

	/**
	 * Phase 2+: WooCommerce checks, admin_menu, REST, WC hooks, etc.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'plugins_loaded', array( $this->i18n, 'load_plugin_textdomain' ) );
		add_action( 'plugins_loaded', array( $this->i18n, 'register_strings' ) );
	}
}
