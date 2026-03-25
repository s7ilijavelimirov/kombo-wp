<?php

add_action('wp_enqueue_scripts', 'kombo_child_enqueue_styles');
function kombo_child_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style'),
        wp_get_theme()->get('Version')
    );
}


/**
 * Registers theme navigation menus.
 */
function register_my_menus()
{
    register_nav_menus(
        array(
            'main-menu-srb' => __('Main Menu'),
        )
    );
}
add_action('init', 'register_my_menus');
function enqueue_slick_slider()
{
    if (!is_front_page()) {
        return;
    }

    $slick_dir = trailingslashit(get_stylesheet_directory()) . 'assets/vendor/slick-carousel/1.8.1/slick';
    $slick_uri = trailingslashit(get_stylesheet_directory_uri()) . 'assets/vendor/slick-carousel/1.8.1/slick';

    $slick_js = $slick_dir . '/slick.min.js';
    $slick_css = $slick_dir . '/slick.css';
    $slick_theme_css = $slick_dir . '/slick-theme.css';

    if (!is_readable($slick_js) || !is_readable($slick_css) || !is_readable($slick_theme_css)) {
        return;
    }

    $ver_js = (file_exists($slick_js) && is_readable($slick_js)) ? (string) filemtime($slick_js) : '1.8.1';
    $ver_css = (file_exists($slick_css) && is_readable($slick_css)) ? (string) filemtime($slick_css) : '1.8.1';
    $ver_theme = (file_exists($slick_theme_css) && is_readable($slick_theme_css)) ? (string) filemtime($slick_theme_css) : '1.8.1';

    wp_enqueue_style('slick', $slick_uri . '/slick.css', array(), $ver_css);
    wp_enqueue_style('slick-theme', $slick_uri . '/slick-theme.css', array('slick'), $ver_theme);
    wp_enqueue_script('slick', $slick_uri . '/slick.min.js', array('jquery'), $ver_js, true);
}
add_action('wp_enqueue_scripts', 'enqueue_slick_slider');

/**
 * Replaces former inline wp_footer / checkout scripts with enqueued assets + localization.
 */
add_action('wp_enqueue_scripts', 'kombo_child_enqueue_context_scripts', 99);
function kombo_child_enqueue_context_scripts()
{
    if (is_admin()) {
        return;
    }

    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();
    $ver = static function ($file) use ($dir) {
        $path = $dir . '/assets/js/' . $file;
        return (file_exists($path) && is_readable($path)) ? (string) filemtime($path) : '1.0';
    };

    wp_register_script(
        'kombo-cart-widget-urls',
        $uri . '/assets/js/cart-widget-urls.js',
        array('jquery'),
        $ver('cart-widget-urls.js'),
        true
    );
    wp_localize_script(
        'kombo-cart-widget-urls',
        'komboCartUrls',
        array(
            'en' => array(
                'cart' => function_exists('kombo_child_permalink_wc_page_for_language')
                    ? kombo_child_permalink_wc_page_for_language('cart', 'en')
                    : site_url('/en/cart/'),
                'checkout' => function_exists('kombo_child_permalink_wc_page_for_language')
                    ? kombo_child_permalink_wc_page_for_language('checkout', 'en')
                    : site_url('/en/payment/'),
            ),
            'ru' => array(
                'cart' => function_exists('kombo_child_permalink_wc_page_for_language')
                    ? kombo_child_permalink_wc_page_for_language('cart', 'ru')
                    : site_url('/ru/cart/'),
                'checkout' => function_exists('kombo_child_permalink_wc_page_for_language')
                    ? kombo_child_permalink_wc_page_for_language('checkout', 'ru')
                    : site_url('/оплата/'),
            ),
            'sr' => array(
                'cart' => function_exists('kombo_child_permalink_wc_page_for_language')
                    ? kombo_child_permalink_wc_page_for_language('cart', 'sr')
                    : site_url('/korpa/'),
                'checkout' => function_exists('kombo_child_permalink_wc_page_for_language')
                    ? kombo_child_permalink_wc_page_for_language('checkout', 'sr')
                    : site_url('/placanje/'),
            ),
        )
    );
    wp_enqueue_script('kombo-cart-widget-urls');

    wp_register_script(
        'kombo-nestpay-order-details',
        $uri . '/assets/js/nestpay-order-details.js',
        array('jquery'),
        $ver('nestpay-order-details.js'),
        true
    );
    wp_enqueue_script('kombo-nestpay-order-details');

    if (function_exists('is_checkout') && is_checkout()) {
        wp_register_script(
            'kombo-checkout-disable-fragments',
            $uri . '/assets/js/checkout-disable-fragments.js',
            array('jquery'),
            $ver('checkout-disable-fragments.js'),
            true
        );
        wp_enqueue_script('kombo-checkout-disable-fragments');

        $checkout_deps = array('jquery');
        if (wp_script_is('wc-checkout', 'registered')) {
            $checkout_deps[] = 'wc-checkout';
        }
        wp_register_script(
            'kombo-checkout-i18n',
            $uri . '/assets/js/checkout-i18n.js',
            $checkout_deps,
            $ver('checkout-i18n.js'),
            true
        );
        if (function_exists('pll__')) {
            wp_localize_script(
                'kombo-checkout-i18n',
                'komboCheckoutI18n',
                array(
                    'shipping' => pll__('Dostava'),
                    'total' => pll__('Ukupno'),
                    'subtotal' => pll__('Međuzbir'),
                    'place_order' => pll__('Naruči'),
                    'variations' => array(
                        'Plan ishrane' => pll__('Plan ishrane'),
                        'Plan:' => pll__('Plan'),
                        'Plan' => pll__('Plan'),
                        'Kalorije:' => pll__('Kalorije'),
                        'Kalorije' => pll__('Kalorije'),
                        'Izaberi paket:' => pll__('Izaberi paket'),
                        'Izaberi paket' => pll__('Izaberi paket'),
                        'Datum dostave:' => pll__('Datum dostave'),
                        'Nedeljni 6' => pll__('Nedeljni 6'),
                        'Nedeljni 5' => pll__('Nedeljni 5'),
                        'Mesečni 20' => pll__('Mesecni 20'),
                        'Mesečni 24' => pll__('Mesecni 24'),
                        'Dnevni' => pll__('Dnevni'),
                        'Slim' => pll__('Slim'),
                        'Fit' => pll__('Fit'),
                        'Protein plus' => pll__('Protein'),
                    ),
                    'checkoutTable' => array(
                        'Proizvod' => pll__('Product'),
                        'Ukupno' => pll__('Total'),
                        'Delivery' => pll__('Delivery'),
                        'Subtotal' => pll__('Subtotal'),
                        'Plaćanje pouzećem' => pll__('Plaćanje pouzećem'),
                        'Dostava kurirskom službom' => pll__('Dostava kurirskom službom'),
                        'Sve cene su sa uračunatim PDV-om i nema skrivenih troškova.' => pll__('Sve cene su sa uračunatim PDV-om i nema skrivenih troškova.'),
                        'Molimo Vas da pročitate i prihvatite uslove kako biste nastavili sa plasiranjem narudžbine.' => pll__('Molimo Vas da pročitate i prihvatite uslove kako biste nastavili sa plasiranjem narudžbine.'),
                    ),
                    'price_notice' => pll__('Sve cene su sa uračunatim PDV-om i nema skrivenih troškova.'),
                    'terms' => pll__('Molimo Vas da pročitate i prihvatite uslove kako biste nastavili sa plasiranjem narudžbine.'),
                )
            );
        } else {
            wp_localize_script('kombo-checkout-i18n', 'komboCheckoutI18n', array());
        }
        wp_enqueue_script('kombo-checkout-i18n');
    }

    if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
        wp_register_script(
            'kombo-order-received-i18n',
            $uri . '/assets/js/order-received-i18n.js',
            array('jquery'),
            $ver('order-received-i18n.js'),
            true
        );
        if (function_exists('pll__')) {
            wp_localize_script(
                'kombo-order-received-i18n',
                'komboOrderReceivedI18n',
                array(
                    'variations' => array(
                        'Plan ishrane' => pll__('Plan ishrane'),
                        'Plan' => pll__('Plan'),
                        'Kalorije' => pll__('Kalorije'),
                        'Izaberi paket' => pll__('Izaberi paket'),
                        'Datum dostave' => pll__('Datum dostave'),
                        'Nedeljni 6' => pll__('Nedeljni 6'),
                        'Nedeljni 5' => pll__('Nedeljni 5'),
                        'Mesečni 20' => pll__('Mesecni 20'),
                        'Mesečni 24' => pll__('Mesecni 24'),
                        'Dnevni' => pll__('Dnevni'),
                        'Slim' => pll__('Slim'),
                        'Fit' => pll__('Fit'),
                        'Protein plus' => pll__('Protein'),
                    ),
                )
            );
        } else {
            wp_localize_script('kombo-order-received-i18n', 'komboOrderReceivedI18n', array('variations' => array()));
        }
        wp_enqueue_script('kombo-order-received-i18n');
    }
}

add_action('wp_head', 'kombo_child_order_received_meta_styles', 99);
function kombo_child_order_received_meta_styles()
{
    if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-received')) {
        return;
    }
    echo '<style>.woocommerce-table__product-name .wc-item-meta strong:after { content: ":"; margin-left: 1px; }</style>';
}
