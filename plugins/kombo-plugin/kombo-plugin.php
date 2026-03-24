<?php
/**
 * Plugin Name:       Kombo
 * Description:       Custom funkcionalnosti za Kombo sajt (razvoj).
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            S7Codedesign
 * Text Domain:       kombo-plugin
 *
 * @package Kombo_Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KOMBO_PLUGIN_VERSION', '0.1.0' );
define( 'KOMBO_PLUGIN_FILE', __FILE__ );
define( 'KOMBO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KOMBO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Ovde kasnije: require autoload, hook-ovi, REST, itd.
