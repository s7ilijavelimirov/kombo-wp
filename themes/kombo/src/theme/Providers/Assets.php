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
        $style_path = WPTHEME_PATH . "/public/css/style.css";
        $style_version = (file_exists($style_path) && is_readable($style_path))
            ? md5((string) filemtime($style_path))
            : md5(WPTHEME_VERSION);
        wp_register_style("wptheme-frontend", WPTHEME_URL . "/public/css/style.css", [], $style_version, "all");

        // Register Font Awesome when package is present (path was broken: stray quotes in URL).
        $fa_rel = "/node_modules/@fortawesome/fontawesome-free/css/all.min.css";
        $fa_abs = WPTHEME_PATH . $fa_rel;
        if (file_exists($fa_abs) && is_readable($fa_abs)) {
            wp_register_style("font-awesome", WPTHEME_URL . $fa_rel, [], (string) filemtime($fa_abs), "all");
        }

        $script_path = WPTHEME_PATH . "/public/js/script.js";
        $script_version = (file_exists($script_path) && is_readable($script_path))
            ? md5((string) filemtime($script_path))
            : md5(WPTHEME_VERSION);
        wp_register_script("wptheme-frontend", WPTHEME_URL . "/public/js/script.js", array(), $script_version, true);
    }

    static function EnqueueAssets()
    {
        wp_enqueue_style("wptheme-frontend");
        if (wp_style_is("font-awesome", "registered")) {
            wp_enqueue_style("font-awesome");
        }

        wp_enqueue_script("wptheme-frontend");
    }
}
