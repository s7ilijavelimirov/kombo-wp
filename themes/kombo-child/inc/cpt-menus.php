<?php

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

function kombo_flush_side_menu_transients($post_id)
{
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }
    $pt = get_post_type($post_id);
    $menu_types = array('weekly_menu', 'vege_menu', 'next_weekly_menu', 'next_vege_menu');
    if (!in_array($pt, $menu_types, true)) {
        return;
    }
    if (function_exists('pll_languages_list')) {
        foreach (pll_languages_list(array('fields' => 'slug')) as $slug) {
            delete_transient('kombo_side_menu_' . $slug);
        }
        return;
    }
    foreach (array('sr', 'en', 'ru', '') as $slug) {
        delete_transient('kombo_side_menu_' . $slug);
    }
}
add_action('save_post', 'kombo_flush_side_menu_transients', 20);