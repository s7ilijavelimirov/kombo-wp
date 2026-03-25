<?php

// Define constants for theme path, URL, version, and text domain
define("WPTHEME_PATH", get_template_directory());
define("WPTHEME_URL", get_template_directory_uri());
define("WPTHEME_VERSION", wp_get_theme()->get("Version"));
define("WPTHEME_TEXTDOMAIN", wp_get_theme()->get("TextDomain"));

// Include the Composer autoload file
require_once(WPTHEME_PATH . "/vendor/autoload.php");

// Initialize the main theme class
\WpTheme\Main::Init();

// Disable Gutenberg editor and revert to classic editor
function disable_gutenberg()
{
    // Disable Gutenberg editor for posts
    add_filter('use_block_editor_for_post', '__return_false', 10);

    // Disable Gutenberg editor for custom post types
    add_filter('use_block_editor_for_post_type', '__return_false', 10);
}
add_action('init', 'disable_gutenberg');

// Revert to classic widget editor
function disable_gutenberg_widget_editor()
{
    remove_theme_support('widgets-block-editor');
}
add_action('after_setup_theme', 'disable_gutenberg_widget_editor');





// Promena oznake valute u RSD
add_filter('woocommerce_currency_symbol', 'change_currency_symbol', 10, 2);
function change_currency_symbol($currency_symbol, $currency)
{
    switch ($currency) {
        case 'RSD':
            $currency_symbol = 'rsd';
            break;
    }
    return $currency_symbol;
}

// Postavljanje podrazumevane valute na RSD
add_filter('woocommerce_currency', 'set_default_currency');
function set_default_currency()
{
    return 'RSD';
}
// Add custom field to checkout fields array
add_filter('woocommerce_checkout_fields', 'custom_checkout_fields');

function custom_checkout_fields($fields)
{
    $fields['billing']['billing_pak'] = array(
        'type' => 'number',
        'label' => __('PAK', 'woocommerce'),
        'placeholder' => __('Unesite PAK', 'woocommerce'),
        'required' => false,
        'class' => array('form-row-wide'),
        'priority' => 95, // Position it right after the postcode
        'custom_attributes' => array(
            'maxlength' => 6, // Max length 6 characters
            'minlength' => 6, // Min length 6 characters
            'pattern' => '\d{6}', // Pattern to ensure exactly 6 digits
        ),
    );
    return $fields;
}

// Validate custom field
add_action('woocommerce_checkout_process', 'custom_checkout_field_process');

function custom_checkout_field_process()
{
    if (isset($_POST['billing_pak']) && !empty($_POST['billing_pak']) && !preg_match('/^\d{6}$/', $_POST['billing_pak'])) {
        wc_add_notice(__('PAK mora biti tačno šestocifreni broj.', 'woocommerce'), 'error');
    }
}

// Save custom field value in order meta data
add_action('woocommerce_checkout_update_order_meta', 'custom_checkout_field_update_order_meta');

function custom_checkout_field_update_order_meta($order_id)
{
    if (empty($_POST['billing_pak'])) {
        return;
    }
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }
    $order->update_meta_data('PAK', sanitize_text_field(wp_unslash($_POST['billing_pak'])));
    $order->save();
}

// Display custom field value in the order details in admin
add_action('woocommerce_admin_order_data_after_billing_address', 'custom_checkout_field_display_admin_order_meta', 10, 1);

function custom_checkout_field_display_admin_order_meta($order)
{
    if (!$order instanceof \WC_Order) {
        return;
    }
    $pak = $order->get_meta('PAK');
    echo '<p><strong>' . esc_html(__('PAK', 'woocommerce')) . ':</strong> ' . esc_html((string) $pak) . '</p>';
}

// Add custom field value to order emails
add_filter('woocommerce_email_order_meta_keys', 'custom_checkout_field_order_meta_keys');

function custom_checkout_field_order_meta_keys($keys)
{
    $keys[] = 'PAK';
    return $keys;
}

add_action('wp_enqueue_scripts', 'kombo_enqueue_checkout_billing_pak_script');
function kombo_enqueue_checkout_billing_pak_script()
{
    if (!function_exists('is_checkout') || !is_checkout()) {
        return;
    }
    $path = get_template_directory() . '/src/assets/scripts/checkout-billing-pak.js';
    wp_enqueue_script(
        'kombo-billing-pak',
        get_template_directory_uri() . '/src/assets/scripts/checkout-billing-pak.js',
        array('jquery'),
        (file_exists($path) && is_readable($path)) ? (string) filemtime($path) : WPTHEME_VERSION,
        true
    );
}
function generate_clamp($base_size)
{
    // Calculate the min, preferred, and max values for clamp
    $min_size = $base_size * 0.75; // Minimum size is 75% of the base size
    $preferred_size = $base_size; // Preferred size is the base size
    $max_size = $base_size * 1.25; // Maximum size is 125% of the base size

    // Return the clamp expression
    return "clamp({$min_size}px, {$preferred_size}vw, {$max_size}px)";
}
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // WooCommerce theme support
    function yourtheme_add_woocommerce_support()
    {
        add_theme_support(
            'woocommerce',
            array(
                'gallery_thumbnail_image_width' => 300,
            )
        );
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
    }
    add_action('after_setup_theme', 'yourtheme_add_woocommerce_support');

    // Ensure WooCommerce template overrides load from your theme
    function yourtheme_wc_template_part($template, $slug, $name)
    {
        $custom_template = locate_template(array(WC()->template_path() . "{$slug}-{$name}.php"));
        return $custom_template ? $custom_template : $template;
    }
    add_filter('wc_get_template_part', 'yourtheme_wc_template_part', 10, 3);

    // Set number of products per page
    function yourtheme_woocommerce_products_per_page($cols)
    {
        return 12;
    }
    add_filter('loop_shop_per_page', 'yourtheme_woocommerce_products_per_page', 20);

    // Set number of products per row
    function yourtheme_loop_columns()
    {
        return 4; // 4 products per row
    }
    add_filter('loop_shop_columns', 'yourtheme_loop_columns');

    // Add WooCommerce image dimensions
    function yourtheme_woocommerce_image_dimensions()
    {
        $catalog = array(
            'width' => '400',    // px
            'height' => '400',    // px
            'crop' => 1         // true
        );

        $single = array(
            'width' => '800',    // px
            'height' => '800',    // px
            'crop' => 1         // true
        );

        $thumbnail = array(
            'width' => '300',    // px
            'height' => '300',    // px
            'crop' => 1         // true
        );
        $gallery_image = array(
            'width' => '300',    // px
            'height' => '300',    // px
            'crop' => 0         // true
        );


        update_option('shop_catalog_image_size', $catalog);       // Product category thumbs
        update_option('shop_single_image_size', $single);         // Single product image
        update_option('shop_thumbnail_image_size', $thumbnail);   // Image gallery thumbs
        update_option('gallery_thumbnail_image_width', $gallery_image);
    }
    add_action('after_switch_theme', 'yourtheme_woocommerce_image_dimensions', 1);

    // Modify the breadcrumb
    function yourtheme_change_breadcrumb_delimiter($defaults)
    {
        $defaults['delimiter'] = ' &gt; ';
        return $defaults;
    }
    add_filter('woocommerce_breadcrumb_defaults', 'yourtheme_change_breadcrumb_delimiter');

    // Ensure WooCommerce pages have the correct sidebar
    function yourtheme_wc_sidebar()
    {
        if (is_woocommerce()) {
            get_sidebar('shop');
        }
    }
    add_action('get_sidebar', 'yourtheme_wc_sidebar');
}
function custom_woocommerce_before_shop_loop()
{
    echo '<div class="custom-shop-header">';
}

function custom_woocommerce_after_shop_loop()
{
    echo '</div>';
}

function custom_reorder_shop_elements()
{
    // Remove default hooks
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);

    // Add custom hooks
    add_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    add_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 25);
}

add_action('woocommerce_before_shop_loop', 'custom_woocommerce_before_shop_loop', 15);
add_action('woocommerce_before_shop_loop', 'custom_woocommerce_after_shop_loop', 35);
add_action('woocommerce_init', 'custom_reorder_shop_elements');

function theme_custom_logo_setup()
{
    add_theme_support('custom-logo', array(
        'height' => 100,
        'width' => 400,
        'flex-width' => true,
        'flex-height' => true,
    ));
}
add_action('after_setup_theme', 'theme_custom_logo_setup');


function register_footer_widgets()
{
    register_sidebar(array(
        'name' => 'Footer logo ',
        'id' => 'footer_logo',
        'before_widget' => '<div class="footer-widget-logo">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => 'Footer navigation',
        'id' => 'footer_nav',
        'before_widget' => '<div class="footer-widget-navigation">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => 'Footer social network',
        'id' => 'footer_social_bar',
        'before_widget' => '<div class="footer-widget-social-wrapper">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => 'Language Switcher Sidebar',
        'id' => 'language_switcher_bar',
        'before_widget' => '<div class="footer-widget-social-wrapper">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => 'Address Sidebar',
        'id' => 'address_bar',
        'before_widget' => '<div class="footer-widget-social-wrapper">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => 'Copyright Sidebar',
        'id' => 'copyright_bar',
        'before_widget' => '<div class="footer-widget-social-wrapper">',
        'after_widget' => '</div>',
        'before_title' => '<h4>',
        'after_title' => '</h4>',
    ));
}
add_action('widgets_init', 'register_footer_widgets');

function enqueue_cart_update_script()
{
    if (!function_exists('WC')) {
        return;
    }
    $rel = '/src/assets/scripts/update-cart-count.js';
    $path = get_template_directory() . $rel;
    wp_enqueue_script(
        'update-cart-count',
        get_template_directory_uri() . $rel,
        array('jquery'),
        (file_exists($path) && is_readable($path)) ? (string) filemtime($path) : WPTHEME_VERSION,
        true
    );
    wp_localize_script('update-cart-count', 'cartCountAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('get_cart_count'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_cart_update_script');

function get_cart_count()
{
    check_ajax_referer('get_cart_count', 'nonce');
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'Cart unavailable'));
    }
    wp_send_json_success(WC()->cart->get_cart_contents_count());
}
add_action('wp_ajax_get_cart_count', 'get_cart_count');
add_action('wp_ajax_nopriv_get_cart_count', 'get_cart_count');
