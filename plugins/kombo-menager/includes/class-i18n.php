<?php
/**
 * Handles plugin text domain and Polylang string registration.
 *
 * @package KomboManager
 */

namespace KomboManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class I18n
 *
 * All translatable string registration goes through this class.
 * Frontend strings → pll_register_string()
 * Admin strings → standard __() / _e() is sufficient
 */
class I18n {

	/**
	 * Load plugin text domain.
	 * Hook: plugins_loaded
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'kombo-manager',
			false,
			dirname( plugin_basename( KM_FILE ) ) . '/languages'
		);
	}

	/**
	 * Register frontend strings with Polylang.
	 * Hook: plugins_loaded (after text domain load)
	 *
	 * Add every new frontend string here — never inline in templates.
	 */
	public function register_strings(): void {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}

		$strings = $this->get_frontend_strings();

		foreach ( $strings as $key => $default ) {
			pll_register_string( $key, $default, 'Kombo Manager' );
		}
	}

	/**
	 * All frontend-visible strings.
	 * Key: unique slug, Value: default English string
	 *
	 * @return array<string, string>
	 */
	private function get_frontend_strings(): array {
		return [
			// Order statuses
			'order-status-active'    => 'Active',
			'order-status-completed' => 'Completed',
			'order-status-cancelled' => 'Cancelled',

			// Payment methods
			'payment-card'           => 'Card',
			'payment-bank-transfer'  => 'Bank transfer',
			'payment-cod'            => 'Cash on delivery',

			// Dashboard
			'dashboard-title'        => 'My Account',
			'dashboard-orders'       => 'My Orders',
			'dashboard-payments'     => 'Payment history',
			'dashboard-subscription' => 'My Subscription',

			// Financial card
			'finance-total-debt'     => 'Total due',
			'finance-total-paid'     => 'Total paid',
			'finance-remaining'      => 'Remaining balance',

			// Subscription
			'subscription-expires'   => 'Your subscription expires on',
			'subscription-renew'     => 'Renew subscription',

			// Messages
			'order-placed'           => 'Your order has been placed successfully.',
			'order-updated'          => 'Your order has been updated.',
			'payment-registered'     => 'Payment registered successfully.',
		];
	}

	/**
	 * Helper: get translated frontend string.
	 * Falls back to __() if Polylang not active.
	 *
	 * @param string $key String key from get_frontend_strings()
	 * @return string
	 */
	public static function get( string $key ): string {
		$strings = ( new self() )->get_frontend_strings();

		if ( ! isset( $strings[ $key ] ) ) {
			return '';
		}

		if ( function_exists( 'pll__' ) ) {
			return pll__( $strings[ $key ] );
		}

		return __( $strings[ $key ], 'kombo-manager' );
	}
}