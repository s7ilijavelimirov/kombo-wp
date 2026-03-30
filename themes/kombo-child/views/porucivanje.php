<?php
/*
* Template Name: Porucivanje
*/

get_header();

$ordering_banner_slides = function_exists('get_field') ? get_field('ordering_page_banner_slides') : array();
$ordering_banner_single = function_exists('get_field') ? (string) get_field('ordering_page_banner_image') : '';

if (is_array($ordering_banner_slides) && !empty($ordering_banner_slides)) :
    $valid_banner_slides = array();
    foreach ($ordering_banner_slides as $slide) {
        $desktop_image = isset($slide['desktop_image']) ? (string) $slide['desktop_image'] : '';
        if ($desktop_image === '') {
            continue;
        }
        $mobile_image = isset($slide['mobile_image']) ? (string) $slide['mobile_image'] : '';
        $valid_banner_slides[] = array(
            'desktop' => $desktop_image,
            'mobile' => $mobile_image,
        );
    }
    $slide_count = count($valid_banner_slides);
    if ($slide_count > 0) :
        $banner_classes = 'km-ordering-page-banner';
        if ($slide_count > 1) {
            $banner_classes .= ' km-ordering-page-banner--slider';
        }
        ?>
        <div class="<?php echo esc_attr($banner_classes); ?>">
            <?php foreach ($valid_banner_slides as $slide_data) : ?>
                <div class="km-ordering-page-banner__slide">
                    <picture>
                        <?php if ($slide_data['mobile'] !== '') : ?>
                            <source media="(max-width: 768px)" srcset="<?php echo esc_url($slide_data['mobile']); ?>">
                        <?php endif; ?>
                        <img src="<?php echo esc_url($slide_data['desktop']); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
                    </picture>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php elseif ($ordering_banner_single !== '') : ?>
    <div class="km-ordering-page-banner">
        <img src="<?php echo esc_url($ordering_banner_single); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
    </div>
<?php endif; ?>

<script>
    (function () {
        function setOrderingBannerOffset() {
            var desktop = document.getElementById('desktop-menu');
            var mobile = document.getElementById('mobile-menu');
            var desktopVisible = desktop && window.getComputedStyle(desktop).display !== 'none';
            var mobileVisible = mobile && window.getComputedStyle(mobile).display !== 'none';
            var headerEl = desktopVisible ? desktop : (mobileVisible ? mobile : null);
            var headerHeight = headerEl ? headerEl.offsetHeight : 0;

            document.documentElement.style.setProperty('--km-header-offset', headerHeight + 'px');
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setOrderingBannerOffset);
        } else {
            setOrderingBannerOffset();
        }

        window.addEventListener('resize', setOrderingBannerOffset);
        window.addEventListener('load', setOrderingBannerOffset);
    })();
</script>

<script>
    (function ($) {
        $(function () {
            var $bannerSlider = $('.km-ordering-page-banner--slider');
            if ($bannerSlider.length && typeof $.fn.slick === 'function' && $bannerSlider.children().length > 1) {
                $bannerSlider.slick({
                    arrows: false,
                    dots: false,
                    infinite: true,
                    autoplay: true,
                    autoplaySpeed: 4500,
                    speed: 450,
                    slidesToShow: 1,
                    slidesToScroll: 1
                });
            }
        });
    })(jQuery);
</script>

<div class="forma-porucivanje">
    <div class="km-meal-plan-forms-column">
        <?php get_template_part('views/template-parts/meal-plan-form'); ?>
    </div>
    <?php get_template_part('views/template-parts/side-food-menu'); ?>
</div>

<?php get_footer(); ?>
