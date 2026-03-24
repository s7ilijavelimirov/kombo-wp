<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Performance optimizations -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://www.google-analytics.com">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//www.google-analytics.com">

    <title>
        <?php wp_title(''); ?>
    </title>

    <?php if (is_singular() && pings_open(get_queried_object())) : ?>
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <?php endif; ?>

    <script type="application/ld+json">
        {
            "@context": "http://schema.org",
            "@type": "Organization",
            "name": "<?php echo get_bloginfo('name'); ?>",
            "url": "<?php echo home_url(); ?>",
            "logo": "<?php echo get_theme_file_uri('path/to/logo.png'); ?>",
            "sameAs": [
                "https://www.instagram.com/kombo_healthymeals/"
            ]
        }
    </script>

    <!-- Google Analytics -->
    <?php if (!is_user_logged_in()) : ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=YOUR_TRACKING_ID"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', 'YOUR_TRACKING_ID', {
                'anonymize_ip': true
            });
        </script>
    <?php endif; ?>

    <?php wp_head(); ?>

    <script>
        const REST_API_URL = "<?php echo esc_url(get_rest_url()); ?>";
    </script>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>