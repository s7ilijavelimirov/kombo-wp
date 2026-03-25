<?php

if (!function_exists('kombo_child_debug_log')) {
    /**
     * Write to debug.log only when WP_DEBUG and WP_DEBUG_LOG are enabled.
     *
     * @param string $message Log message.
     */
    function kombo_child_debug_log($message)
    {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($message);
        }
    }
}

/**
 * Jednolinijski trace za meal-plan AJAX. U wp-config.php: define('KOMBO_TRACE_MEAL_PLAN', true);
 *
 * @param string               $label   Kratak opis koraka.
 * @param array<string, mixed> $context Opcioni podaci (kratko).
 */
function kombo_trace_meal_plan($label, array $context = array())
{
    if (!defined('KOMBO_TRACE_MEAL_PLAN') || !KOMBO_TRACE_MEAL_PLAN) {
        return;
    }
    $line = '[kombo-meal-plan] ' . $label;
    if ($context) {
        $line .= ' ' . wp_json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    error_log($line);
}

/**
 * Detaljni trace (get_price / ajax_get_price pri svakom hover-u paketa). U wp-config:
 * define('KOMBO_TRACE_MEAL_PLAN_VERBOSE', true);
 */
function kombo_trace_meal_plan_verbose($label, array $context = array())
{
    if (!defined('KOMBO_TRACE_MEAL_PLAN_VERBOSE') || !KOMBO_TRACE_MEAL_PLAN_VERBOSE) {
        return;
    }
    kombo_trace_meal_plan($label, $context);
}

$kombo_child_inc = trailingslashit(get_stylesheet_directory()) . 'inc/';
require_once $kombo_child_inc . 'setup-assets.php';
require_once $kombo_child_inc . 'meal-plan.php';
require_once $kombo_child_inc . 'cpt-menus.php';
require_once $kombo_child_inc . 'polylang-strings.php';
require_once $kombo_child_inc . 'woocommerce.php';

/**
 * When the child theme Version (style.css) changes, clear WooCommerce’s system status
 * template scan cache. Otherwise WooCommerce can keep reporting stale @version strings
 * for up to an hour (transient wc_system_status_theme_info).
 *
 * Note: suffixes like @version 7.0.1+CUSTOM break PHP version_compare() and are treated
 * as older than 7.0.1 — overrides must use a plain semver line matching core.
 */
add_action(
    'after_setup_theme',
    static function () {
        if ( ! function_exists( 'WC' ) ) {
            return;
        }
        $theme_version = wp_get_theme( get_stylesheet() )->get( 'Version' );
        $last          = get_option( 'kombo_child_wc_status_theme_cache_version', '' );
        if ( $last !== $theme_version ) {
            delete_transient( 'wc_system_status_theme_info' );
            update_option( 'kombo_child_wc_status_theme_cache_version', $theme_version, false );
        }
    },
    20
);
