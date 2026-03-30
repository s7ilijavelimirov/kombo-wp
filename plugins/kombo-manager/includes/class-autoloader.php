<?php
/**
 * Simple autoload for KomboManager\* → includes/ (and subfolders).
 *
 * @package KomboManager
 */

namespace KomboManager;

defined( 'ABSPATH' ) || exit;

/**
 * Registers spl_autoload for plugin classes.
 */
final class Autoloader {

	private const PREFIX = 'KomboManager\\';

	/**
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( array( self::class, 'autoload' ) );
	}

	/**
	 * @param string $class Fully qualified class name.
	 * @return void
	 */
	public static function autoload( string $class ): void {
		if ( strncmp( self::PREFIX, $class, strlen( self::PREFIX ) ) !== 0 ) {
			return;
		}

		$relative = substr( $class, strlen( self::PREFIX ) );
		$relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );
		$segments = explode( DIRECTORY_SEPARATOR, $relative );
		$short    = array_pop( $segments );

		if ( ! is_string( $short ) || $short === '' ) {
			return;
		}

		$kebab   = strtolower( (string) preg_replace( '/([a-z])([A-Z])/', '$1-$2', $short ) );
		$subdir  = $segments ? implode( DIRECTORY_SEPARATOR, $segments ) . DIRECTORY_SEPARATOR : '';
		$path    = KM_DIR . 'includes' . DIRECTORY_SEPARATOR . $subdir . 'class-' . $kebab . '.php';

		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
}
