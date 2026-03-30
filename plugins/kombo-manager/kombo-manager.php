<?php
/**
 * Plugin Name:       Kombo Manager
 * Description:       Order management, manager workflows, kitchen views, labels, payments, and subscriptions for kombomeals.rs (WooCommerce).
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            s7codedesign
 * Author URI:        https://www.s7codedesign.com/
 * Text Domain:       kombo-manager
 * Domain Path:       /languages
 *
 * @package KomboManager
 */

/* Developer: Ilija | s7codedesign */

defined( 'ABSPATH' ) || exit;

define( 'KM_VERSION', '0.1.0' );
define( 'KM_FILE', __FILE__ );
define( 'KM_DIR', plugin_dir_path( __FILE__ ) );
define( 'KM_URL', plugin_dir_url( __FILE__ ) );

require_once KM_DIR . 'includes/class-autoloader.php';

KomboManager\Autoloader::register();

register_activation_hook( KM_FILE, array( KomboManager\Activator::class, 'activate' ) );
register_deactivation_hook( KM_FILE, array( KomboManager\Deactivator::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		KomboManager\KomboManager::get_instance();
	},
	1
);
