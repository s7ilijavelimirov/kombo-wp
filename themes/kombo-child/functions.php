<?php

use Illuminate\Support\Arr;

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


/*************  ✨ Codeium Command ⭐  *************/
/**
 * Registers theme navigation menus.
 *
 * This function declares a navigation menu location for the theme,
/******  cc60c070-ac08-4317-a23f-9b5ac256dfbf  *******/
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
    wp_enqueue_style('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_style('slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
    wp_enqueue_script('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '', true);
}
add_action('wp_enqueue_scripts', 'enqueue_slick_slider');



function get_meal_plan_base_product()
{
    return 181;
}

function pll_ru($string, $russian = null)
{

    if ($russian && function_exists('pll_current_language') && pll_current_language() === 'ru') {
        return $russian;
    }

    return function_exists('pll__') ? pll__($string) : $string;
}
function meal_plan_form_assets()
{
    // jQuery UI za datepicker
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style(
        'jquery-ui-css',
        'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'
    );

    // Naš custom script
    wp_enqueue_script(
        'meal-plan-form-script',
        get_stylesheet_directory_uri() . '/assets/js/meal-plan-form.js',
        array('jquery', 'jquery-ui-datepicker'),
        '1.0',
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
            'buyNow' => pll__('Naruči odmah'),
            'fillAllFields' => pll__('Molimo popunite sva polja'),
            'processing' => pll__('Procesiranje...'),
            'addingToCart' => pll__('Dodavanje u korpu...'),
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
        'nonce' => wp_create_nonce('meal_plan_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'meal_plan_form_assets');

add_action('wp_ajax_add_meal_plan_to_cart', 'handle_meal_plan_add_to_cart');
add_action('wp_ajax_nopriv_add_meal_plan_to_cart', 'handle_meal_plan_add_to_cart');

add_action('wp_ajax_get_meal_plan_price', 'get_meal_plan_price');
add_action('wp_ajax_nopriv_get_meal_plan_price', 'get_meal_plan_price');

function add_cart_fragments_scripts()
{
    if (function_exists('is_woocommerce')) {
        wp_enqueue_script('wc-cart-fragments');
    }
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

function handle_meal_plan_add_to_cart()
{
    error_log('=== Starting handle_meal_plan_add_to_cart ===');
    try {
        check_ajax_referer('meal_plan_nonce', 'nonce');

        // Dodajte više error_log-ova za debugging
        error_log('POST data: ' . print_r($_POST, true));

        $menu_type = sanitize_text_field($_POST['menu_type']);
        $gender = sanitize_text_field($_POST['gender']);
        $calories = intval($_POST['calories']);
        $package = sanitize_text_field($_POST['package']);
        $dates = $_POST['dates'];
        $days_count = ($package === 'dnevni' && is_array($dates)) ? count($dates) : 1;

        error_log("Processed data: gender=$gender, calories=$calories, package=$package, days_count=$days_count");
        error_log("Dates: " . print_r($dates, true));

        // Get product ID
        $product_id = get_meal_plan_base_product();
        error_log("Product ID: $product_id");

        if (!$product_id) {
            error_log('Product not found');
            wp_send_json_error('Proizvod nije pronađen');
            return;
        }

        // Get price
        $base_price = get_package_price($gender, $calories, $package);
        $final_price = ($package === 'dnevni') ? $base_price * $days_count : $base_price;
        error_log("Price calculation: base=$base_price, final=$final_price");

        // Prepare cart item data
        $cart_item_data = array(
            'menu_type' => $menu_type,
            'gender' => $gender,
            'calories' => $calories,
            'delivery_dates' => $dates,
            'package_type' => $package,
            'unique_key' => md5(microtime() . rand()),
            '_price' => $final_price
        );

        error_log("Cart item data: " . print_r($cart_item_data, true));

        // Add to cart
        $added = WC()->cart->add_to_cart(
            $product_id,
            1,
            0,
            array(),
            $cart_item_data
        );

        error_log("Add to cart result: " . ($added ? 'success' : 'failed'));

        if ($added) {
            wp_send_json_success(array(
                'message' => 'Proizvod je uspešno dodat u korpu',
                'cart_url' => wc_get_cart_url(),
                'checkout_url' => wc_get_checkout_url()
            ));
        } else {
            error_log('Failed to add to cart');
            wp_send_json_error('Došlo je do greške prilikom dodavanja u korpu');
        }
    } catch (Exception $e) {
        error_log('Exception in handle_meal_plan_add_to_cart: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        wp_send_json_error('Došlo je do greške: ' . $e->getMessage());
    }
}
add_action('wp_ajax_add_meal_plan_to_cart', 'handle_meal_plan_add_to_cart');
add_action('wp_ajax_nopriv_add_meal_plan_to_cart', 'handle_meal_plan_add_to_cart');


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
        error_log("Getting price for gender: $gender, cal: $calories, package: $package - Price: $price");

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
        error_log("Created new product with ID: $product_id");

        return $product_id;
    } catch (Exception $e) {
        error_log('Exception in create_or_get_meal_plan_product: ' . $e->getMessage());
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

    // Debug
    error_log("Getting price for: gender=$gender, calories=$calories, package=$package");
    error_log("Prices in DB: " . print_r($prices, true));

    // Direktno mapiranje, jer sad koristimo iste nazive
    $plan_type = $gender; // 'slim', 'fit', ili 'protein'

    if (isset($prices[$plan_type][$calories][$package])) {
        $price = $prices[$plan_type][$calories][$package];
        error_log("Found price: $price");
        return $price;
    }

    error_log("Price not found, returning 0");
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
add_action('wp_ajax_add_meal_plan_to_cart', 'handle_meal_plan_add_to_cart');
add_action('wp_ajax_nopriv_add_meal_plan_to_cart', 'handle_meal_plan_add_to_cart');


add_action('wp_ajax_get_meal_plan_price', 'get_meal_plan_price');
add_action('wp_ajax_nopriv_get_meal_plan_price', 'get_meal_plan_price');
// Set custom price

/**
 * Add price display to form based on selection
 */
function get_meal_plan_price()
{
    check_ajax_referer('meal_plan_nonce', 'nonce');

    $plan = sanitize_text_field($_POST['gender']);
    $calories = sanitize_text_field($_POST['calories']);
    $package = sanitize_text_field($_POST['package']);
    $days_count = isset($_POST['days_count']) ? intval($_POST['days_count']) : 1;

    error_log("Calculating price for: plan=$plan, calories=$calories, package=$package, days=$days_count");

    // Dobavi osnovnu cenu
    $base_price = get_package_price($plan, $calories, $package);

    // Ako je dnevni paket, pomnoži sa brojem dana
    $final_price = ($package === 'dnevni') ? $base_price * $days_count : $base_price;

    wp_send_json_success(array(
        'price' => $final_price,
        'formatted_price' => wc_price($final_price)
    ));
}
add_action('wp_ajax_get_meal_plan_price', 'get_meal_plan_price');
add_action('wp_ajax_nopriv_get_meal_plan_price', 'get_meal_plan_price');



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
    if (isset($_POST['meal_plan_pricing_nonce']) && wp_verify_nonce($_POST['meal_plan_pricing_nonce'], 'save_meal_plan_pricing')) {
        $prices = array();

        // Slim planovi (ranije ženski)
        foreach (['1300', '1600'] as $calories) {
            foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package) {
                $key = "slim_{$calories}_{$package}";
                $prices['slim'][$calories][$package] = floatval($_POST[$key]);
            }
        }

        // Fit planovi
        foreach (['1600', '1900'] as $calories) {
            foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package) {
                $key = "fit_{$calories}_{$package}";
                $prices['fit'][$calories][$package] = floatval($_POST[$key]);
            }
        }

        // Protein planovi (ranije muški)
        foreach (['2000', '2600'] as $calories) {
            foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package) {
                $key = "protein_{$calories}_{$package}";
                $prices['protein'][$calories][$package] = floatval($_POST[$key]);
            }
        }

        // Vege planovi
        foreach (['1400', '1900'] as $calories) {
            foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package) {
                $key = "vege_{$calories}_{$package}";
                $prices['vege'][$calories][$package] = floatval($_POST[$key]);
            }
        }

        update_option('meal_plan_prices', $prices);
        echo '<div class="updated"><p>Cene su uspešno sačuvane!</p></div>';
    }

    // Učitaj postojeće cene
    $prices = get_option('meal_plan_prices', array());
?>
    <div class="wrap">
        <h1>Plan Ishrane - Upravljanje cenama</h1>

        <form method="post" action="">
            <?php wp_nonce_field('save_meal_plan_pricing', 'meal_plan_pricing_nonce'); ?>

            <div class="pricing-sections" style="display: flex; gap: 20px;">
                <!-- Slim planovi -->
                <div class="pricing-section" style="flex: 1;">
                    <h2>Slim planovi</h2>
                    <?php foreach (['1300', '1600'] as $calories): ?>
                        <div class="calorie-section">
                            <h3><?php echo $calories; ?> kcal</h3>
                            <table class="form-table">
                                <?php foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package): ?>
                                    <tr>
                                        <th><?php echo ucfirst($package); ?></th>
                                        <td>
                                            <input type="number"
                                                name="slim_<?php echo $calories; ?>_<?php echo $package; ?>"
                                                value="<?php echo isset($prices['slim'][$calories][$package]) ? $prices['slim'][$calories][$package] : ''; ?>"
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
                    <h2>Fit planovi</h2>
                    <?php foreach (['1600', '1900'] as $calories): ?>
                        <div class="calorie-section">
                            <h3><?php echo $calories; ?> kcal</h3>
                            <table class="form-table">
                                <?php foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package): ?>
                                    <tr>
                                        <th><?php echo ucfirst($package); ?></th>
                                        <td>
                                            <input type="number"
                                                name="fit_<?php echo $calories; ?>_<?php echo $package; ?>"
                                                value="<?php echo isset($prices['fit'][$calories][$package]) ? $prices['fit'][$calories][$package] : ''; ?>"
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
                    <h2>Protein planovi</h2>
                    <?php foreach (['2000', '2600'] as $calories): ?>
                        <div class="calorie-section">
                            <h3><?php echo $calories; ?> kcal</h3>
                            <table class="form-table">
                                <?php foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package): ?>
                                    <tr>
                                        <th><?php echo ucfirst($package); ?></th>
                                        <td>
                                            <input type="number"
                                                name="protein_<?php echo $calories; ?>_<?php echo $package; ?>"
                                                value="<?php echo isset($prices['protein'][$calories][$package]) ? $prices['protein'][$calories][$package] : ''; ?>"
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
                    <h2>Vege planovi</h2>
                    <?php foreach (['1400', '1900'] as $calories): ?>
                        <div class="calorie-section">
                            <h3><?php echo $calories; ?> kcal</h3>
                            <table class="form-table">
                                <?php foreach (['dnevni', 'nedeljni5', 'nedeljni6', 'mesecni20', 'mesecni24'] as $package): ?>
                                    <tr>
                                        <th><?php echo ucfirst($package); ?></th>
                                        <td>
                                            <input type="number"
                                                name="vege_<?php echo $calories; ?>_<?php echo $package; ?>"
                                                value="<?php echo isset($prices['vege'][$calories][$package]) ? $prices['vege'][$calories][$package] : ''; ?>"
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
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Sačuvaj promene">
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






// Prvo registrujemo Custom Post Type za nedeljni meni
function register_weekly_menu_post_type()
{
    $labels = array(
        'name'                  => 'Nedeljni Meni',
        'singular_name'         => 'Nedeljni Meni',
        'menu_name'            => 'Nedeljni Meni',
        'add_new'              => 'Dodaj Novi',
        'add_new_item'         => 'Dodaj Novi Meni',
        'edit_item'            => 'Izmeni Meni',
        'new_item'             => 'Novi Meni',
        'view_item'            => 'Pogledaj Meni',
        'search_items'         => 'Pretraži Menije',
        'not_found'            => 'Nema pronađenih menija',
        'not_found_in_trash'   => 'Nema menija u korpi'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'supports'            => array('title'),
        'menu_icon'           => 'dashicons-food'
    );

    register_post_type('weekly_menu', $args);
}
add_action('init', 'register_weekly_menu_post_type');
add_action('after_setup_theme', function () {
    if (function_exists('pll_register_string')) {
        add_filter('pll_get_post_types', function ($post_types) {
            return array_merge($post_types, array('weekly_menu' => 'weekly_menu'));
        });
    }
});

// Vege Menu CPT
function register_vege_menu_post_type()
{
    $labels = array(
        'name'                  => 'Vege Meni',
        'singular_name'         => 'Vege Meni',
        'menu_name'            => 'Vege Meni',
        'add_new'              => 'Dodaj Novi',
        'add_new_item'         => 'Dodaj Novi Vege Meni',
        'edit_item'            => 'Izmeni Vege Meni',
        'new_item'             => 'Novi Vege Meni',
        'view_item'            => 'Pogledaj Vege Meni',
        'search_items'         => 'Pretraži Vege Menije',
        'not_found'            => 'Nema pronađenih vege menija',
        'not_found_in_trash'   => 'Nema vege menija u korpi'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'supports'            => array('title'),
        'menu_icon'           => 'dashicons-carrot'
    );

    register_post_type('vege_menu', $args);
}
add_action('init', 'register_vege_menu_post_type');
add_action('after_setup_theme', function () {
    if (function_exists('pll_register_string')) {
        add_filter('pll_get_post_types', function ($post_types) {
            return array_merge($post_types, array('vege_menu' => 'vege_menu'));
        });
    }
});

// CPT za Nedeljni Meni sledeće nedelje
function register_next_weekly_menu_post_type()
{
    $labels = array(
        'name'                  => 'Nedeljni Meni (Sledeća Nedelja)',
        'singular_name'         => 'Nedeljni Meni (Sledeća Nedelja)',
        'menu_name'            => 'Sledeća Nedelja - Standard',
        'add_new'              => 'Dodaj Novi',
        'add_new_item'         => 'Dodaj Novi Meni',
        'edit_item'            => 'Izmeni Meni',
        'new_item'             => 'Novi Meni',
        'view_item'            => 'Pogledaj Meni',
        'search_items'         => 'Pretraži Menije',
        'not_found'            => 'Nema pronađenih menija',
        'not_found_in_trash'   => 'Nema menija u korpi'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'supports'            => array('title'),
        'menu_icon'           => 'dashicons-calendar-alt'
    );

    register_post_type('next_weekly_menu', $args);
}
add_action('init', 'register_next_weekly_menu_post_type');
add_action('after_setup_theme', function () {
    if (function_exists('pll_register_string')) {
        add_filter('pll_get_post_types', function ($post_types) {
            return array_merge($post_types, array('next_weekly_menu' => 'next_weekly_menu'));
        });
    }
});

// CPT za Vege Meni sledeće nedelje
function register_next_vege_menu_post_type()
{
    $labels = array(
        'name'                  => 'Vege Meni (Sledeća Nedelja)',
        'singular_name'         => 'Vege Meni (Sledeća Nedelja)',
        'menu_name'            => 'Sledeća Nedelja - Vege',
        'add_new'              => 'Dodaj Novi',
        'add_new_item'         => 'Dodaj Novi Vege Meni',
        'edit_item'            => 'Izmeni Vege Meni',
        'new_item'             => 'Novi Vege Meni',
        'view_item'            => 'Pogledaj Vege Meni',
        'search_items'         => 'Pretraži Vege Menije',
        'not_found'            => 'Nema pronađenih vege menija',
        'not_found_in_trash'   => 'Nema vege menija u korpi'
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'supports'            => array('title'),
        'menu_icon'           => 'dashicons-calendar'
    );

    register_post_type('next_vege_menu', $args);
}
add_action('init', 'register_next_vege_menu_post_type');
add_action('after_setup_theme', function () {
    if (function_exists('pll_register_string')) {
        add_filter('pll_get_post_types', function ($post_types) {
            return array_merge($post_types, array('next_vege_menu' => 'next_vege_menu'));
        });
    }
});

add_action('init', function () {
    pll_register_string('Order number', 'Order number:', 'WooCommerce');
    pll_register_string('Order date', 'Date:', 'WooCommerce');
    pll_register_string('Order email', 'Email:', 'WooCommerce');
    pll_register_string('Order total', 'Total:', 'WooCommerce');
    pll_register_string('Payment method', 'Payment method:', 'WooCommerce');
    pll_register_string('Order details', 'Order details', 'WooCommerce');
    pll_register_string('Product', 'Product', 'WooCommerce');
    pll_register_string('Total', 'Total', 'WooCommerce');
    pll_register_string('Meni za ovu nedelju', 'Meni za ovu nedelju');
    pll_register_string('Meni se objavljuje svake nedelje u sedmici.', 'Meni se objavljuje svake nedelje u sedmici.');
    pll_register_string('Ponedeljak', 'Ponedeljak');
    pll_register_string('Utorak', 'Utorak');
    pll_register_string('Sreda', 'Sreda');
    pll_register_string('Cetvrtak', 'Cetvrtak');
    pll_register_string('Petak', 'Petak');
    pll_register_string('Subota', 'Subota');
    pll_register_string('Doručak', 'Doručak');
    pll_register_string('Užina 1', 'Užina 1');
    pll_register_string('Ručak', 'Ručak');
    pll_register_string('Užina 2', 'Užina 2');
    pll_register_string('Večera', 'Večera');
    pll_register_string('Naruči svoj paket', 'Naruči svoj paket');
    pll_register_string('Pol', 'Pol');
    pll_register_string('Muško', 'Muško');
    pll_register_string('Žensko', 'Žensko');
    pll_register_string('Kalorije', 'Kalorije');
    pll_register_string('Tip paketa', 'Tip paketa');
    pll_register_string('Datum dostave', 'Datum dostave');
    pll_register_string('Proizvod je uspešno dodat u korpu', 'Proizvod je uspešno dodat u korpu');
    pll_register_string('Izaberi paket', 'Izaberi paket');
    pll_register_string('Na osnovu odabira paketa, cene planova se menjaju', 'Na osnovu odabira paketa, cene planova se menjaju');
    pll_register_string('Izaberi plan', 'Izaberi plan');
    pll_register_string('Dnevni', 'Dnevni');
    pll_register_string('RSD po danu', 'RSD po danu');
    pll_register_string('RSD', 'RSD');
    pll_register_string('Nedeljni 5', 'Nedeljni 5');
    pll_register_string('(radni dani)', '(radni dani)');
    pll_register_string('Nedeljni 6', 'Nedeljni 6');
    pll_register_string('(radni dani i subota)', '(radni dani i subota)');
    pll_register_string('Mesečni 20', 'Mesečni 20');
    pll_register_string('Mesečni 24', 'Mesečni 24');
    pll_register_string('Izaberi datum za naručivanje', 'Izaberi datum za naručivanje');
    pll_register_string('Cena paketa:', 'Cena paketa:');
    pll_register_string('Dodaj u korpu', 'Dodaj u korpu');
    pll_register_string('Naruči odmah', 'Naruči odmah');
    pll_register_string('kcal', 'kcal');
    pll_register_string('Plan ishrane', 'Plan ishrane');
    pll_register_string('Najnovije', 'Najnovije');
    pll_register_string('Nazad na prodavnicu', 'Nazad na prodavnicu');
    pll_register_string('Nazad na početnu', 'Nazad na početnu');
    pll_register_string('Vaša korpa je prazna', 'Vaša korpa je prazna');
    pll_register_string('Naruči Kombo paket', 'Naruči Kombo paket');
    pll_register_string('Oops! Stranica nije pronađena', 'Oops! Stranica nije pronađena');
    pll_register_string('Čini se da stranica koju tražite ne postoji. Možda ste pogrešno uneli adresu ili je stranica uklonjena.', 'Čini se da stranica koju tražite ne postoji. Možda ste pogrešno uneli adresu ili je stranica uklonjena.');
    pll_register_string('Greška 404', 'Greška 404');
    pll_register_string('Protein', 'Protein');
    pll_register_string('Slim', 'Slim');
    pll_register_string('Fit', 'Fit');
    pll_register_string('Mali', 'Mali');
    pll_register_string('Veliki', 'Veliki');
    pll_register_string('Izaberi veličinu', 'Izaberi veličinu');
    pll_register_string('Januar', 'Januar');
    pll_register_string('Februar', 'Februar');
    pll_register_string('Mart', 'Mart');
    pll_register_string('April', 'April');
    pll_register_string('Maj', 'Maj');
    pll_register_string('Jun', 'Jun');
    pll_register_string('Jul', 'Jul');
    pll_register_string('Avgust', 'Avgust');
    pll_register_string('Septembar', 'Septembar');
    pll_register_string('Oktobar', 'Oktobar');
    pll_register_string('Novembar', 'Novembar');
    pll_register_string('Decembar', 'Decembar');
    pll_register_string('Cena', 'Cena');
    pll_register_string('nedeljni5', 'nedeljni5');
    pll_register_string('nedeljni6', 'nedeljni6');
    pll_register_string('Datum za naručivanje', 'Datum za naručivanje');
    pll_register_string('Količina', 'Količina');
    pll_register_string('Dostava dostupna samo za Beograd, Novi Sad i Staru i Novu Pazovu', 'Dostava dostupna samo za Beograd, Novi Sad i Staru i Novu Pazovu');
    pll_register_string('Dostava', 'Dostava');
    pll_register_string('Ukupna cena', 'Ukupna cena');
    pll_register_string('Kupi', 'Kupi');
    pll_register_string('Dostava i plaćanje', 'Dostava i plaćanje');
    pll_register_string('Napomena o narudžbini', 'Napomena o narudžbini');
    pll_register_string('Dodajte napomenu za vašu narudžbinu (opciono)', 'Dodajte napomenu za vašu narudžbinu (opciono)');
    pll_register_string('Vaša narudžbina', 'Vaša narudžbina');
    pll_register_string('Dostava kurirskom službom', 'Dostava kurirskom službom');
    pll_register_string('Plaćanje pouzećem', 'Plaćanje pouzećem');
    pll_register_string('od', 'od');
    pll_register_string('do', 'do');
    pll_register_string('View Cart', 'View Cart');
    pll_register_string('Naruči', 'Naruči');
    pll_register_string('Kalendar', 'Kalendar');
    pll_register_string('Plan', 'Plan');
    pll_register_string('Select plan', 'Molimo izaberite plan');
    pll_register_string('Select calories', 'Molimo izaberite kalorije');
    pll_register_string('Select package', 'Molimo izaberite paket');
    pll_register_string('Select date', 'Molimo izaberite datum');
    pll_register_string('Sve cene su sa uračunatim PDV-om i nema skrivenih troškova.', 'Sve cene su sa uračunatim PDV-om i nema skrivenih troškova.');
    pll_register_string('Plaćanje platnom karticom', 'Plaćanje platnom karticom');
    pll_register_string(
        'Terms error message',
        'Molimo Vas da pročitate i prihvatite uslove kako biste nastavili sa plasiranjem narudžbine.',
        'WooCommerce'
    );
    pll_register_string('Processing', 'Processing...',);
    pll_register_string('Adding to cart', 'Adding to cart',);
    pll_register_string('Fill all fields', 'Fill all fields',);
    pll_register_string('Došlo je do greške pri komunikaciji sa serverom. Pokušajte ponovo.', 'Došlo je do greške pri komunikaciji sa serverom. Pokušajte ponovo.');
    pll_register_string('Broj narudžbine', 'Broj narudžbine');
    pll_register_string('Datum', 'Datum');
    pll_register_string('Ukupno', 'Ukupno');
    pll_register_string('Način plaćanja', 'Način plaćanja');
    pll_register_string('Nastavite sa plaćanjem', 'Nastavite sa plaćanjem');
    pll_register_string('Vege paketi', 'Vege paketi');
    pll_register_string('Sledeća nedelja', 'Sledeća nedelja');
    pll_register_string('Ova nedelja', 'Ova nedelja');
	pll_register_string('Tip menija', 'Tip menija');
	pll_register_string('Standardni paketi', 'Standardni paketi');
});


add_action('wp_footer', 'translate_nestpay_button_js');
function translate_nestpay_button_js()
{
?>
    <script>
        jQuery(document).ready(function($) {
            // Prevodi za dugme
            if ($('.button-proceed').length) {
                $('.button-proceed').val('Nastavi plaćanje / Continue payment');
            }

            // Prevodi za order details
            const translations = {
                'Broj narudžbine:': 'Broj narudžbine / Order number:',
                'Datum:': 'Datum / Date:',
                'Ukupno:': 'Ukupno / Total:',
                'Način plaćanja:': 'Način plaćanja / Payment method:',
                'Plaćanje platnom karticom': 'Plaćanje platnom karticom / Card payment'
            };

            // Zamenjujemo samo prvi text node u svakom li elementu
            $('.order_details li').each(function() {
                const $li = $(this);
                const firstTextNode = Array.from($li[0].childNodes).find(node =>
                    node.nodeType === 3 && node.textContent.trim()
                );

                if (firstTextNode && translations[firstTextNode.textContent.trim()]) {
                    firstTextNode.textContent = translations[firstTextNode.textContent.trim()];
                }
            });

            // Posebno za način plaćanja u strong tagu
            $('.order_details li.method strong').text('Plaćanje platnom karticom / Card payment');
        });
    </script>
<?php
}

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
function custom_remove_pak_field($fields)
{
    // Uklanja PAK polje
    unset($fields['billing']['billing_pak']);
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'custom_remove_pak_field', 20);

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

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $new_dates = $_POST['new_date'];

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








add_action('wp_ajax_get_cart_totals', 'get_cart_totals');
add_action('wp_ajax_nopriv_get_cart_totals', 'get_cart_totals');
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
add_action('wp_ajax_update_cart_action', 'handle_cart_update_action');
add_action('wp_ajax_nopriv_update_cart_action', 'handle_cart_update_action');
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
add_filter('woocommerce_cart_item_price', 'modify_cart_item_price_display', 100, 3);
function modify_cart_item_price_display($price_html, $cart_item, $cart_item_key)
{
    $product_price = $cart_item['data']->get_price();
    $quantity = $cart_item['quantity'];

    // Množimo cenu sa količinom i formatiramo
    return wc_price($product_price * $quantity);
}

// Dodajemo još jedan hook sa višim prioritetom za ažuriranje pojedinačne cene
add_filter('woocommerce_cart_item_price', 'update_cart_item_price_display', 100, 3);
function update_cart_item_price_display($price, $cart_item, $cart_item_key)
{
    if (isset($cart_item['delivery_dates']) && strpos(strtolower($cart_item['data']->get_name()), 'dnevni') !== false) {
        $base_price = get_package_price(
            $cart_item['gender'],
            $cart_item['calories'],
            'dnevni'
        );
        $days_count = is_array($cart_item['delivery_dates']) ? count($cart_item['delivery_dates']) : 1;
        $new_price = $base_price * $days_count;

        return wc_price($new_price);
    }
    return $price;
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

    if (is_cart()) {
        wp_enqueue_script(
            'cart-script',
            get_stylesheet_directory_uri() . '/assets/js/cart.js',
            array('jquery'),
            filemtime(get_stylesheet_directory() . '/assets/js/cart.js'),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_cart_script');


// function enqueue_meal_plan_scripts()
// {
//     wp_enqueue_script(
//         'meal-plan-form',
//         get_stylesheet_directory_uri() . '/js/meal-plan-form.js',
//         array('jquery'),
//         '1.0',
//         true
//     );

//     wp_localize_script(
//         'meal-plan-form',
//         'mealTranslations',
//         array(
//             'small' => pll__('Mali'),
//             'large' => pll__('Veliki'),
//             'kcal' => pll__('kcal'),
//             'od' => pll__('od'),
//             'do' => pll__('do'),
//             'months' => array(
//                 pll__('Januar'),
//                 pll__('Februar'),
//                 pll__('Mart'),
//                 pll__('April'),
//                 pll__('Maj'),
//                 pll__('Jun'),
//                 pll__('Jul'),
//                 pll__('Avgust'),
//                 pll__('Septembar'),
//                 pll__('Oktobar'),
//                 pll__('Novembar'),
//                 pll__('Decembar')
//             )
//         )
//     );
// }
// add_action('wp_enqueue_scripts', 'enqueue_meal_plan_scripts');
function enqueue_cart_scripts()
{
    wp_enqueue_script('cart-scripts', get_stylesheet_directory_uri() . '/assets/js/cart.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_cart_scripts');





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


// Prvo registrujemo filtere za URL-ove
add_filter('xoo_wsc_cart_page_url', 'custom_cart_url');
add_filter('xoo_wsc_checkout_page_url', 'custom_checkout_url');
add_filter('woocommerce_get_cart_url', 'custom_cart_url');
add_filter('woocommerce_get_checkout_url', 'custom_checkout_url');

function custom_cart_url($url)
{
    if (function_exists('pll_current_language')) {
        $current_lang = pll_current_language();
        switch ($current_lang) {
            case 'en':
                return site_url('/en/cart/');
            case 'ru':
                return site_url('/ru/cart/');
            default:
                return site_url('/korpa/');
        }
    }
    return $url;
}

function custom_checkout_url($url)
{
    if (function_exists('pll_current_language')) {
        $current_lang = pll_current_language();
        switch ($current_lang) {
            case 'en':
                return site_url('/en/checkout/');
            case 'ru':
                return site_url('/ru/checkout/');
            default:
                return site_url('/placanje/');
        }
    }
    return $url;
}
add_action('wp_footer', 'add_cart_url_script');
function add_cart_url_script()
{
?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            const currentLang = $('html').attr('lang').split('-')[0];
            const urls = {
                'en': {
                    cart: '<?php echo site_url("/en/cart/"); ?>',
                    checkout: '<?php echo site_url("/en/payment/"); ?>'
                },
                'ru': {
                    cart: '<?php echo site_url("/ru/корзина/"); ?>',
                    checkout: '<?php echo site_url("/ru/оплата/"); ?>'
                },
                'sr': {
                    cart: '<?php echo site_url("/korpa/"); ?>',
                    checkout: '<?php echo site_url("/placanje/"); ?>'
                }
            };

            // Funkcija za ažuriranje URL-ova
            function updateCartUrls() {

                const cartButton = $('.xoo-wsc-ft-btn-cart');
                const checkoutButton = $('.xoo-wsc-ft-btn-checkout');

                if (cartButton.length && urls[currentLang]) {

                    cartButton.attr('href', urls[currentLang].cart);
                }

                if (checkoutButton.length && urls[currentLang]) {

                    checkoutButton.attr('href', urls[currentLang].checkout);
                }
            }

            // Kreiranje MutationObserver-a
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        const cartButton = $('.xoo-wsc-ft-btn-cart');
                        if (cartButton.length) {
                            updateCartUrls();
                        }
                    }
                });
            });

            // Posmatraj ceo document za promene
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            // Takođe pokušaj ažurirati URL-ove na sve ove događaje
            $(document.body).on('xoo_wsc_cart_opened updated_cart_totals updated_checkout', function() {
                setTimeout(updateCartUrls, 100);
            });

            // Inicijalno ažuriranje
            updateCartUrls();

            // Dodatno ažuriranje nakon 1 sekunde
            setTimeout(updateCartUrls, 1000);
        });
    </script>
    <?php
}



//! 

add_filter('woocommerce_get_endpoint_url', 'add_language_to_endpoints', 10, 4);
function add_language_to_endpoints($url, $endpoint, $value, $permalink)
{
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

    if (function_exists('pll_current_language')) {
        $lang = pll_current_language();
        $url = add_query_arg('lang', $lang, $url);
    }

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
add_action('wp_footer', 'disable_cart_fragments_loading', 999);
function disable_cart_fragments_loading()
{
    if (is_checkout()) {
    ?>
        <script type="text/javascript">
            jQuery(function($) {
                $(document.body).off('wc_fragments_loaded wc_fragments_refreshed');
                $(window).off('beforeunload');
            });
        </script>
    <?php
    }
}
add_action('woocommerce_review_order_before_payment', 'add_translation_support');
function add_translation_support()
{
    if (function_exists('pll_current_language')) {
    ?>
        <script type="text/javascript">
            jQuery(function($) {
                $(document.body).on('updated_checkout', function() {
                    $('.woocommerce-shipping-totals th').text('<?php echo esc_js(pll__("Dostava")); ?>');
                    $('.order-total th').text('<?php echo esc_js(pll__("Ukupno")); ?>');
                    $('.cart-subtotal th').text('<?php echo esc_js(pll__("Međuzbir")); ?>');
                    $('#place_order').text('<?php echo esc_js(pll__("Naruči")); ?>');
                });
            });
        </script>
    <?php
    }
}
add_action('wp_footer', 'add_checkout_translation_handler');
function add_checkout_translation_handler()
{
    if (!is_checkout()) return;

    ?>
    <script type="text/javascript">
        jQuery(function($) {
            var translations = {
                'shipping': <?php echo json_encode(pll__('Dostava')); ?>,
                'total': <?php echo json_encode(pll__('Ukupno')); ?>,
                'subtotal': <?php echo json_encode(pll__('Međuzbir')); ?>,
                'place_order': <?php echo json_encode(pll__('Naruči')); ?>
            };

            var isTranslating = false;

            function translateCheckout() {
                if (isTranslating) return;
                isTranslating = true;

                $('.woocommerce-shipping-totals th').text(translations.shipping);
                $('.order-total th').text(translations.total);
                $('.cart-subtotal th').text(translations.subtotal);
                $('#place_order').text(translations.place_order);

                setTimeout(function() {
                    isTranslating = false;
                }, 1000);
            }

            $(document.body).on('updated_checkout payment_method_selected update_checkout', function(e) {
                translateCheckout();
            });

            translateCheckout();
        });
    </script>
<?php
}
// Dodajemo funkciju za prevod varijacija
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
// Dodajemo i JavaScript za održavanje prevoda nakon AJAX-a
add_action('wp_footer', 'add_variations_translation_script');
function add_variations_translation_script()
{
    if (!is_checkout()) return;
?>
    <script type="text/javascript">
        jQuery(function($) {
            const variations_translations = {
                'Plan ishrane': '<?php echo esc_js(pll__("Plan ishrane")); ?>',
                'Plan:': '<?php echo esc_js(pll__("Plan")); ?>', // Promena ovde
                'Plan': '<?php echo esc_js(pll__("Plan")); ?>', // Dodato
                'Kalorije:': '<?php echo esc_js(pll__("Kalorije")); ?>', // Promena ovde
                'Kalorije': '<?php echo esc_js(pll__("Kalorije")); ?>', // Dodato
                'Izaberi paket:': '<?php echo esc_js(pll__("Izaberi paket")); ?>', // Promena ovde
                'Izaberi paket': '<?php echo esc_js(pll__("Izaberi paket")); ?>', // Dodato
                'Datum dostave:': '<?php echo esc_js(pll__("Datum dostave")); ?>',
                'Nedeljni 6': '<?php echo esc_js(pll__("Nedeljni 6")); ?>',
                'Nedeljni 5': '<?php echo esc_js(pll__("Nedeljni 5")); ?>',
                'Mesečni 20': '<?php echo esc_js(pll__("Mesecni 20")); ?>',
                'Mesečni 24': '<?php echo esc_js(pll__("Mesecni 24")); ?>',
                'Dnevni': '<?php echo esc_js(pll__("Dnevni")); ?>',
                'Slim': '<?php echo esc_js(pll__("Slim")); ?>',
                'Fit': '<?php echo esc_js(pll__("Fit")); ?>',
                'Protein plus': '<?php echo esc_js(pll__("Protein")); ?>'
            };

            function translateText(text) {
                // Prvo tražimo tačne podudarnosti
                Object.keys(variations_translations).forEach(function(key) {
                    if (text.trim() === key) {
                        text = variations_translations[key];
                    }
                });

                // Zatim tražimo delimična podudaranja
                Object.keys(variations_translations).forEach(function(key) {
                    if (text.includes(key)) {
                        text = text.replace(key, variations_translations[key]);
                    }
                });
                return text;
            }
            $(document.body).on('updated_checkout', function() {
                setTimeout(function() {
                    // Prevod naziva proizvoda (prvi deo)
                    $('.product-name').contents().filter(function() {
                        return this.nodeType === 3; // Text nodes only
                    }).each(function() {
                        this.nodeValue = translateText(this.nodeValue);
                    });

                    // Prevod količine
                    $('.product-name strong.product-quantity').contents().filter(function() {
                        return this.nodeType === 3;
                    }).each(function() {
                        this.nodeValue = translateText(this.nodeValue);
                    });

                    // Prevod varijacija
                    $('.variation dt').contents().filter(function() {
                        return this.nodeType === 3;
                    }).each(function() {
                        this.nodeValue = translateText(this.nodeValue);
                    });

                    // Prevod vrednosti varijacija
                    $('.variation dd p').contents().filter(function() {
                        return this.nodeType === 3;
                    }).each(function() {
                        this.nodeValue = translateText(this.nodeValue);
                    });
                }, 100);
            });
        });
    </script>
<?php
}
add_action('wp_footer', 'add_checkout_translations_script');
function add_checkout_translations_script()
{
    if (!is_checkout()) return;
?>
    <script type="text/javascript">
        jQuery(function($) {
            var translations = {
                'Proizvod': '<?php echo esc_js(pll__("Product")); ?>',
                'Ukupno': '<?php echo esc_js(pll__("Total")); ?>',
                'Delivery': '<?php echo esc_js(pll__("Delivery")); ?>',
                'Subtotal': '<?php echo esc_js(pll__("Subtotal")); ?>',
                'Plaćanje pouzećem': '<?php echo esc_js(pll__("Plaćanje pouzećem")); ?>',
                'Dostava kurirskom službom': '<?php echo esc_js(pll__("Dostava kurirskom službom")); ?>',
                'Sve cene su sa uračunatim PDV-om i nema skrivenih troškova.': '<?php echo esc_js(pll__("Sve cene su sa uračunatim PDV-om i nema skrivenih troškova.")); ?>',
                'Molimo Vas da pročitate i prihvatite uslove kako biste nastavili sa plasiranjem narudžbine.': '<?php echo esc_js(pll__("Molimo Vas da pročitate i prihvatite uslove kako biste nastavili sa plasiranjem narudžbine.")); ?>'
            };

            $(document.body).on('updated_checkout', function() {
                Object.keys(translations).forEach(function(key) {
                    $('*:contains("' + key + '")').each(function() {
                        if ($(this).children().length === 0) {
                            $(this).text($(this).text().replace(key, translations[key]));
                        }
                    });
                });

                // Specifično targetiranje elemenata
                $('.price-notice').text(translations['Sve cene su sa uračunatim PDV-om i nema skrivenih troškova.']);
                $('#terms').text(translations['Molimo Vas da pročitate i prihvatite uslove kako biste nastavili sa plasiranjem narudžbine.']);
            });
        });
    </script>
<?php
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
            // Dodajemo jezik u svaki AJAX poziv
            $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
                if (options.url && options.url.indexOf("wc-ajax") > -1) {
                    var lang = $("input[name=\'lang\']").val();
                    if (lang) {
                        options.url = options.url + (options.url.indexOf("?") > -1 ? "&" : "?") + "lang=" + lang;
                        if (options.data) {
                            options.data = options.data + "&lang=" + lang;
                        } else {
                            options.data = "lang=" + lang;
                        }
                    }
                }
            });

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
add_filter('woocommerce_update_order_review_fragments', 'add_language_to_fragments', 10, 1);
function add_language_to_fragments($fragments)
{
    if (function_exists('pll_current_language')) {
        $fragments['current_language'] = pll_current_language();
    }
    return $fragments;
}
add_action('woocommerce_checkout_update_order_review', 'set_checkout_language');
function set_checkout_language()
{
    if (isset($_POST['lang']) && function_exists('pll_current_language')) {
        PLL()->curlang = PLL()->model->get_language($_POST['lang']);
    }
}
add_action('wp_enqueue_scripts', 'modify_checkout_js', 99);
function modify_checkout_js()
{
    if (is_checkout()) {
        wp_dequeue_script('wc-cart-fragments');
        wp_enqueue_script('wc-cart-fragments', null, array('jquery'), null, true);

        // Dodajemo custom skriptu za checkout
        wp_add_inline_script('woocommerce-checkout', '
            jQuery(function($) {
                // Sprečavamo višestruke AJAX pozive
                var isUpdating = false;
                
                $(document.body).on("update_checkout", function() {
                    if (isUpdating) return false;
                    isUpdating = true;
                    
                    setTimeout(function() {
                        isUpdating = false;
                    }, 2000);
                });
            });
        ');
    }
}
add_filter('woocommerce_get_endpoint_url', 'fix_endpoint_url', 10, 4);
function fix_endpoint_url($url, $endpoint = '', $value = '', $permalink = '')
{
    if (strpos($url, '%25%25endpoint%25%25') !== false) {
        $url = str_replace('%25%25endpoint%25%25', $endpoint, $url);
    }
    return $url;
}


// 1. Prvo ćemo kompletno isključiti cart fragments na checkout stranici
add_action('wp_enqueue_scripts', 'handle_checkout_scripts', 100);
function handle_checkout_scripts()
{
    if (is_checkout()) {
        // Isključujemo cart fragments
        wp_dequeue_script('wc-cart-fragments');

        // Dodajemo custom checkout handler
        wp_enqueue_script(
            'custom-checkout-handler',
            null,
            array('jquery', 'woocommerce-checkout'),
            null,
            true
        );

        wp_add_inline_script('custom-checkout-handler', '
            jQuery(function($) {
                // Sprečavamo višestruke update_checkout pozive
                var preventMultipleUpdates = false;
                
                $(document.body).on("update_checkout", function() {
                    if (preventMultipleUpdates) {
                        return false;
                    }
                    
                    preventMultipleUpdates = true;
                    setTimeout(function() {
                        preventMultipleUpdates = false;
                    }, 3000);
                });
                
                // Modifikujemo WooCommerce checkout objekt
                if (typeof wc_checkout_params !== "undefined") {
                    wc_checkout_params.ajax_url = wc_checkout_params.ajax_url + "?lang=' . pll_current_language() . '";
                }
                
                // Dodajemo jezik u sve AJAX pozive na checkout stranici
                $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
                    if (options.url.indexOf("wc-ajax") > -1) {
                        options.url = options.url.replace("%25%25endpoint%25%25", "update_order_review");
                        if (options.url.indexOf("lang=") === -1) {
                            options.url += (options.url.indexOf("?") > -1 ? "&" : "?") + "lang=' . pll_current_language() . '";
                        }
                    }
                });
            });
        ');
    }
}

add_filter('woocommerce_get_script_data', 'fix_checkout_script_data', 10, 2);
function fix_checkout_script_data($data, $handle)
{
    if ($handle === 'wc-checkout') {
        if (function_exists('pll_current_language')) {
            $lang = pll_current_language();
            $data['ajax_url'] = add_query_arg('lang', $lang, $data['ajax_url']);
        }
        // Postavimo pravilan endpoint
        $data['wc_ajax_url'] = WC_AJAX::get_endpoint('%%endpoint%%');
    }
    return $data;
}
add_action('wp_enqueue_scripts', 'modify_checkout_scripts', 99);
function modify_checkout_scripts()
{
    if (!is_checkout()) return;

    wp_dequeue_script('wc-cart-fragments');

    wp_add_inline_script('wc-checkout', '
        jQuery(function($) {
            // Čekamo da se DOM učita
            var isProcessing = false;
            
            // Sprečavamo višestruke pozive
            $(document.body).on("update_checkout", function(event) {
                if (isProcessing) {
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }
                
                isProcessing = true;
                setTimeout(function() {
                    isProcessing = false;
                }, 3000);
            });

            // Popravljamo endpoint u URL-u
            $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
                if (options.url && options.url.indexOf("wc-ajax=%%endpoint%%") > -1) {
                    options.url = options.url.replace("%%endpoint%%", "update_order_review");
                }
                if (options.url && options.url.indexOf("%25%25endpoint%25%25") > -1) {
                    options.url = options.url.replace("%25%25endpoint%25%25", "update_order_review");
                }
            });

            // Održavamo prevode
            function updateTranslations() {
                $(".woocommerce-shipping-totals th").text("' . esc_js(pll__('Dostava')) . '");
                $(".cart-subtotal th").text("' . esc_js(pll__('Međuzbir')) . '");
                $(".order-total th").text("' . esc_js(pll__('Ukupno')) . '");
                $("#place_order").text("' . esc_js(pll__('Naruči')) . '");
            }

            $(document.body).on("updated_checkout", updateTranslations);
            updateTranslations();
        });
    ');
}
add_action('woocommerce_ajax_get_endpoint', 'add_language_to_ajax_endpoint', 10, 2);
function add_language_to_ajax_endpoint($url, $endpoint)
{
    if (function_exists('pll_current_language')) {
        $lang = pll_current_language();
        if (!empty($lang)) {
            $url = add_query_arg('lang', $lang, $url);
        }
    }
    return $url;
}
add_filter('woocommerce_update_order_review_fragments', 'maintain_translations_in_fragments', 999);
function maintain_translations_in_fragments($fragments)
{
    if (!function_exists('pll_current_language')) return $fragments;

    // Dodajemo prevedene stringove u fragmente
    $fragments['translations'] = [
        'shipping' => pll__('Dostava'),
        'subtotal' => pll__('Međuzbir'),
        'total' => pll__('Ukupno'),
        'place_order' => pll__('Naruči')
    ];

    return $fragments;
}
add_action('woocommerce_checkout_update_order_review', 'set_checkout_language_before_fragments', 1);
function set_checkout_language_before_fragments($post_data)
{
    parse_str($post_data, $data);
    if (isset($data['lang']) && function_exists('pll_current_language')) {
        PLL()->curlang = PLL()->model->get_language($data['lang']);
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


add_action('wp_footer', 'add_order_received_translations');
function add_order_received_translations()
{
    // Proveravamo da li smo na order-received stranici
    if (!is_wc_endpoint_url('order-received')) return;
?>
    <script type="text/javascript">
        jQuery(function($) {
            const variations_translations = {
                'Plan ishrane': '<?php echo esc_js(pll__("Plan ishrane")); ?>',
                'Plan': '<?php echo esc_js(pll__("Plan")); ?>',
                'Kalorije': '<?php echo esc_js(pll__("Kalorije")); ?>',
                'Izaberi paket': '<?php echo esc_js(pll__("Izaberi paket")); ?>',
                'Datum dostave': '<?php echo esc_js(pll__("Datum dostave")); ?>',
                'Nedeljni 6': '<?php echo esc_js(pll__("Nedeljni 6")); ?>',
                'Nedeljni 5': '<?php echo esc_js(pll__("Nedeljni 5")); ?>',
                'Mesečni 20': '<?php echo esc_js(pll__("Mesecni 20")); ?>',
                'Mesečni 24': '<?php echo esc_js(pll__("Mesecni 24")); ?>',
                'Dnevni': '<?php echo esc_js(pll__("Dnevni")); ?>',
                'Slim': '<?php echo esc_js(pll__("Slim")); ?>',
                'Fit': '<?php echo esc_js(pll__("Fit")); ?>',
                'Protein plus': '<?php echo esc_js(pll__("Protein")); ?>'
            };

            function translateText(text) {
                // Prvo tražimo tačne podudarnosti
                Object.keys(variations_translations).forEach(function(key) {
                    if (text.trim() === key) {
                        text = variations_translations[key];
                    }
                });

                // Zatim tražimo delimična podudaranja
                Object.keys(variations_translations).forEach(function(key) {
                    if (text.includes(key)) {
                        text = text.replace(key, variations_translations[key]);
                    }
                });
                return text;
            }

            function translateOrderDetails() {
                // Prevod naziva proizvoda
                $('.woocommerce-table__product-name').contents().filter(function() {
                    return this.nodeType === 3;
                }).each(function() {
                    this.nodeValue = translateText(this.nodeValue);
                });

                // Prevod varijacija
                $('.woocommerce-table__product-name .wc-item-meta li').each(function() {
                    const $item = $(this);

                    // Prevod labele
                    $item.find('strong').contents().filter(function() {
                        return this.nodeType === 3;
                    }).each(function() {
                        this.nodeValue = translateText(this.nodeValue);
                    });

                    // Prevod vrednosti
                    $item.find('p').contents().filter(function() {
                        return this.nodeType === 3;
                    }).each(function() {
                        this.nodeValue = translateText(this.nodeValue);
                    });
                });

                // Prevod zaglavlja tabele
                $('.woocommerce-table th').each(function() {
                    let $th = $(this);
                    $th.text(translateText($th.text()));
                });
            }

            // Pozivamo prevod odmah
            translateOrderDetails();
        });
    </script>
    <?php

    // Dodajemo CSS za dvotačke
    ?>
    <style>
        .woocommerce-table__product-name .wc-item-meta strong:after {
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

    // Dobavi ID stranice politike privatnosti za trenutni jezik
    $privacy_page_id = pll_get_post(get_option('wp_page_for_privacy_policy'), $current_lang);
    $privacy_url = get_permalink($privacy_page_id);

    // Različiti URL-ovi za različite jezike
    $urls = [
        'sr' => '/politika-privatnosti',
        'en' => '/en/privacy-policy',
        'ru' => '/ru/политика-конфиденциальности'  // prilagodi prema stvarnom URL-u
    ];

    $url = isset($urls[$current_lang]) ? $urls[$current_lang] : $urls['sr'];

    // Različiti tekstovi za različite jezike
    $texts = [
        'sr' => 'Pročitao/la sam i slažem se sa <a href="' . $url . '" target="_blank">uslovima korišćenja</a>',
        'en' => 'I have read and agree to the <a href="' . $url . '" target="_blank">terms of use</a>',
        'ru' => 'Я прочитал и согласен с <a href="' . $url . '" target="_blank">условия эксплуатации</a>'
    ];

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





// ========== DODAJ OVE FUNKCIJE U functions.php ==========

// 1. Uklanjamo postojeće kupon polje sa standardne pozicije
function remove_default_coupon_form()
{
    remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
}
add_action('init', 'remove_default_coupon_form');

// 2. Dodajemo novo kupon polje u order review sekciji, odmah pre payment metoda
function add_custom_coupon_field_to_checkout()
{
?>
    <div id="custom-coupon-section" class="custom-coupon-wrapper">
        <h4><?php echo pll__('Imate kupon?'); ?></h4>
        <div class="coupon-form-wrapper">
            <input type="text" name="coupon_code" id="coupon_code"
                placeholder="<?php echo esc_attr(pll__('Unesite kod kupona')); ?>"
                value="" class="input-text" />
            <button type="button" class="button apply-coupon" id="apply_coupon">
                <?php echo pll__('Primeni kupon'); ?>
            </button>
        </div>
        <div id="coupon-messages"></div>
    </div>
<?php
}
// Dodajemo polje u order review, odmah pre payment metoda
add_action('woocommerce_review_order_before_payment', 'add_custom_coupon_field_to_checkout');

// 3. AJAX handler za primenu kupona
function handle_apply_coupon()
{
    error_log('=== Apply coupon AJAX handler started ===');
    error_log('POST data: ' . print_r($_POST, true));

    // Koristimo postojeći WooCommerce nonce ali sa false parametrom
    if (!wp_verify_nonce($_POST['security'], 'update_order_review')) {
        error_log('Nonce check failed, trying alternative...');
        // Alternativni pristup - proveravamo da li je korisnik ulogovan ili dozvoljavamo bez nonce
        if (!is_user_logged_in() && !defined('DOING_AJAX')) {
            wp_send_json_error(array('message' => pll__('Bezbednosna provera nije uspela')));
            return;
        }
        // Ili jednostavno dozvoljavamo izvršavanje
        error_log('Proceeding without strict nonce check...');
    }

    $coupon_code = sanitize_text_field($_POST['coupon_code']);

    if (empty($coupon_code)) {
        wp_send_json_error(array('message' => pll__('Molimo unesite kod kupona')));
        return;
    }

    // Proveravamo da li kupon već postoji u korpi
    $applied_coupons = WC()->cart->get_applied_coupons();
    if (in_array(strtolower($coupon_code), array_map('strtolower', $applied_coupons))) {
        wp_send_json_error(array('message' => pll__('Kupon je već primenjen')));
        return;
    }

    // Primenjujemo kupon
    $result = WC()->cart->apply_coupon($coupon_code);

    if ($result) {
        // Preračunavamo totale
        WC()->cart->calculate_totals();

        wp_send_json_success(array(
            'message' => pll__('Kupon je uspešno primenjen'),
            'coupon_code' => $coupon_code
        ));
    } else {
        // Dobijamo poslednju grešku
        $notices = wc_get_notices('error');
        $error_message = !empty($notices) ? $notices[0]['notice'] : pll__('Kupon nije validan');
        wc_clear_notices();

        wp_send_json_error(array('message' => $error_message));
    }
}
add_action('wp_ajax_apply_coupon_checkout', 'handle_apply_coupon');
add_action('wp_ajax_nopriv_apply_coupon_checkout', 'handle_apply_coupon');

// 4. AJAX handler za uklanjanje kupona
function handle_remove_coupon()
{
    if (!wp_verify_nonce($_POST['security'], 'update_order_review')) {
        error_log('Remove coupon: Nonce check failed, proceeding anyway...');
    }
    if (!check_ajax_referer('woocommerce-checkout', 'security', false)) {
        wp_send_json_error(array('message' => pll__('Bezbednosna provera nije uspela')));
        return;
    }

    $coupon_code = sanitize_text_field($_POST['coupon_code']);

    if (empty($coupon_code)) {
        wp_send_json_error(array('message' => pll__('Kod kupona nije specificiran')));
        return;
    }

    // Uklanjamo kupon
    $result = WC()->cart->remove_coupon($coupon_code);

    if ($result) {
        WC()->cart->calculate_totals();

        wp_send_json_success(array(
            'message' => pll__('Kupon je uklonjen'),
            'fragments' => apply_filters('woocommerce_update_order_review_fragments', array())
        ));
    } else {
        wp_send_json_error(array('message' => pll__('Greška pri uklanjanju kupona')));
    }
}
add_action('wp_ajax_remove_coupon_checkout', 'handle_remove_coupon');
add_action('wp_ajax_nopriv_remove_coupon_checkout', 'handle_remove_coupon');

// 5. Dodajemo JavaScript i CSS za kupon funkcionalnost
function enqueue_checkout_coupon_assets()
{
    if (is_checkout()) {
        // CSS ostaje isti
        wp_add_inline_style('woocommerce-general', '
            .custom-coupon-wrapper {
             
                border-top: 1px solid rgba(0, 0, 0, .1);
                border-bottom: 1px solid rgba(0, 0, 0, .1);
              
                   padding: 15px 12px;
                margin-bottom: 25px;
             
            }
            
            .custom-coupon-wrapper h4 {
                margin: 0 0 15px 0;
                font-size: 16px;
                font-weight: 600;
                color: #333;
            }
            
            .coupon-form-wrapper {
                display: flex;
                gap: 12px;
                align-items: center;
            }
            
            .coupon-form-wrapper input[type="text"] {
                flex: 1;
              padding: 9px 12px;
                border: 1px solid rgba(0, 0, 0, .1);
             
                font-size: 14px;
                background: #fff;
             
            }
            
            .coupon-form-wrapper input[type="text"]:focus {
                outline: none;
                border-color: #0073aa;
                box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.1);
            }
            
            .coupon-form-wrapper .button.apply-coupon {
                padding: 12px 24px;
                   background-color: #000;
                color: white;
                border: none;
                border-radius: 3px;
                cursor: pointer;
                font-size: 14px;
                  font-weight: 700;
                transition: all 0.3s ease;
                white-space: nowrap;
            }
            
            // .coupon-form-wrapper .button.apply-coupon:hover:not(:disabled) {
            //     background: #005a87;
            //     transform: translateY(-1px);
            //     box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            // }
            .woocommerce button.button:hover{
background-color: #000;
   color: white;
}

            // .coupon-form-wrapper .button.apply-coupon:disabled {
            //     background: #ccc;
            //     cursor: not-allowed;
            //     transform: none;
            //     box-shadow: none;
            // }
            
            #coupon-messages {
                margin-top: 15px;
            }
            
            .coupon-success {
                color: #155724;
                background: #d4edda;
                border: 1px solid #c3e6cb;
                padding: 12px 15px;
                border-radius: 6px;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .coupon-success:before {
                content: "✓";
                font-weight: bold;
            }
            
            .coupon-error {
                color: #721c24;
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                padding: 12px 15px;
                border-radius: 6px;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .coupon-error:before {
                content: "⚠";
                font-weight: bold;
            }
            
            .applied-coupon {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: #d4edda;
                border: 1px solid #c3e6cb;
                padding: 12px 15px;
                border-radius: 6px;
                margin-top: 15px;
                color: #155724;
            }
            
            .applied-coupon .coupon-details {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .applied-coupon .coupon-details:before {
                content: "🏷";
            }
            
            .applied-coupon .remove-coupon {
                background: none;
                border: none;
                color: #dc3545;
                cursor: pointer;
                font-size: 12px;
                text-decoration: underline;
                padding: 4px 8px;
                border-radius: 4px;
                transition: background-color 0.2s ease;
            }
            
            // .applied-coupon .remove-coupon:hover {
            //     background: rgba(220, 53, 69, 0.1);
            //     text-decoration: none;
            // }
            
            @media (max-width: 768px) {
                .coupon-form-wrapper {
                    flex-direction: column;
                    align-items: stretch;
                }
                
                .coupon-form-wrapper .button {
                    margin-top: 10px;
                }
                
                .applied-coupon {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                }
                
                .applied-coupon .remove-coupon {
                    align-self: flex-end;
                }
            }
            
            .checkout-wrapper .order-review .custom-coupon-wrapper {
                margin-top: 0;
                margin-bottom: 20px;
            }
        ');

        // Novi JavaScript pristup sa pravilnim timing-om
        wp_add_inline_script('wc-checkout', '
        // Čekamo da se sve učita
        jQuery(window).on("load", function($) {
            initializeCouponFunctionality();
        });
        
        // Takođe pokušavamo odmah
        jQuery(document).ready(function($) {
            setTimeout(initializeCouponFunctionality, 1000);
        });
        
        function initializeCouponFunctionality() {
            var $ = jQuery;
            var isProcessing = false;
            
            console.log("Initializing coupon functionality");
            
            // Uklanjamo postojeće event listenere da izbegnemo duplikate
            $(document).off("click.coupon", "#apply_coupon");
            $(document).off("click.coupon", ".remove-coupon");
            $(document).off("keypress.coupon", "#coupon_code");
            
            // Apply coupon funkcionalnost
            $(document).on("click.coupon", "#apply_coupon", function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log("Apply coupon button clicked");
                
                if (isProcessing) {
                    console.log("Already processing, ignoring click");
                    return;
                }
                
                var couponCode = $("#coupon_code").val().trim();
                console.log("Coupon code:", couponCode);
                
                if (!couponCode) {
                    showCouponMessage("' . esc_js(pll__('Molimo unesite kod kupona')) . '", "error");
                    $("#coupon_code").focus();
                    return;
                }
                
                // Proveravamo da li wc_checkout_params postoji
                if (typeof wc_checkout_params === "undefined") {
                    console.error("wc_checkout_params is undefined");
                    showCouponMessage("Greška u konfiguraciji checkout stranice", "error");
                    return;
                }
                
                isProcessing = true;
                var $button = $(this);
                var originalText = $button.text();
                $button.prop("disabled", true).text("' . esc_js(pll__('Primenjujem...')) . '");
                
                $("#coupon-messages").html("<div style=\"padding: 8px; background: #f0f8ff; border: 1px solid #007cba; border-radius: 4px;\">⏳ Primenjujem kupon...</div>");
                
                $.ajax({
                    url: wc_checkout_params.ajax_url,
                    type: "POST",
                    data: {
                        action: "apply_coupon_checkout",
                        coupon_code: couponCode,
                     security: wc_checkout_params.update_order_review_nonce
                    },
                    success: function(response) {
                        console.log("AJAX Success:", response);
                        console.log("Response success property:", response.success);
console.log("Response data:", response.data);
                        if (response.success) {
                            showCouponMessage(response.data.message, "success");
                            $("#coupon_code").val("");
                            showAppliedCoupon(response.data.coupon_code || couponCode);
                            
                            // Trigger checkout update
                            $(document.body).trigger("update_checkout", [{
                                update_shipping_method: false
                            }]);
                        } else {
                            console.log("AJAX Error:", response.data);
                            showCouponMessage(response.data.message || "Greška pri primeni kupona", "error");
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        console.error("AJAX Request failed:", textStatus, errorThrown);
                        console.error("Response:", xhr.responseText);
                        showCouponMessage("Greška pri komunikaciji sa serverom", "error");
                    },
                    complete: function() {
                        isProcessing = false;
                        $button.prop("disabled", false).text(originalText);
                    }
                });
            });
            
            // Remove coupon funkcionalnost
            $(document).on("click.coupon", ".remove-coupon", function(e) {
                e.preventDefault();
                
                if (isProcessing) return;
                
                var couponCode = $(this).data("coupon");
                isProcessing = true;
                
                $(this).prop("disabled", true);
                $("#coupon-messages").html("<div style=\"padding: 8px; background: #f0f8ff; border: 1px solid #007cba; border-radius: 4px;\">⏳ Uklanjam kupon...</div>");
                
                $.ajax({
                    url: wc_checkout_params.ajax_url,
                    type: "POST",
                    data: {
                        action: "remove_coupon_checkout",
                        coupon_code: couponCode,
                   security: wc_checkout_params.update_order_review_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            showCouponMessage(response.data.message, "success");
                            $(".applied-coupon").remove();
                            
                            $(document.body).trigger("update_checkout", [{
                                update_shipping_method: false
                            }]);
                        } else {
                            showCouponMessage(response.data.message, "error");
                        }
                    },
                    error: function() {
                        showCouponMessage("Greška pri uklanjanju kupona", "error");
                    },
                    complete: function() {
                        isProcessing = false;
                    }
                });
            });
            
            // Enter key podrška
            $(document).on("keypress.coupon", "#coupon_code", function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $("#apply_coupon").click();
                }
            });
            
            // Helper functions
            function showCouponMessage(message, type) {
                var className = type === "success" ? "coupon-success" : "coupon-error";
                var html = "<div class=\"" + className + "\">" + message + "</div>";
                
                $("#coupon-messages").html(html);
                
                setTimeout(function() {
                    $("#coupon-messages").fadeOut(400, function() {
                        $(this).html("").show();
                    });
                }, 5000);
            }
            
            function showAppliedCoupon(couponCode) {
                var html = "<div class=\"applied-coupon\">" +
                           "<div class=\"coupon-details\">" +
                           "<span>' . esc_js(pll__('Primenjen kupon:')) . ' <strong>" + couponCode + "</strong></span>" +
                           "</div>" +
                           "<button type=\"button\" class=\"remove-coupon\" data-coupon=\"" + couponCode + "\">' . esc_js(pll__('Ukloni')) . '</button>" +
                           "</div>";
                
                $("#coupon-messages").html(html);
            }
            
            console.log("Coupon functionality initialized successfully");
        }
        ', 'after');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_checkout_coupon_assets', 150);

// 6. Registrujemo potrebne stringove za prevod (dodano više stringova)
add_action('init', function () {
    if (function_exists('pll_register_string')) {
        pll_register_string('Imate kupon?', 'Imate kupon?');
        pll_register_string('Unesite kod kupona', 'Unesite kod kupona');
        pll_register_string('Primeni kupon', 'Primeni kupon');
        pll_register_string('Primenjujem...', 'Primenjujem...');
        pll_register_string('Primenjujem kupon...', 'Primenjujem kupon...');
        pll_register_string('Uklanjam kupon...', 'Uklanjam kupon...');
        pll_register_string('Primenjen kupon:', 'Primenjen kupon:');
        pll_register_string('Ukloni', 'Ukloni');
        pll_register_string('Molimo unesite kod kupona', 'Molimo unesite kod kupona');
        pll_register_string('Kupon je već primenjen', 'Kupon je već primenjen');
        pll_register_string('Kupon je uspešno primenjen', 'Kupon je uspešno primenjen');
        pll_register_string('Kupon nije validan', 'Kupon nije validan');
        pll_register_string('Kod kupona nije specificiran', 'Kod kupona nije specificiran');
        pll_register_string('Kupon je uklonjen', 'Kupon je uklonjen');
        pll_register_string('Greška pri uklanjanju kupona', 'Greška pri uklanjanju kupona');
        pll_register_string('Došlo je do greške. Pokušajte ponovo.', 'Došlo je do greške. Pokušajte ponovo.');
        pll_register_string('Bezbednosna provera nije uspela', 'Bezbednosna provera nije uspela');
        pll_register_string('Kupon:', 'Kupon:');
    }
}, 100);

// 7. Dodatno: Prevodimo standardne WooCommerce kupon poruke
add_filter('woocommerce_coupon_error', 'translate_coupon_errors', 10, 3);
function translate_coupon_errors($err, $err_code, $coupon)
{
    // Mapa grešaka na srpski
    $error_translations = array(
        'Coupon code already applied!' => pll__('Kupon je već primenjen!'),
        'Coupon does not exist!' => pll__('Kupon ne postoji!'),
        'This coupon has expired.' => pll__('Ovaj kupon je istekao.'),
        'The minimum spend for this coupon is %s.' => pll__('Minimalna potrošnja za ovaj kupon je %s.'),
        'The maximum spend for this coupon is %s.' => pll__('Maksimalna potrošnja za ovaj kupon je %s.'),
        'This coupon is not valid for your cart contents.' => pll__('Ovaj kupon nije validan za sadržaj vaše korpe.'),
        'This coupon has reached its usage limit.' => pll__('Ovaj kupon je dostigao limit korišćenja.'),
    );

    // Ako imamo prevod, koristimo ga
    foreach ($error_translations as $english => $serbian) {
        if (strpos($err, str_replace('%s', '', $english)) !== false) {
            return str_replace($english, $serbian, $err);
        }
    }

    return $err;
}
function add_coupon_checkout_script_data()
{
    if (is_checkout()) {
        wp_localize_script('wc-checkout', 'coupon_checkout_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'coupon_nonce' => wp_create_nonce('coupon_checkout_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'add_coupon_checkout_script_data', 200);

?>