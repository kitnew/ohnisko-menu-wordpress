<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    register_post_type('ohnisko_menu_item', [
        'labels' => [
            'name'          => 'Menu',
            'singular_name' => 'Menu Item',
            'add_new_item'  => 'Add Menu Item',
            'edit_item'     => 'Edit Menu Item',
            'new_item'      => 'New Menu Item',
            'search_items'  => 'Search Menu',
            'not_found'     => 'No menu items found',
            'menu_name'     => 'Menu',
        ],
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => false,
        'menu_icon'          => 'dashicons-food',
        'supports'           => ['title'],
        'has_archive'        => false,
        'rewrite'            => false,
        'query_var'          => false,
        'publicly_queryable' => false,
    ]);

    register_taxonomy('ohnisko_menu_section', ['ohnisko_menu_item'], [
        'labels' => [
            'name'          => 'Menu Sections',
            'singular_name' => 'Menu Section',
            'menu_name'     => 'Sections',
        ],
        'public'            => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => false,
        'hierarchical'      => true,
        'rewrite'           => false,
    ]);
});
