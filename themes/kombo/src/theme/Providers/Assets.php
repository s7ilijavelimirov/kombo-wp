<?php

namespace WpTheme\Providers;

class Assets
{
    static function Init()
    {
        self::RegisterAssets();
        self::EnqueueAssets();
    }

    static function RegisterAssets()
    {
        $style_version = md5(filemtime(WPTHEME_PATH . "/public/css/style.css"));
        wp_register_style("wptheme-frontend", WPTHEME_URL . "/public/css/style.css", [], $style_version, "all");

        // Register FontAwesome
        wp_register_style("font-awesome", WPTHEME_URL . "'/node_modules/@fortawesome/fontawesome-free/css/all.min.css'", [], null, "all");

        $script_version = md5(filemtime(WPTHEME_PATH . "/public/js/script.js"));
        wp_register_script("wptheme-frontend", WPTHEME_URL . "/public/js/script.js", [], $script_version, true);
    }

    static function EnqueueAssets()
    {
        wp_enqueue_style("wptheme-frontend");
        wp_enqueue_style("font-awesome"); // Enqueue FontAwesome

        wp_enqueue_script("wptheme-frontend");
    }
}
