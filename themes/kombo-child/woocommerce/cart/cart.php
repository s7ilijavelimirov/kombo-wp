<?php

/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.1
 */

defined( 'ABSPATH' ) || exit;

do_action('woocommerce_before_cart'); ?>

<div class="cart-wrapper">

    <div class="form-wrapper">
        <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
            <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
            <?php do_action('woocommerce_before_cart_table'); ?>
            <?php do_action('woocommerce_before_cart_contents'); ?>

            <?php
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                /**
                 * Filter the product name.
                 *
                 * @since 2.1.0
                 * @param string $product_name Name of the product in the cart.
                 * @param array $cart_item The product in the cart.
                 * @param string $cart_item_key Key for the product in the cart.
                 */
                $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);

                if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                    $package_type = isset($cart_item['package_type']) ? (string) $cart_item['package_type'] : '';
                    ob_start();
                    if (isset($cart_item['delivery_dates'])) {
                        if (is_array($cart_item['delivery_dates'])) {
                            $grouped_dates = [];
                            foreach ($cart_item['delivery_dates'] as $date) {
                                $date_obj = DateTime::createFromFormat('d-m-Y', $date);
                                if ($date_obj) {
                                    $month_key = $date_obj->format('n-Y');
                                    if (!isset($grouped_dates[$month_key])) {
                                        $grouped_dates[$month_key] = [];
                                    }
                                    $grouped_dates[$month_key][] = intval($date_obj->format('d'));
                                }
                            }
                            $output = [];
                            foreach ($grouped_dates as $month_key => $days) {
                                list($month, $year) = explode('-', $month_key);
                                $month_name = [
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
                                ][intval($month) - 1];
                                sort($days);
                                $output[] = implode('., ', $days) . '. ' . $month_name;
                            }
                            echo implode(' ; ', $output);
                        } else {
                            $date_obj = DateTime::createFromFormat('d-m-Y', $cart_item['delivery_dates']);
                            if ($date_obj) {
                                $month_name = [
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
                                ][$date_obj->format('n') - 1];
                                echo $date_obj->format('d. ') . $month_name;
                            } else {
                                echo esc_html($cart_item['delivery_dates']);
                            }
                        }
                    }
                    $cart_date_display_text = trim(ob_get_clean());
                    $cart_has_dates       = $cart_date_display_text !== '';
                    if (!$cart_has_dates) {
                        $cart_date_display_text = pll__('Nije odabran datum');
                    }
                    $cart_delivery_input_id = 'cart-delivery-' . $cart_item_key;
            ?>
                    <div class="woocommerce-cart-form__cart-item product-wrapper <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" data-package-type="<?php echo esc_attr($package_type); ?>">
                        <div class="product-header">

                            <div class="product-name">
                                <?php
                                echo wp_kses_post($product_name);

                                do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

                                if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                    echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
                                }
                                ?>
                            </div>
                            <div class="product-price">
                                <span><?php echo pll_ru('Cena', 'Цена') ?>:</span>
                                <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                            </div>
                        </div>
                        <div class="product-body">
                            <div class="product-date">
                                <div class="label">
                                    <?php echo pll_ru('Datum za naručivanje', 'Дата заказа'); ?>:
                                </div>
                                <div class="date" style="cursor: pointer;" role="button" tabindex="0">
                                    <input type="text" id="<?php echo esc_attr($cart_delivery_input_id); ?>" class="cart-datepicker datepicker" readonly style="display: none;" autocomplete="off">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M6 5V1M14 5V1M5 9H15M3 19H17C17.5304 19 18.0391 18.7893 18.4142 18.4142C18.7893 18.0391 19 17.5304 19 17V5C19 4.46957 18.7893 3.96086 18.4142 3.58579C18.0391 3.21071 17.5304 3 17 3H3C2.46957 3 1.96086 3.21071 1.58579 3.58579C1.21071 3.96086 1 4.46957 1 5V17C1 17.5304 1.21071 18.0391 1.58579 18.4142C1.96086 18.7893 2.46957 19 3 19Z" stroke="#6E6E6E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="date-display<?php echo $cart_has_dates ? ' has-date' : ''; ?>"><?php echo esc_html($cart_date_display_text); ?></span>
                                </div>
                            </div>
                            <div class="product-quantity">
                                <div class="label"><?php echo pll_ru('Količina', 'Количество') ?>:</div>
                                <?php
                                if ( $_product->is_sold_individually() ) {
                                    $min_quantity = 1;
                                    $max_quantity = 1;
                                } else {
                                    $min_quantity = 0;
                                    $max_quantity = $_product->get_max_purchase_quantity();
                                }

                                $product_quantity = woocommerce_quantity_input(
                                    array(
                                        'input_name'   => "cart[{$cart_item_key}][qty]",
                                        'input_value'  => $cart_item['quantity'],
                                        'max_value'    => $max_quantity,
                                        'min_value'    => $min_quantity,
                                        'product_name' => $product_name,
                                    ),
                                    $_product,
                                    true
                                );

                                echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
                                ?>
                            </div>
                            <div class="product-remove">
                                <?php
                                echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    'woocommerce_cart_item_remove_link',
                                    sprintf(
                                        '<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart_item_key="%s">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="18" viewBox="0 0 14 18" fill="none">
                                        <path d="M0.999832 15.9999C0.999821 17.0999 1.89981 17.9999 2.9998 17.9999L10.9997 18C12.0997 18 12.9997 17.1 12.9997 16L12.9999 4.0001L0.999952 3.99998L0.999832 15.9999ZM13.9999 1.00013L10.4999 1.0001L9.49993 9.49701e-05L4.49997 4.49859e-05L3.49996 1.00003L-9.99685e-06 0.999992L-2.99906e-05 2.99998L13.9999 3.00012L13.9999 1.00013Z" fill="#0E0E0E"/>
                                    </svg>
                                </a>',
                                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                        esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
                                        esc_attr( $product_id ),
                                        esc_attr( $_product->get_sku() ),
                                        esc_attr( $cart_item_key )
                                    ),
                                    $cart_item_key
                                );
                                ?>
                            </div>
                        </div>
                        <div class="cart-actions">
                            <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
                        </div>
                    </div>
            <?php
                }
            }
            ?>
            <?php do_action('woocommerce_cart_contents'); ?>
        </form>
    </div>


    <div class="summary-wrapper">
        <p class="delivery-info"><?php echo pll_ru('Dostava dostupna samo za Beograd', 'Доставка осуществляется только в Белград.') ?></p>
        <div class="order-total shipping">

            <h3><?php echo pll_ru('Dostava', 'Доставка') ?>: </h3>
            <p>0 <?php echo pll_ru('RSD', 'рсд') ?></p>
        </div>

        <div class="order-total">
            <h3><?php echo pll_ru('Ukupna cena', 'Общая цена') ?>: </h3>
            <p><?php echo WC()->cart->get_total(); ?></p> <!-- Total price from the cart -->
        </div>

        <div class="button-wrapper">
            <a
                href="<?php echo esc_url(function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/')); ?>"
                class="button-main">
                <span><?php echo pll_ru('Kupi', 'Купить'); ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="32px" height="32px" viewBox="0 0 16 16" fill="#fff"
                    class="bi bi-arrow-right-short">
                    <path fill-rule="evenodd"
                        d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z" />
                </svg>
            </a>
        </div>
    </div>

</div>

<?php do_action('woocommerce_before_cart_collaterals'); ?>

<!-- <div class="cart-collaterals">
    <?php
    /**
     * Cart collaterals hook.
     *
     * @hooked woocommerce_cross_sell_display
     * @hooked woocommerce_cart_totals - 10
     */
    do_action('woocommerce_cart_collaterals');
    ?>
</div> -->

<?php do_action('woocommerce_after_cart'); ?>

<?php
// Check if the form is submitted
if (isset($_POST['coupon_code']) && !empty($_POST['coupon_code'])) {
    $coupon_code = sanitize_text_field($_POST['coupon_code']); // Sanitize the input
    if (WC()->cart->apply_coupon($coupon_code)) {
        // Coupon applied successfully
        wc_print_notices(); // Display WooCommerce notices (e.g., success message)
    } else {
        // If coupon is not valid, you can display an error message
        echo '<p class="error">Kupon nije validan.</p>';
    }
}
?>


<style>
    .woocommerce-message {

        display: none;
    }
</style>