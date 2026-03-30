<?php
/**
 * Plugin deactivation (Phase 1 stub).
 *
 * @package KomboManager
 */

namespace KomboManager;

defined( 'ABSPATH' ) || exit;

/**
 * Runs on register_deactivation_hook only.
 */
final class Deactivator {

	/**
	 * Phase 2+: clear scheduled events, transient flags; do not drop tables unless explicitly required.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		// Intentionally empty in Phase 1.
	}
}
