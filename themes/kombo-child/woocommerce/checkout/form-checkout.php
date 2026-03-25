<?php

/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}

?>





<form name="checkout" method="post" class="checkout woocommerce-checkout container" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__('Checkout', 'woocommerce'); ?>">
    <?php if (function_exists('pll_current_language')):
        $current_lang = pll_current_language();
        // Dobavi ID stranice politike privatnosti za trenutni jezik
        $privacy_page_id = pll_get_post(get_option('wp_page_for_privacy_policy'), $current_lang);
    ?>
        <input type="hidden" name="lang" value="<?php echo esc_attr($current_lang); ?>">
        <input type="hidden" id="privacy_page_url" value="<?php echo get_permalink($privacy_page_id); ?>">
        <script type="text/javascript">
            var wc_checkout_current_lang = '<?php echo esc_js($current_lang); ?>';

            // Dodaj event listener za link politike privatnosti
            document.addEventListener('DOMContentLoaded', function() {
                const privacyLink = document.querySelector('.woocommerce-privacy-policy-link');
                if (privacyLink) {
                    privacyLink.href = document.getElementById('privacy_page_url').value;
                }
            });
        </script>
    <?php endif; ?>
    <?php if ($checkout->get_checkout_fields()) : ?>

        <?php do_action('woocommerce_checkout_before_customer_details'); ?>

        <div class="checkout-wrapper">
            <div class="customer-details">
                <h4><?php echo pll_ru('Dostava i plaćanje', 'Доставка и оплата') ?></h4>
                <div class="col2-set" id="customer_details">
                    <div class="col-1">
                        <?php do_action('woocommerce_checkout_billing'); ?>
                    </div>



                    <div class="col-2 hidden">
                        <?php do_action('woocommerce_checkout_shipping'); ?>
                    </div>
                </div>
            </div>

            <div class="order-review">
                <h3 id="order_review_heading"><?php echo pll_ru('Vaša narudžbina', 'Ваш заказ'); ?></h3>
                <div id="order_review" class="woocommerce-checkout-review-order">
                    <?php do_action('woocommerce_checkout_order_review'); ?>
                </div>
            </div>
        </div>

        <?php do_action('woocommerce_checkout_after_customer_details'); ?>

    <?php endif; ?>

    <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>



    <?php do_action('woocommerce_checkout_before_order_review'); ?>



    <?php do_action('woocommerce_checkout_after_order_review'); ?>

</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>




<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('ship-to-different-address-checkbox');
        const shippingSection = document.querySelector('.col-2');
        if (!checkbox || !shippingSection) {
            return;
        }

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                shippingSection.style.display = 'block';
                shippingSection.classList.remove('hidden');
            } else {
                shippingSection.style.display = 'none';
                shippingSection.classList.add('hidden');
            }
        });
    });
</script>