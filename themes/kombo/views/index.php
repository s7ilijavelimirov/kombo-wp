<?php
/**
 * Ultimate fallback template (maps from root `index.php` via Core::TemplateHierarchy).
 *
 * @package kombo
 */

defined('ABSPATH') || exit;

get_header();

if (have_posts()) {
    while (have_posts()) {
      
        the_post();
        the_content();
    }
} else {
    echo '<p>' . esc_html__('No content found', WPTHEME_TEXTDOMAIN) . '</p>';
}

get_sidebar();
get_footer();
