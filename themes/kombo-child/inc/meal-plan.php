<?php


function get_meal_plan_base_product()
{
    return 181;
}

/**
 * Polylang: za admin-ajax.php često nije postavljen isti jezik kao na stranici poručivanja.
 * Bez ovoga WC/Polylang mogu koristiti drugačiju korpu ili jezik → prazna korpa posle redirecta.
 *
 * @param string $slug Polylang kod jezika (npr. sr, en, ru).
 */
function kombo_meal_plan_ajax_set_polylang_language($slug)
{
    if (!function_exists('PLL') || $slug === '') {
        return;
    }
    $slug = sanitize_key($slug);
    if ($slug === '') {
        return;
    }
    $lang = PLL()->model->get_language($slug);
    if ($lang) {
        PLL()->curlang = $lang;
    }
}

function pll_ru($string, $russian = null)
{

    if ($russian && function_exists('pll_current_language') && pll_current_language() === 'ru') {
        return $russian;
    }

    return function_exists('pll__') ? pll__($string) : $string;
}

function kombo_child_needs_meal_plan_assets()
{
    if (is_page_template('views/porucivanje.php')) {
        return true;
    }
    if (!is_singular('page')) {
        return false;
    }
    $slug = get_post_field('post_name', get_queried_object_id());
    return in_array($slug, array('porucivanje', 'ordering', 'заказ'), true);
}

/**
 * jQuery UI Datepicker base stylesheet — required for calendar chrome; parent _calendar.scss builds on this.
 * Loaded on poručivanje (meal plan) and cart so the popup matches site-wide.
 */
function kombo_child_enqueue_jquery_ui_datepicker_base_css()
{
    $jquery_ui_ver      = '1.12.1';
    $jquery_ui_css_path = get_stylesheet_directory() . '/assets/vendor/jquery-ui/1.12.1/themes/base/jquery-ui.css';
    $jquery_ui_css_uri  = get_stylesheet_directory_uri() . '/assets/vendor/jquery-ui/1.12.1/themes/base/jquery-ui.css';

    if (file_exists($jquery_ui_css_path) && is_readable($jquery_ui_css_path)) {
        wp_enqueue_style(
            'jquery-ui-css',
            $jquery_ui_css_uri,
            array(),
            (string) filemtime($jquery_ui_css_path)
        );
    } else {
        wp_enqueue_style(
            'jquery-ui-css',
            'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
            array(),
            $jquery_ui_ver
        );
    }
}

function meal_plan_form_assets()
{
    if (!kombo_child_needs_meal_plan_assets()) {
        return;
    }
    wp_enqueue_script('jquery-ui-datepicker');
    kombo_child_enqueue_jquery_ui_datepicker_base_css();

    $meal_js = get_stylesheet_directory() . '/assets/js/meal-plan-form.js';
    $meal_js_ver = (file_exists($meal_js) && is_readable($meal_js)) ? (string) filemtime($meal_js) : wp_get_theme()->get('Version');
    wp_enqueue_script(
        'meal-plan-form-script',
        get_stylesheet_directory_uri() . '/assets/js/meal-plan-form.js',
        array('jquery', 'jquery-ui-datepicker'),
        $meal_js_ver,
        true
    );
    wp_localize_script(
        'meal-plan-form-script',
        'mealTranslations',
        array(
            'small' => pll__('Mali'),
            'large' => pll__('Veliki'),
            'kcal' => pll__('kcal'),
            'od' => pll__('od'),
            'do' => pll__('do'),
            'selectPlan' => pll__('Molimo izaberite plan'),
            'selectCalories' => pll__('Molimo izaberite kalorije'),
            'selectPackage' => pll__('Molimo izaberite paket'),
            'selectDate' => pll__('Molimo izaberite datum'),
            'calendar' => pll__('kalendar'),
            'addToCart' => pll__('Dodaj u korpu'),
            'addPackage' => pll__('+ Dodaj paket'),
            'buyNow' => pll__('Naruči odmah'),
            'fillAllFields' => pll__('Molimo popunite sva polja'),
            'processing' => pll__('Procesiranje...'),
            'addingToCart' => pll__('Dodavanje u korpu...'),
            'addingPackage' => pll__('Dodavanje paketa...'),
            'packageAdded' => pll__('Paket je dodat u korpu. Možete izabrati sledeći.'),
            'serverError' => pll__('Došlo je do greške pri komunikaciji sa serverom. Pokušajte ponovo.'),
            'months' => array(
                pll__('Januar'),
                pll__('Februar'),
                pll__('Mart'),
                pll__('April'),
                pll__('Maj'),
                pll__('Jun'),
                pll__('Jul'),
                pll__('Avgust'),
                pll__('Septembar'),
                pll__('Oktobar'),
                pll__('Novembar'),
                pll__('Decembar')
            )
        )
    );
    // Lokalizacija za AJAX
    wp_localize_script('meal-plan-form-script', 'meal_plan_vars', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('meal_plan_nonce'),
        'trace' => defined('KOMBO_TRACE_MEAL_PLAN') && KOMBO_TRACE_MEAL_PLAN,
        'pll_lang' => function_exists('pll_current_language') ? pll_current_language() : '',
    ));
}
add_action('wp_enqueue_scripts', 'meal_plan_form_assets');

add_action('wp_ajax_add_meal_plan_to_cart', 'handle_meal_plan_add_to_cart');
add_action('wp_ajax_nopriv_add_meal_plan_to_cart', 'handle_meal_plan_add_to_cart');

add_action('wp_ajax_get_meal_plan_price', 'get_meal_plan_price');
add_action('wp_ajax_nopriv_get_meal_plan_price', 'get_meal_plan_price');

/**
 * wc-cart-fragments: samo gde korpa/checkout/katalog ili meal-plan stranica trebaju osvežavanje mini-korpe.
 * (Izbegava globalno učitavanje na svim front stranicama — vidi audit PF-1.)
 * Filter: kombo_child_enqueue_cart_fragments — vrati true da forsiraš (npr. Xoo WSC na početnoj).
 */
function kombo_child_should_enqueue_cart_fragments()
{
    if (!function_exists('is_woocommerce')) {
        return false;
    }
    $need = is_cart() || is_checkout() || is_woocommerce();
    if (function_exists('is_account_page')) {
        $need = $need || is_account_page();
    }
    if (!$need && function_exists('kombo_child_needs_meal_plan_assets')) {
        $need = kombo_child_needs_meal_plan_assets();
    }
    return (bool) apply_filters('kombo_child_enqueue_cart_fragments', $need);
}

function add_cart_fragments_scripts()
{
    if (!kombo_child_should_enqueue_cart_fragments()) {
        return;
    }
    wp_enqueue_script('wc-cart-fragments');
}
add_action('wp_enqueue_scripts', 'add_cart_fragments_scripts');


function get_or_create_base_meal_plan_product()
{
    $base_product_slug = 'meal-plan-base';

    // Prvo proverimo da li osnovni proizvod postoji
    $existing_product = get_posts(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'name' => $base_product_slug
    ));

    if (!empty($existing_product)) {
        return $existing_product[0]->ID;
    }

    // Ako ne postoji, kreiramo jedan osnovni proizvod
    $product = new WC_Product_Simple();
    $product->set_name(pll__('Plan ishrane'));
    $product->set_slug($base_product_slug);
    $product->set_status('publish');
    $product->set_catalog_visibility('hidden');
    $product->set_virtual(true);

    return $product->save();
}

/**
 * Zajednička logika: čita POST, dodaje meal plan u korpu, čuva WC sesiju.
 * Koristi se i za admin-ajax (stari poziv) i za pravi HTTP POST sa stranice poručivanja
 * (WooCommerce kao na single product: forma → redirect; sesija ostaje ista).
 *
 * @param string $trace_source 'ajax' | 'form_post'
 * @return array{ok:bool,message?:string,buy_now?:bool,cart_url?:string,checkout_url?:string}
 */
function kombo_meal_plan_process_add_to_cart_internal($trace_source = 'ajax')
{
    if (!WC()->cart) {
        return array('ok' => false, 'message' => 'Korpa nije dostupna.');
    }

    $menu_type = sanitize_text_field(wp_unslash($_POST['menu_type'] ?? ''));
    $gender = sanitize_text_field(wp_unslash($_POST['gender'] ?? $_POST['selected_gender'] ?? ''));
    $calories = intval($_POST['calories'] ?? $_POST['selected_calories'] ?? 0);
    $package = sanitize_text_field(wp_unslash($_POST['package'] ?? $_POST['selected_package'] ?? ''));
    $dates_raw = isset($_POST['dates']) ? wp_unslash($_POST['dates']) : array();
    if (!is_array($dates_raw)) {
        $dates_raw = ($dates_raw !== '' && $dates_raw !== null) ? array($dates_raw) : array();
    }
    $dates = array_map('sanitize_text_field', $dates_raw);
    $days_count = ($package === 'dnevni' && is_array($dates)) ? count($dates) : 1;
    $buy_now_req = isset($_POST['buy_now']) ? wc_string_to_bool(wp_unslash($_POST['buy_now'])) : false;

    kombo_trace_meal_plan(
        'parsed',
        array(
            'source' => $trace_source,
            'menu_type' => $menu_type,
            'gender' => $gender,
            'calories' => $calories,
            'package' => $package,
            'days_count' => $days_count,
            'buy_now' => $buy_now_req,
        )
    );

    $product_id = get_meal_plan_base_product();
    if (!$product_id) {
        kombo_trace_meal_plan('no_product_id', array('source' => $trace_source));
        return array('ok' => false, 'message' => 'Proizvod nije pronađen');
    }

    $base_price = get_package_price($gender, $calories, $package);
    $final_price = ($package === 'dnevni') ? $base_price * $days_count : $base_price;
    kombo_trace_meal_plan('price', array('source' => $trace_source, 'base' => $base_price, 'final' => $final_price));

    $cart_item_data = array(
        'menu_type' => $menu_type,
        'gender' => $gender,
        'calories' => $calories,
        'delivery_dates' => $dates,
        'package_type' => $package,
        'unique_key' => md5(microtime() . wp_rand()),
        '_price' => $final_price,
    );

    $added = WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);
    kombo_trace_meal_plan('add_to_cart', array('source' => $trace_source, 'ok' => (bool) $added, 'product_id' => $product_id));

    if (!$added) {
        $notices = function_exists('wc_get_notices') ? wc_get_notices('error') : array();
        kombo_trace_meal_plan('add_failed', array('source' => $trace_source, 'notices' => $notices ? wp_list_pluck($notices, 'notice') : array()));
        if (function_exists('wc_clear_notices')) {
            wc_clear_notices();
        }
        return array('ok' => false, 'message' => 'Došlo je do greške prilikom dodavanja u korpu');
    }

    WC()->cart->calculate_totals();
    WC()->session->save_data();
    if (WC()->session && method_exists(WC()->session, 'set_customer_session_cookie')) {
        WC()->session->set_customer_session_cookie(true);
    }
    if (WC()->cart && method_exists(WC()->cart, 'maybe_set_cart_cookies')) {
        WC()->cart->maybe_set_cart_cookies();
    }

    $cart_url = wc_get_cart_url();
    $checkout_url = wc_get_checkout_url();
    kombo_trace_meal_plan(
        'before_redirect_or_json',
        array(
            'source' => $trace_source,
            'buy_now' => $buy_now_req,
            'cart_contents_count' => WC()->cart->get_cart_contents_count(),
            'cart_hash' => WC()->cart->get_cart_hash(),
            'cart_url' => $cart_url,
            'checkout_url' => $checkout_url,
            'pll_curlang' => function_exists('pll_current_language') ? pll_current_language() : null,
        )
    );

    return array(
        'ok' => true,
        'buy_now' => $buy_now_req,
        'cart_url' => $cart_url,
        'checkout_url' => $checkout_url,
        'message' => 'Proizvod je uspešno dodat u korpu',
    );
}

/**
 * HTTP POST sa iste stranice kao WooCommerce add-to-cart forma (bez AJAX + JS redirect).
 */
function kombo_meal_plan_handle_cart_post()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_POST['kombo_meal_plan_submit'])) {
        return;
    }
    if (!function_exists('WC')) {
        return;
    }
    if (!kombo_child_needs_meal_plan_assets()) {
        return;
    }
    if (
        !isset($_POST['meal_plan_nonce'])
        || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['meal_plan_nonce'])), 'add_meal_plan_to_cart')
    ) {
        kombo_trace_meal_plan('form_post_nonce_fail', array());
        if (function_exists('wc_add_notice')) {
            wc_add_notice(__('Greška verifikacije forme. Osvežite stranicu i pokušajte ponovo.', 'kombo-child'), 'error');
        }
        wp_safe_redirect(wp_get_referer() ?: home_url('/'));
        exit;
    }

    $pll_from_post = isset($_POST['pll_lang']) ? sanitize_key(wp_unslash($_POST['pll_lang'])) : '';
    kombo_meal_plan_ajax_set_polylang_language($pll_from_post);
    kombo_trace_meal_plan(
        'form_post_start',
        array(
            'pll_lang_post' => $pll_from_post,
            'pll_curlang' => function_exists('pll_current_language') ? pll_current_language() : null,
        )
    );

    try {
        $result = kombo_meal_plan_process_add_to_cart_internal('form_post');
    } catch (Exception $e) {
        kombo_trace_meal_plan('form_post_exception', array('msg' => $e->getMessage()));
        if (function_exists('wc_add_notice')) {
            wc_add_notice(__('Došlo je do greške. Pokušajte ponovo.', 'kombo-child'), 'error');
        }
        wp_safe_redirect(wp_get_referer() ?: home_url('/'));
        exit;
    }

    if (empty($result['ok'])) {
        if (function_exists('wc_add_notice')) {
            wc_add_notice($result['message'], 'error');
        }
        wp_safe_redirect(wp_get_referer() ?: home_url('/'));
        exit;
    }

    if (function_exists('wc_add_notice')) {
        wc_add_notice($result['message'], 'success');
    }
    if (!headers_sent()) {
        nocache_headers();
    }
    $stay_on_page = isset($_POST['stay_on_page']) ? wc_string_to_bool(wp_unslash($_POST['stay_on_page'])) : false;
    if ($stay_on_page) {
        $target = wp_get_referer() ?: home_url('/');
        if (strpos($target, '#mealPlanForm') === false) {
            $target .= '#mealPlanForm';
        }
    } else {
        $target = !empty($result['buy_now']) ? $result['checkout_url'] : $result['cart_url'];
    }
    wp_safe_redirect($target);
    exit;
}
add_action('template_redirect', 'kombo_meal_plan_handle_cart_post', 15);

function handle_meal_plan_add_to_cart()
{
    kombo_trace_meal_plan('ajax_start', array('post_keys' => array_keys($_POST)));
    try {
        check_ajax_referer('meal_plan_nonce', 'nonce');

        $pll_from_post = isset($_POST['pll_lang']) ? sanitize_key(wp_unslash($_POST['pll_lang'])) : '';
        kombo_meal_plan_ajax_set_polylang_language($pll_from_post);
        kombo_trace_meal_plan('pll_context', array(
            'pll_lang_post' => $pll_from_post,
            'pll_curlang' => function_exists('pll_current_language') ? pll_current_language() : null,
        ));

        $result = kombo_meal_plan_process_add_to_cart_internal('ajax');
        if (empty($result['ok'])) {
            wp_send_json_error($result['message']);
            return;
        }

        if (!headers_sent()) {
            nocache_headers();
        }
        wp_send_json_success(array(
            'message' => $result['message'],
            'cart_url' => $result['cart_url'],
            'checkout_url' => $result['checkout_url'],
            'buy_now' => $result['buy_now'],
        ));
    } catch (Exception $e) {
        kombo_trace_meal_plan('exception', array('msg' => $e->getMessage()));
        wp_send_json_error('Došlo je do greške: ' . $e->getMessage());
    }
}


/**
 * Create or get meal plan product
 */
function create_or_get_meal_plan_product($gender, $calories, $package)
{
    try {
        // First, try to find existing product
        $product_name = "Meal Plan {$calories}kcal - " . ucfirst($package);

        // Set up price based on package type
        $price = get_package_price($gender, $calories, $package);
        kombo_trace_meal_plan('create_product_price', array('gender' => $gender, 'price' => $price));

        // Create new product
        $product = new WC_Product_Simple();
        $product->set_name($product_name);
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden');
        $product->set_price($price);
        $product->set_regular_price($price);
        $product->set_sold_individually(true);
        $product->set_virtual(true);

        // Save product
        $product_id = $product->save();
        kombo_trace_meal_plan('product_saved', array('id' => $product_id));

        return $product_id;
    } catch (Exception $e) {
        kombo_trace_meal_plan('create_product_exception', array('msg' => $e->getMessage()));
        return false;
    }
}

/**
 * Get package price based on selection
 */
function get_package_price($gender, $calories, $package)
{
    // Učitaj cene
    $prices = get_option('meal_plan_prices', array());

    kombo_trace_meal_plan_verbose('get_price', compact('gender', 'calories', 'package'));

    // Direktno mapiranje, jer sad koristimo iste nazive
    $plan_type = $gender; // 'slim', 'fit', ili 'protein'

    if (isset($prices[$plan_type][$calories][$package])) {
        return $prices[$plan_type][$calories][$package];
    }

    kombo_trace_meal_plan_verbose('price_missing', compact('plan_type', 'calories', 'package'));
    return 0;
}

/**
 * Modify product name in cart
 */
function modify_cart_item_name($name, $cart_item, $cart_item_key)
{
    if (isset($cart_item['calories']) && isset($cart_item['package_type'])) {
        // Mapiramo stare vrednosti u nove
        $plan_mapping = array(
            'musko' => 'Protein',
            'zensko' => 'Slim',
            'protein' => 'Protein',
            'slim' => 'Slim',
            'fit' => 'Fit'
        );

        // Dodajemo mapiranje za tipove paketa
        $package_mapping = array(
            'dnevni' => pll__('Dnevni'),
            'nedeljni5' => pll__('Nedeljni 5'),
            'nedeljni6' => pll__('Nedeljni 6'),
            'mesecni20' => pll__('Mesečni 20'),
            'mesecni24' => pll__('Mesečni 24')
        );

        $plan_value = isset($plan_mapping[$cart_item['gender']])
            ? $plan_mapping[$cart_item['gender']]
            : $cart_item['gender'];

        $package_value = isset($package_mapping[$cart_item['package_type']])
            ? $package_mapping[$cart_item['package_type']]
            : $cart_item['package_type'];

        $name = sprintf(
            '%s - %s - %d %s - %s',
            pll__('Plan ishrane'),
            pll__($plan_value),
            $cart_item['calories'],
            pll__('kcal'),
            $package_value
        );
    }
    return $name;
}
add_filter('woocommerce_cart_item_name', 'modify_cart_item_name', 10, 3);
function add_cart_item_data_to_order_items($item, $cart_item_key, $values, $order)
{
    if (isset($values['gender'])) {
        $item->add_meta_data('Paket', ucfirst($values['gender']));
    }
    if (isset($values['calories'])) {
        $item->add_meta_data('Kalorije', $values['calories'] . ' kcal');
    }
    if (isset($values['package_type'])) {
        $item->add_meta_data('Tip paketa', ucfirst($values['package_type']));
    }
    if (isset($values['delivery_dates'])) {
        // Proveravamo da li je niz datuma
        if (is_array($values['delivery_dates'])) {
            // Sortiramo datume
            sort($values['delivery_dates']);
            // Spojimo datume zarezom
            $dates_string = implode(', ', $values['delivery_dates']);
            $item->add_meta_data('Datum dostave', $dates_string);
        } else {
            // Ako je samo jedan datum
            $item->add_meta_data('Datum dostave', $values['delivery_dates']);
        }
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'add_cart_item_data_to_order_items', 10, 4);
function display_cart_item_custom_meta($item_data, $cart_item)
{
    if (isset($cart_item['menu_type'])) {
        $menu_type_value = '';
        switch ($cart_item['menu_type']) {
            case 'standard':
                $menu_type_value = pll__('Standardni paketi');
                break;
            case 'vege':
                $menu_type_value = pll__('Vege paketi');
                break;
        }
        $item_data[] = array(
            'key' => pll__('Tip menija'),
            'value' => $menu_type_value
        );
    }
    if (isset($cart_item['gender']) && $cart_item['gender'] !== 'vege') {
        $gender_value = '';
        switch ($cart_item['gender']) {
            case 'slim':
                $gender_value = pll__('Slim');
                break;
            case 'fit':
                $gender_value = pll__('Fit');
                break;
            case 'protein':
                $gender_value = pll__('Protein');
                break;
        }
        $item_data[] = array(
            'key' => pll__('Plan'),
            'value' => $gender_value
        );
    }
    if (isset($cart_item['calories'])) {
        $item_data[] = array(
            'key' => pll__('Kalorije'),
            'value' => $cart_item['calories'] . ' ' . pll__('kcal')
        );
    }

    if (isset($cart_item['package_type'])) {
        $package_translations = array(
            'dnevni' => pll__('Dnevni'),
            'nedeljni5' => pll__('Nedeljni 5'),
            'nedeljni6' => pll__('Nedeljni 6'),
            'mesecni20' => pll__('Mesečni 20'),
            'mesecni24' => pll__('Mesečni 24')
        );

        $package_value = isset($package_translations[$cart_item['package_type']])
            ? $package_translations[$cart_item['package_type']]
            : $cart_item['package_type'];

        $item_data[] = array(
            'key' => pll__('Izaberi paket'),
            'value' => $package_value
        );
    }
    if (isset($cart_item['delivery_dates'])) {
        if (is_array($cart_item['delivery_dates'])) {
            sort($cart_item['delivery_dates']);
            $dates_string = implode(', ', $cart_item['delivery_dates']);
            $item_data[] = array(
                'key' => pll__('Datum dostave'),
                'value' => $dates_string
            );
        } else {
            $item_data[] = array(
                'key' => pll__('Datum dostave'),
                'value' => $cart_item['delivery_dates']
            );
        }
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_cart_item_custom_meta', 10, 2);

function adjust_cart_item_price($cart_object)
{
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    foreach ($cart_object->get_cart() as $cart_item) {
        if (isset($cart_item['_price'])) {
            $cart_item['data']->set_price($cart_item['_price']);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'adjust_cart_item_price', 10, 1);

// Set custom price

/**
 * Add price display to form based on selection
 */
function get_meal_plan_price()
{
    check_ajax_referer('meal_plan_nonce', 'nonce');

    $pll_from_post = isset($_POST['pll_lang']) ? sanitize_key(wp_unslash($_POST['pll_lang'])) : '';
    kombo_meal_plan_ajax_set_polylang_language($pll_from_post);

    $plan = sanitize_text_field($_POST['gender']);
    $calories = sanitize_text_field($_POST['calories']);
    $package = sanitize_text_field($_POST['package']);
    $days_count = isset($_POST['days_count']) ? intval($_POST['days_count']) : 1;

    kombo_trace_meal_plan_verbose('ajax_get_price', compact('plan', 'calories', 'package', 'days_count'));

    // Dobavi osnovnu cenu
    $base_price = get_package_price($plan, $calories, $package);

    // Ako je dnevni paket, pomnoži sa brojem dana
    $final_price = ($package === 'dnevni') ? $base_price * $days_count : $base_price;

    wp_send_json_success(array(
        'price' => $final_price,
        'formatted_price' => wc_price($final_price)
    ));
}


function register_meal_plan_pricing_menu()
{
    add_menu_page(
        'Plan Ishrane - Cene',  // Page title
        'Plan Ishrane',         // Menu title
        'manage_options',       // Capability
        'meal-plan-pricing',    // Menu slug
        'meal_plan_pricing_page', // Function to display the page
        'dashicons-calculator', // Icon
        30                      // Position
    );
}
add_action('admin_menu', 'register_meal_plan_pricing_menu');


function meal_plan_pricing_page()
{
    // Sačuvaj promene ako je forma submitovana
    if (
        isset($_POST['meal_plan_pricing_nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['meal_plan_pricing_nonce'])), 'save_meal_plan_pricing')
        && current_user_can('manage_options')
    ) {
        $prices = array();

        // Slim planovi (ranije ženski)
        foreach (['1300', '1600'] as $calories) {
            foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package) {
                $key = "slim_{$calories}_{$package}";
                $prices['slim'][$calories][$package] = isset($_POST[$key]) ? floatval(wp_unslash($_POST[$key])) : 0;
            }
        }

        // Fit planovi
        foreach (['1600', '1900'] as $calories) {
            foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package) {
                $key = "fit_{$calories}_{$package}";
                $prices['fit'][$calories][$package] = isset($_POST[$key]) ? floatval(wp_unslash($_POST[$key])) : 0;
            }
        }

        // Protein planovi (ranije muški)
        foreach (['2000', '2600'] as $calories) {
            foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package) {
                $key = "protein_{$calories}_{$package}";
                $prices['protein'][$calories][$package] = isset($_POST[$key]) ? floatval(wp_unslash($_POST[$key])) : 0;
            }
        }

        // Vege planovi
        foreach (['1400', '1900'] as $calories) {
            foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package) {
                $key = "vege_{$calories}_{$package}";
                $prices['vege'][$calories][$package] = isset($_POST[$key]) ? floatval(wp_unslash($_POST[$key])) : 0;
            }
        }

        update_option('meal_plan_prices', $prices);
        echo '<div class="updated"><p>' . esc_html('Cene su uspešno sačuvane!') . '</p></div>';
    }

    // Učitaj postojeće cene
    $prices = get_option('meal_plan_prices', array());
?>
    <div class="wrap">
        <h1><?php echo esc_html('Plan Ishrane - Upravljanje cenama'); ?></h1>

        <form method="post" action="">
            <?php wp_nonce_field('save_meal_plan_pricing', 'meal_plan_pricing_nonce'); ?>

            <div class="pricing-sections" style="display: flex; gap: 20px;">
                <!-- Slim planovi -->
                <div class="pricing-section" style="flex: 1;">
                    <h2><?php echo esc_html('Slim planovi'); ?></h2>
                    <?php foreach (['1300', '1600'] as $calories): ?>
                        <div class="calorie-section">
                            <h3><?php echo esc_html((string) $calories); ?> kcal</h3>
                            <table class="form-table">
                                <?php foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package): ?>
                                    <tr>
                                        <th><?php echo esc_html(ucfirst($package)); ?></th>
                                        <td>
                                            <input type="number"
                                                name="<?php echo esc_attr('slim_' . $calories . '_' . $package); ?>"
                                                value="<?php echo esc_attr(isset($prices['slim'][$calories][$package]) ? (string) $prices['slim'][$calories][$package] : ''); ?>"
                                                step="0.01">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Fit planovi -->
                <div class="pricing-section" style="flex: 1;">
                    <h2><?php echo esc_html('Fit planovi'); ?></h2>
                    <?php foreach (['1600', '1900'] as $calories): ?>
                        <div class="calorie-section">
                            <h3><?php echo esc_html((string) $calories); ?> kcal</h3>
                            <table class="form-table">
                                <?php foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package): ?>
                                    <tr>
                                        <th><?php echo esc_html(ucfirst($package)); ?></th>
                                        <td>
                                            <input type="number"
                                                name="<?php echo esc_attr('fit_' . $calories . '_' . $package); ?>"
                                                value="<?php echo esc_attr(isset($prices['fit'][$calories][$package]) ? (string) $prices['fit'][$calories][$package] : ''); ?>"
                                                step="0.01">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Protein planovi -->
                <div class="pricing-section" style="flex: 1;">
                    <h2><?php echo esc_html('Protein planovi'); ?></h2>
                    <?php foreach (['2000', '2600'] as $calories): ?>
                        <div class="calorie-section">
                            <h3><?php echo esc_html((string) $calories); ?> kcal</h3>
                            <table class="form-table">
                                <?php foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package): ?>
                                    <tr>
                                        <th><?php echo esc_html(ucfirst($package)); ?></th>
                                        <td>
                                            <input type="number"
                                                name="<?php echo esc_attr('protein_' . $calories . '_' . $package); ?>"
                                                value="<?php echo esc_attr(isset($prices['protein'][$calories][$package]) ? (string) $prices['protein'][$calories][$package] : ''); ?>"
                                                step="0.01">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Vege planovi -->
                <div class="pricing-section" style="flex: 1;">
                    <h2><?php echo esc_html('Vege planovi'); ?></h2>
                    <?php foreach (['1400', '1900'] as $calories): ?>
                        <div class="calorie-section">
                            <h3><?php echo esc_html((string) $calories); ?> kcal</h3>
                            <table class="form-table">
                                <?php foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package): ?>
                                    <tr>
                                        <th><?php echo esc_html(ucfirst($package)); ?></th>
                                        <td>
                                            <input type="number"
                                                name="<?php echo esc_attr('vege_' . $calories . '_' . $package); ?>"
                                                value="<?php echo esc_attr(isset($prices['vege'][$calories][$package]) ? (string) $prices['vege'][$calories][$package] : ''); ?>"
                                                step="0.01">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr('Sačuvaj promene'); ?>">
            </p>
        </form>
    </div>
<?php
}


function get_all_package_prices()
{
    check_ajax_referer('meal_plan_nonce', 'nonce');

    $gender = sanitize_text_field($_POST['gender']);    // ovo je plan type (slim/fit/protein)
    $calories = sanitize_text_field($_POST['calories']); // kalorije

    $packages = array(
        'dnevni',
        'nedeljni5',
        'nedeljni6',
        'mesecni20',
        'mesecni24'
    );

    $prices = array();
    foreach ($packages as $package) {
        // Dodajemo sve potrebne parametre
        $price = get_package_price($gender, $calories, $package);
        $prices[$package] = wc_price($price); // Formatiramo cenu
    }

    wp_send_json_success(array(
        'prices' => $prices
    ));
}
add_action('wp_ajax_get_all_package_prices', 'get_all_package_prices');
add_action('wp_ajax_nopriv_get_all_package_prices', 'get_all_package_prices');