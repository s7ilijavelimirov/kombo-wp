<?php

/**
 * Checkout: vidljivo polje za kupon + wc-ajax apply_coupon (WooCommerce core).
 *
 * @package Kombo_Child
 */

add_action(
    'init',
    static function () {
        remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
    },
    20
);

add_action('woocommerce_review_order_before_payment', 'kombo_child_checkout_coupon_field', 5);
function kombo_child_checkout_coupon_field()
{
    if (!function_exists('wc_coupons_enabled') || !wc_coupons_enabled()) {
        return;
    }
?>
    <div class="kombo-checkout-coupon">
        <h4 style="margin:0 0 12px;font-size:16px;font-weight:600;"><?php echo esc_html(pll__('Imate kupon?')); ?></h4>
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
            <label class="screen-reader-text" for="kombo_coupon_code"><?php echo esc_html(pll__('Kupon')); ?></label>
            <input type="text" id="kombo_coupon_code" class="input-text" style="flex:1;min-width:160px;padding:9px 12px;"
                placeholder="<?php echo esc_attr(pll__('Unesite kod kupona')); ?>" autocomplete="off" />
            <button type="button" class="button" id="kombo_apply_coupon" style="padding:10px 18px;"><?php echo esc_html(pll__('Primeni kupon')); ?></button>
        </div>
        <div id="kombo_coupon_message" class="kombo-coupon-message" style="margin-top:10px;font-size:14px;" aria-live="polite"></div>
    </div>
<?php
}

add_action('wp_enqueue_scripts', 'kombo_child_checkout_coupon_assets', 150);
function kombo_child_checkout_coupon_assets()
{
    if (!is_checkout()) {
        return;
    }
    wp_add_inline_style(
        'woocommerce-general',
        '.kombo-checkout-coupon{margin:0 0 20px;padding:15px 0;border-top:1px solid rgba(0,0,0,.1);border-bottom:1px solid rgba(0,0,0,.1)}.kombo-coupon-ok{color:#1e4620}.kombo-coupon-err{color:#b32d2e}'
    );
    wp_add_inline_script('wc-checkout', kombo_child_checkout_coupon_inline_js(), 'after');
}

/**
 * @return string
 */
function kombo_child_checkout_coupon_inline_js()
{
    $i18n = wp_json_encode(
        array(
            'empty' => pll__('Unesite kod kupona.'),
            'waiting' => pll__('Primenjujem…'),
            'invalid' => pll__('Kupon nije važeći.'),
            'comm' => pll__('Greška u vezi sa serverom.'),
        ),
        JSON_UNESCAPED_UNICODE
    );

    return 'var komboCouponL10n=' . $i18n . ';
jQuery(function($){
if(typeof wc_checkout_params==="undefined")return;
function ep(u){return wc_checkout_params.wc_ajax_url.toString().replace("%%endpoint%%",u);}
$("#kombo_apply_coupon").on("click",function(){
var c=($("#kombo_coupon_code").val()||"").trim(),$b=$(this);
if(!c){$("#kombo_coupon_message").html("<span class=kombo-coupon-err>"+komboCouponL10n.empty+"</span>");return;}
$b.prop("disabled",true);
$("#kombo_coupon_message").html("<span class=kombo-coupon-ok>"+komboCouponL10n.waiting+"</span>");
$.ajax({type:"POST",url:ep("apply_coupon"),dataType:"html",data:{
security:wc_checkout_params.apply_coupon_nonce,coupon_code:c,
billing_email:($("form.checkout").find("[name=billing_email]").val()||"")}})
.done(function(r){
var bad=r.indexOf("woocommerce-error")!==-1,$w=$("<div>").append($.parseHTML(r)),
m=$w.find(".woocommerce-error li").first().text().trim()||$w.find(".woocommerce-error").first().text().trim()||$w.find(".woocommerce-message").first().text().trim();
if(bad&&!m)m=komboCouponL10n.invalid;
var lo=(m||"").toLowerCase(),ok=!bad||(lo.indexOf("already applied")!==-1||lo.indexOf("već primenjen")!==-1||lo.indexOf("vec primenjen")!==-1);
$("#kombo_coupon_message").html("<span class="+(ok?"kombo-coupon-ok":"kombo-coupon-err")+">"+$("<span>").text(m).html()+"</span>");
if(ok){$("#kombo_coupon_code").val("");$(document.body).trigger("update_checkout",{update_shipping_method:false});}})
.fail(function(){$("#kombo_coupon_message").html("<span class=kombo-coupon-err>"+komboCouponL10n.comm+"</span>");})
.always(function(){$b.prop("disabled",false);});
});
});';
}

add_filter('woocommerce_coupon_error', 'translate_coupon_errors', 10, 3);
function translate_coupon_errors($err, $err_code, $coupon)
{
    $error_translations = array(
        'Coupon code already applied!' => pll__('Kupon je već primenjen!'),
        'Coupon does not exist!' => pll__('Kupon ne postoji!'),
        'This coupon has expired.' => pll__('Ovaj kupon je istekao.'),
        'The minimum spend for this coupon is %s.' => pll__('Minimalna potrošnja za ovaj kupon je %s.'),
        'The maximum spend for this coupon is %s.' => pll__('Maksimalna potrošnja za ovaj kupon je %s.'),
        'This coupon is not valid for your cart contents.' => pll__('Ovaj kupon nije validan za sadržaj vaše korpe.'),
        'This coupon has reached its usage limit.' => pll__('Ovaj kupon je dostigao limit korišćenja.'),
    );

    foreach ($error_translations as $english => $serbian) {
        if (strpos($err, str_replace('%s', '', $english)) !== false) {
            return str_replace($english, $serbian, $err);
        }
    }

    return $err;
}
