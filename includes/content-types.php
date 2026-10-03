<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    register_post_type('ohnisko_menu_item', [
        'labels' => [
            'name'          => 'Menu items',
            'singular_name' => 'Menu Item',
            'add_new_item'  => 'Add Menu Item',
            'edit_item'     => 'Edit Menu Item',
            'new_item'      => 'New Menu Item',
            'search_items'  => 'Search Menu',
            'not_found'     => 'No menu items found',
            'menu_name'     => 'Menu items',
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

});
