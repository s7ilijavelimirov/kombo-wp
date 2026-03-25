<?php



function custom_remove_checkout_fields($fields)
{
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_pak']);
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'custom_remove_checkout_fields');

function custom_make_all_checkout_fields_required($fields)
{
    // Prolazi kroz sve sekcije (billing, shipping, account)
    foreach ($fields as $section => $section_fields) {
        foreach ($section_fields as $field_key => $field) {
            // Postavi sva polja kao obavezna
            $fields[$section][$field_key]['required'] = true;
        }
    }
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'custom_make_all_checkout_fields_required');


function custom_change_checkout_placeholders($fields)
{

    $fields['billing']['billing_address_2']['placeholder'] = 'Apartman, stan, jedinica *'; // Tvoj novi tekst placeholder-a



    return $fields;
}
add_filter('woocommerce_checkout_fields', 'custom_change_checkout_placeholders');
function custom_remove_shipping_fields($fields)
{
    unset($fields['shipping']);
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'custom_remove_shipping_fields');
function custom_add_order_note_field($fields)
{
    // Dodavanje polja za napomenu o narudžbini u checkout formu
    $fields['order']['order_comments'] = array(
        'type'        => 'textarea',
        'label'       => pll__('Napomena o narudžbini'),
        'placeholder' => pll__('Dodajte napomenu za vašu narudžbinu (opciono)'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'clear'       => true,
    );

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'custom_add_order_note_field');

function handle_update_cart_delivery_date()
{
    check_ajax_referer('woocommerce-cart', 'security');

    $cart_item_key = sanitize_text_field(wp_unslash($_POST['cart_item_key']));
    $new_dates = isset($_POST['new_date']) ? wp_unslash($_POST['new_date']) : array();
    if (!is_array($new_dates)) {
        $new_dates = $new_dates !== '' && $new_dates !== null ? array($new_dates) : array();
    }
    $new_dates = array_map('sanitize_text_field', $new_dates);

    if (!isset(WC()->cart->cart_contents[$cart_item_key])) {
        wp_send_json_error(array('message' => 'Cart item not found'));
        return;
    }

    try {
        // Ažuriramo datume
        WC()->cart->cart_contents[$cart_item_key]['delivery_dates'] = $new_dates;

        // Ako je dnevni paket, ažuriramo cenu
        if (
            isset(WC()->cart->cart_contents[$cart_item_key]['package_type']) &&
            WC()->cart->cart_contents[$cart_item_key]['package_type'] === 'dnevni'
        ) {

            $base_price = get_package_price(
                WC()->cart->cart_contents[$cart_item_key]['gender'],
                WC()->cart->cart_contents[$cart_item_key]['calories'],
                'dnevni'
            );

            $days_count = is_array($new_dates) ? count($new_dates) : 1;
            $new_price = $base_price * $days_count;

            WC()->cart->cart_contents[$cart_item_key]['data']->set_price($new_price);
            WC()->cart->cart_contents[$cart_item_key]['_price'] = $new_price;
        }

        // Preračunamo totale
        WC()->cart->calculate_totals();

        // Sačuvamo u sesiju
        WC()->cart->set_session();

        // Vraćamo signal da treba osvežiti stranicu
        wp_send_json_success(array(
            'refresh' => true
        ));
    } catch (Exception $e) {
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}

add_action('wp_ajax_update_cart_delivery_date', 'handle_update_cart_delivery_date');
add_action('wp_ajax_nopriv_update_cart_delivery_date', 'handle_update_cart_delivery_date');
add_filter('woocommerce_package_rates', 'set_zero_shipping', 100);
function set_zero_shipping($rates)
{
    if ($rates) {
        foreach ($rates as $rate) {
            $rate->cost = 0;
        }
    }
    return $rates;
}
add_filter('woocommerce_update_cart_action_cart_updated', 'maintain_cart_prices', 10, 1);
function maintain_cart_prices($cart_updated)
{
    if ($cart_updated) {
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['delivery_dates']) && strpos(strtolower($cart_item['data']->get_name()), 'dnevni') !== false) {
                $base_price = $cart_item['data']->get_regular_price();
                $days_count = is_array($cart_item['delivery_dates']) ? count($cart_item['delivery_dates']) : 1;
                $cart_item['data']->set_price($base_price * $days_count);
            }
        }
    }
    return $cart_updated;
}

// Čuvanje datuma prilikom ažuriranja korpe
function preserve_cart_delivery_dates($cart_item_data, $cart_item_key)
{
    if (isset(WC()->cart->cart_contents[$cart_item_key]['delivery_dates'])) {
        $cart_item_data['delivery_dates'] = WC()->cart->cart_contents[$cart_item_key]['delivery_dates'];
    }
    return $cart_item_data;
}
add_filter('woocommerce_update_cart_item_data', 'preserve_cart_delivery_dates', 10, 2);


add_action('woocommerce_checkout_create_order_line_item', 'add_cart_item_data_to_order_item', 10, 4);
function add_cart_item_data_to_order_item($item, $cart_item_key, $values, $order)
{
    if (isset($values['delivery_dates'])) {
        $item->add_meta_data('delivery_dates', $values['delivery_dates']);

        // Ako je dnevni paket, postavljamo pravilnu cenu
        if (strpos(strtolower($values['data']->get_name()), 'dnevni') !== false) {
            $days_count = is_array($values['delivery_dates']) ? count($values['delivery_dates']) : 1;
            $base_price = $values['data']->get_price();
            $item->set_total($base_price * $days_count * $values['quantity']);
            $item->set_subtotal($base_price * $days_count * $values['quantity']);
        }
    }
}


add_action('wp_ajax_update_cart_action', 'handle_cart_update_action');
add_action('wp_ajax_nopriv_update_cart_action', 'handle_cart_update_action');


// Modifikujte postojeću funkciju
function handle_cart_update_action()
{
    if (!check_ajax_referer('woocommerce-cart', 'security', false)) {
        wp_die('Invalid nonce');
    }

    if (!empty($_POST['cart'])) {
        foreach ($_POST['cart'] as $cart_item_key => $values) {
            if (isset($values['qty'])) {
                $quantity = absint($values['qty']);
                $cart_item = WC()->cart->get_cart_item($cart_item_key);

                if ($cart_item) {
                    // Postavimo količinu
                    WC()->cart->set_quantity($cart_item_key, $quantity);

                    // Ažuriramo cenu proizvoda
                    $product = $cart_item['data'];
                    $single_price = $product->get_price();

                    // Ako je dnevni paket, računamo po danima
                    if (isset($cart_item['package_type']) && $cart_item['package_type'] === 'dnevni') {
                        $base_price = get_package_price(
                            $cart_item['gender'],
                            $cart_item['calories'],
                            'dnevni'
                        );

                        $days_count = isset($cart_item['delivery_dates']) && is_array($cart_item['delivery_dates'])
                            ? count($cart_item['delivery_dates'])
                            : 1;

                        $single_price = $base_price * $days_count;
                    }

                    // Postavljamo novu cenu
                    $product->set_price($single_price);
                    WC()->cart->cart_contents[$cart_item_key]['data']->set_price($single_price);
                }
            }
        }
    }

    // Preračunavamo totale
    WC()->cart->calculate_totals();

    // Osvežavamo prikaz cena
    $updated_cart = WC()->cart->get_cart();
    $response = array();

    foreach ($updated_cart as $cart_item_key => $cart_item) {
        $response['fragments']['.product-wrapper[data-cart-key="' . $cart_item_key . '"] .product-price'] =
            '<div class="product-price">' .
            apply_filters(
                'woocommerce_cart_item_price',
                WC()->cart->get_product_price($cart_item['data']),
                $cart_item,
                $cart_item_key
            ) .
            '</div>';
    }

    wp_send_json_success($response);
}

// Osiguravamo da se cena pravilno ažurira i nakon standardnog WooCommerce ažuriranja

add_action('woocommerce_before_calculate_totals', 'update_cart_prices', 999);
function update_cart_prices($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    static $is_running = false;
    if ($is_running) return;
    $is_running = true;

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['delivery_dates']) && strpos(strtolower($cart_item['data']->get_name()), 'dnevni') !== false) {
            $base_price = get_package_price(
                $cart_item['gender'],
                $cart_item['calories'],
                'dnevni'
            );
            $days_count = is_array($cart_item['delivery_dates']) ? count($cart_item['delivery_dates']) : 1;
            $new_price = $base_price * $days_count;

            // Postavljamo novu cenu za proizvod
            $cart_item['data']->set_price($new_price);
            $cart_item['data']->set_regular_price($new_price);

            // Ažuriramo i prikaz cene
            WC()->cart->cart_contents[$cart_item_key]['data']->set_price($new_price);
        }
    }

    $is_running = false;
}
add_filter('woocommerce_cart_item_price', 'kombo_child_cart_item_price_html', 100, 3);
/**
 * Jedan filter umesto dva @ priority 100 (izbegava nepredvidiv redosled).
 */
function kombo_child_cart_item_price_html($price_html, $cart_item, $cart_item_key)
{
    $quantity = $cart_item['quantity'];
    if (isset($cart_item['delivery_dates']) && strpos(strtolower($cart_item['data']->get_name()), 'dnevni') !== false) {
        $base_price = get_package_price(
            $cart_item['gender'],
            $cart_item['calories'],
            'dnevni'
        );
        $days_count = is_array($cart_item['delivery_dates']) ? count($cart_item['delivery_dates']) : 1;
        return wc_price($base_price * $days_count * $quantity);
    }
    $product_price = $cart_item['data']->get_price();
    return wc_price($product_price * $quantity);
}



add_filter('woocommerce_add_cart_item', 'add_custom_price_to_cart_item', 10, 2);
function add_custom_price_to_cart_item($cart_item, $cart_item_key)
{
    if (isset($cart_item['delivery_dates']) && strpos(strtolower($cart_item['data']->get_name()), 'dnevni') !== false) {
        $base_price = floatval($cart_item['data']->get_regular_price());
        $days_count = is_array($cart_item['delivery_dates']) ? count($cart_item['delivery_dates']) : 1;
        $cart_item['data']->set_price($base_price * $days_count);
    }
    return $cart_item;
}


add_action('woocommerce_after_cart_item_quantity_update', 'force_cart_update', 10, 4);
function force_cart_update($cart_item_key, $quantity, $old_quantity, $cart)
{
    WC()->cart->calculate_totals();
}


function enqueue_cart_script()
{
    if (!is_cart()) {
        return;
    }

    // Same base as poručivanje — parent templates/_calendar.scss expects jQuery UI structure.
    if (function_exists('kombo_child_enqueue_jquery_ui_datepicker_base_css')) {
        kombo_child_enqueue_jquery_ui_datepicker_base_css();
    }

    $cart_js_path = get_stylesheet_directory() . '/assets/js/cart.js';
    if (!file_exists($cart_js_path) || !is_readable($cart_js_path)) {
        return;
    }

    wp_enqueue_script(
        'cart-script',
        get_stylesheet_directory_uri() . '/assets/js/cart.js',
        array('jquery', 'jquery-ui-datepicker'),
        (string) filemtime($cart_js_path),
        true
    );

    wp_localize_script(
        'cart-script',
        'komboCartI18n',
        array(
            'noDate' => pll__('Nije odabran datum'),
        )
    );
}
add_action('wp_enqueue_scripts', 'enqueue_cart_script');





add_filter('woocommerce_shipping_rate_label', 'custom_shipping_rate_label');
function custom_shipping_rate_label($label)
{
    return pll__('Dostava kurirskom službom');
}


add_filter('woocommerce_gateway_title', 'custom_payment_gateway_title', 10, 2);
function custom_payment_gateway_title($title, $id)
{
    if ($id === 'cod') { // cod je ID za plaćanje pouzećem
        return pll__('Plaćanje pouzećem');
    }
    if ($id === 'nestpay') { // nestpay ID za plaćanje karticom
        return pll__('Plaćanje platnom karticom');
    }
    return $title;
}


/**
 * Permalink za WooCommerce stranicu (cart / checkout) na datom Polylang jeziku.
 *
 * U WP adminu postoji jedna „Checkout“ stranica (shortcode); prevodi u Polylangu su npr. EN Payment,
 * SR Plaćanje, RU Оплата — različiti slugovi, ista uloga. pll_get_post() vraća tačan permalink za jezik.
 *
 * @param string $page 'cart'|'checkout'.
 * @param string $lang Polylang kod jezika (npr. en, ru, sr).
 */
function kombo_child_permalink_wc_page_for_language($page, $lang)
{
    if (!function_exists('pll_get_post') || !function_exists('WC')) {
        return '';
    }
    $page_id = wc_get_page_id($page);
    if ($page_id <= 0) {
        return '';
    }
    $translated_id = pll_get_post($page_id, $lang);
    $use_id = $translated_id ? $translated_id : $page_id;

    return get_permalink($use_id);
}

// URL filteri za korpu i checkout (Polylang prevodi stranica)
add_filter('xoo_wsc_cart_page_url', 'custom_cart_url');
add_filter('xoo_wsc_checkout_page_url', 'custom_checkout_url');
add_filter('woocommerce_get_cart_url', 'custom_cart_url');
add_filter('woocommerce_get_checkout_url', 'custom_checkout_url');

function custom_cart_url($url)
{
    if (!function_exists('pll_current_language')) {
        return $url;
    }
    $permalink = kombo_child_permalink_wc_page_for_language('cart', pll_current_language());
    return $permalink ? $permalink : $url;
}

function custom_checkout_url($url)
{
    if (!function_exists('pll_current_language')) {
        return $url;
    }
    $permalink = kombo_child_permalink_wc_page_for_language('checkout', pll_current_language());
    return $permalink ? $permalink : $url;
}

add_filter('woocommerce_get_endpoint_url', 'kombo_child_wc_endpoint_url', 10, 4);
/**
 * Jedan filter: ispravlja %%endpoint%% artefakte + opcioni ?lang= za Polylang.
 */
function kombo_child_wc_endpoint_url($url, $endpoint = '', $value = '', $permalink = '')
{
    if (strpos($url, '%25%25endpoint%25%25') !== false) {
        $url = str_replace('%25%25endpoint%25%25', $endpoint, $url);
    }
    if (function_exists('pll_current_language')) {
        $current_lang = pll_current_language();
        if (!empty($current_lang)) {
            $url = add_query_arg('lang', $current_lang, $url);
        }
    }
    return $url;
}
// 2. Dodajemo jezik u WooCommerce AJAX zahteve
add_filter('woocommerce_ajax_get_endpoint', 'fix_ajax_endpoint', 10, 2);
function fix_ajax_endpoint($url, $endpoint)
{
    if (strpos($url, '%25%25endpoint%25%25') !== false) {
        $url = str_replace('%25%25endpoint%25%25', $endpoint, $url);
    }

    // Ne dodajemo lang u wc-ajax URL (duplo sa drugim filterima / JS → 403 na nekim serverima).
    // Jezik na checkoutu: polje lang u formi + set_checkout_language_before_fragments iz post_data.

    return $url;
}

add_action('woocommerce_checkout_order_processed', 'force_reload_after_checkout');
function force_reload_after_checkout()
{
    if (function_exists('pll_current_language')) {
        $lang = pll_current_language();
        WC()->session->set('reload_checkout', true);
        WC()->session->set('checkout_language', $lang);
    }
}

add_filter('woocommerce_get_item_data', 'translate_checkout_variations', 10, 2);
function translate_checkout_variations($item_data, $cart_item)
{
    if (!is_checkout() || !function_exists('pll_current_language')) {
        return $item_data;
    }

    foreach ($item_data as $key => $data) {
        // Prevodi za labels
        switch ($data['key']) {
            case 'Plan':
                $item_data[$key]['key'] = pll__('Plan');
                break;
            case 'Kalorije':
            case 'Calories':
                $item_data[$key]['key'] = pll__('Kalorije');
                break;
            case 'Izaberi paket':
            case 'Choose a package':
                $item_data[$key]['key'] = pll__('Izaberi paket');
                break;
            case 'Datum dostave':
            case 'Delivery date':
                $item_data[$key]['key'] = pll__('Datum dostave');
                break;
        }

        // Prevodi za vrednosti paketa
        if (isset($data['value'])) {
            switch (trim($data['value'])) {
                case 'Nedeljni 5':
                    $item_data[$key]['value'] = pll__('Nedeljni 5');
                    break;
                case 'Nedeljni 6':
                    $item_data[$key]['value'] = pll__('Nedeljni 6');
                    break;
                case 'Mesečni 20':
                    $item_data[$key]['value'] = pll__('Mesecni 20');
                    break;
                case 'Mesečni 24':
                    $item_data[$key]['value'] = pll__('Mesecni 24');
                    break;
                case 'Slim':
                    $item_data[$key]['value'] = pll__('Slim');
                    break;
                case 'Fit':
                    $item_data[$key]['value'] = pll__('Fit');
                    break;
                case 'Protein':
                case 'Protein plus':
                    $item_data[$key]['value'] = pll__('Protein');
                    break;
            }
        }
    }

    return $item_data;
}

add_filter('woocommerce_get_order_item_totals', 'translate_order_item_labels', 10, 3);
function translate_order_item_labels($total_rows, $order, $tax_display)
{
    if (function_exists('pll_current_language')) {
        $lang = isset($_POST['lang']) ? $_POST['lang'] : pll_current_language();

        if ($lang !== 'sr') {
            $translations = array(
                'Ukupno' => pll__('Total'),
                'Dostava' => pll__('Delivery'),
                'Međuzbir' => pll__('Subtotal')
            );

            foreach ($total_rows as $key => &$row) {
                if (isset($translations[$row['label']])) {
                    $row['label'] = $translations[$row['label']];
                }
            }
        }
    }
    return $total_rows;
}
add_action('wp_enqueue_scripts', 'add_language_to_checkout_js', 100);
function add_language_to_checkout_js()
{
    if (!is_checkout() || !function_exists('pll_current_language')) return;

    wp_add_inline_script('wc-checkout', '
        jQuery(function($) {
            // Osvežavamo prevode nakon svakog AJAX poziva
            $(document.body).on("updated_checkout", function() {
                var lang = $("input[name=\'lang\']").val();
                if (lang !== "sr") {
                    $(".cart-subtotal th").text("' . esc_js(pll__('Subtotal')) . '");
                    $(".order-total th").text("' . esc_js(pll__('Total')) . '");
                    $(".woocommerce-shipping-totals th").text("' . esc_js(pll__('Delivery')) . '");
                    $("#place_order").text("' . esc_js(pll__('Order')) . '");
                }
            });
        });
    ', 'after');
}
add_action('woocommerce_checkout_update_order_review', 'set_checkout_language');
function set_checkout_language()
{
    if (empty($_POST['lang']) || !function_exists('PLL')) {
        return;
    }
    $slug = sanitize_key(wp_unslash($_POST['lang']));
    $lang = PLL()->model->get_language($slug);
    if ($lang) {
        PLL()->curlang = $lang;
    }
}
add_filter('woocommerce_get_script_data', 'fix_checkout_script_data', 10, 2);
function fix_checkout_script_data($data, $handle)
{
    if ($handle === 'wc-checkout') {
        // Ne modifikujemo ajax_url query (izbegavamo konflikte); wc_ajax_url preko filtera ispod.
        $data['wc_ajax_url'] = WC_AJAX::get_endpoint('%%endpoint%%');
    }
    return $data;
}
add_action('woocommerce_checkout_order_review', 'kombo_checkout_notices_anchor', 5);
function kombo_checkout_notices_anchor()
{
    echo '<div id="kombo-checkout-notices-anchor" class="kombo-checkout-notices-anchor" aria-live="polite"></div>';
}

add_action('wp_enqueue_scripts', 'modify_checkout_scripts', 99);
function modify_checkout_scripts()
{
    if (!is_checkout()) return;

    wp_dequeue_script('wc-cart-fragments');

    wp_add_inline_style(
        'woocommerce-general',
        '.kombo-checkout-notices-anchor{margin:0 0 16px}.kombo-checkout-notices-anchor .woocommerce-message,.kombo-checkout-notices-anchor .woocommerce-error,.kombo-checkout-notices-anchor .woocommerce-info{margin:0 0 10px}'
    );

    wp_add_inline_script('wc-checkout', '
        jQuery(function($) {
            function komboRelocateCheckoutNotices() {
                var form = document.querySelector("form.checkout.woocommerce-checkout");
                var anchor = document.getElementById("kombo-checkout-notices-anchor");
                if (!form || !anchor) {
                    return;
                }
                var toMove = [];
                var p = form.previousElementSibling;
                while (p) {
                    var cls = (p.className && String(p.className)) || "";
                    var isNotice = /woocommerce-(message|error|info)|woocommerce-notices-wrapper|NoticeGroup-checkout/i.test(cls)
                        || (p.querySelector && p.querySelector(".woocommerce-message, .woocommerce-error, .woocommerce-info"));
                    if (!isNotice) {
                        break;
                    }
                    toMove.push(p);
                    p = p.previousElementSibling;
                }
                for (var i = toMove.length - 1; i >= 0; i--) {
                    anchor.appendChild(toMove[i]);
                }
            }

            function updateTranslations() {
                $(".woocommerce-shipping-totals th").text("' . esc_js(pll__('Dostava')) . '");
                $(".cart-subtotal th").text("' . esc_js(pll__('Međuzbir')) . '");
                $(".order-total th").text("' . esc_js(pll__('Ukupno')) . '");
                $("#place_order").text("' . esc_js(pll__('Naruči')) . '");
            }

            $(document.body).on("updated_checkout", function() {
                updateTranslations();
                komboRelocateCheckoutNotices();
            });
            $(document.body).on("applied_coupon_in_checkout removed_coupon_in_checkout", function() {
                setTimeout(komboRelocateCheckoutNotices, 0);
            });
            updateTranslations();
            komboRelocateCheckoutNotices();
        });
    ');
}
add_action('woocommerce_checkout_update_order_review', 'set_checkout_language_before_fragments', 1);
function set_checkout_language_before_fragments($post_data)
{
    parse_str($post_data, $data);
    if (empty($data['lang']) || !function_exists('PLL')) {
        return;
    }
    $slug = sanitize_key($data['lang']);
    $lang = PLL()->model->get_language($slug);
    if ($lang) {
        PLL()->curlang = $lang;
    }
}

add_filter('woocommerce_get_item_data', 'translate_delivery_date', 20, 2);
function translate_delivery_date($item_data, $cart_item)
{
    if (is_checkout()) {
        foreach ($item_data as $key => $data) {
            if ($data['key'] === 'Datum dostave' || $data['key'] === 'Delivery date') {
                $item_data[$key]['key'] = pll__('Datum dostave');
            }
        }
    }
    return $item_data;
}


add_action('wp_head', 'add_checkout_variation_styles');
function add_checkout_variation_styles()
{
    if (!is_checkout()) return;
?>
    <style>
        .woocommerce-checkout-review-order-table .variation dt:after {
            content: ":";
            margin-left: 1px;
        }
    </style>
<?php
}

add_filter('woocommerce_update_order_review_fragments', 'customize_payment_method_description', 999);
function customize_payment_method_description($fragments)
{
    if (isset($fragments['.woocommerce-checkout-payment'])) {

        $fragments['.woocommerce-checkout-payment'] = preg_replace(
            '/<p>Plaćanje je moguće samo pouzećem\.<\/p>/',
            '',
            $fragments['.woocommerce-checkout-payment']
        );
    }
    return $fragments;
}
function add_price_info_after_order_total()
{
    echo '<tr class="price-info">

            <td colspan="2">
                <p class="price-notice">' . pll__('Sve cene su sa uračunatim PDV-om i nema skrivenih troškova.') . '</p>
            </td>
           
          </tr>';
}
add_action('woocommerce_review_order_after_order_total', 'add_price_info_after_order_total');




// Dodaj i CSS
function add_price_info_styles()
{
?>
    <style>
        .price-info td {
            padding-top: 10px !important;
        }

        .price-notice {
            font-size: 14px;
            color: #666;
            margin: 0;
            text-align: start;
        }
    </style>
<?php
}
add_action('wp_head', 'add_price_info_styles');


// Modifikacija linka za politiku privatnosti u checkout-u
add_filter('woocommerce_get_terms_and_conditions_checkbox_text', 'custom_terms_and_conditions_checkbox_text', 10);

function custom_terms_and_conditions_checkbox_text($text)
{
    if (!function_exists('pll_get_post') || !function_exists('pll_current_language')) {
        return $text;
    }

    $current_lang = pll_current_language();

    $privacy_page_id = pll_get_post((int) get_option('wp_page_for_privacy_policy'), $current_lang);
    $privacy_permalink = ($privacy_page_id && get_post_status($privacy_page_id)) ? get_permalink($privacy_page_id) : '';

    $urls = array(
        'sr' => '/politika-privatnosti',
        'en' => '/en/privacy-policy',
        'ru' => '/ru/политика-конфиденциальности',
    );

    $path = isset($urls[$current_lang]) ? $urls[$current_lang] : $urls['sr'];
    $link_href = $privacy_permalink ? $privacy_permalink : home_url($path);
    $link_href = esc_url($link_href);

    $texts = array(
        'sr' => 'Pročitao/la sam i slažem se sa <a href="' . $link_href . '" target="_blank" rel="noopener noreferrer">uslovima korišćenja</a>',
        'en' => 'I have read and agree to the <a href="' . $link_href . '" target="_blank" rel="noopener noreferrer">terms of use</a>',
        'ru' => 'Я прочитал и согласен с <a href="' . $link_href . '" target="_blank" rel="noopener noreferrer">условия эксплуатации</a>',
    );

    return isset($texts[$current_lang]) ? $texts[$current_lang] : $texts['sr'];
}



function copy_billing_to_shipping_on_order_creation($order_id)
{

    $order = wc_get_order($order_id);


    $shipping_address = $order->get_address('shipping');
    $has_shipping = false;


    if (!empty($shipping_address['first_name']) || !empty($shipping_address['last_name']) || !empty($shipping_address['address_1'])) {
        $has_shipping = true;
    }


    if (!$has_shipping) {

        $billing_address = $order->get_address('billing');


        $order->set_shipping_first_name($billing_address['first_name']);
        $order->set_shipping_last_name($billing_address['last_name']);
        $order->set_shipping_company($billing_address['company']);
        $order->set_shipping_address_1($billing_address['address_1']);
        $order->set_shipping_address_2($billing_address['address_2']);
        $order->set_shipping_city($billing_address['city']);
        $order->set_shipping_state($billing_address['state']);
        $order->set_shipping_postcode($billing_address['postcode']);
        $order->set_shipping_country($billing_address['country']);


        $order->save();
    }
}


add_action('woocommerce_checkout_order_created', 'copy_billing_to_shipping_on_order_creation', 10, 1);




/**
 * Checkout kupon UI + prevodi — izdvojeno u modul (čitljiviji `woocommerce.php`).
 * Primena: ugrađeni Woo **`wc-ajax`** `apply_coupon` + **`apply_coupon_nonce`** (nema custom PHP handlera).
 */
require_once __DIR__ . '/woocommerce/checkout-coupon.php';