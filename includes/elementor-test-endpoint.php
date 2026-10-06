<?php

if (!defined('ABSPATH')) exit;

add_action('template_redirect', function (): void {
    if (!isset($_GET['ohnisko_elementor_test'])) return;

    $expected = ohnisko_menu_print_token();
    $token = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : '';
    if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
        status_header(403);
        nocache_headers();
        exit('Forbidden');
    }

    $id = isset($_GET['ohnisko_elementor_id']) ? absint($_GET['ohnisko_elementor_id']) : 0;
    $source = $id ? get_post($id) : null;
    if (!$source || $source->post_type !== 'page' || !in_array($source->post_status, ['publish', 'private'], true)
        || !class_exists('\\Elementor\\Plugin') || get_post_meta($id, '_elementor_edit_mode', true) !== 'builder') {
        status_header(404);
        nocache_headers();
        exit('Elementor page not found');
    }

    global $post;
    $original_post = $post;
    $post = null; // Elementor rejects rendering when get_the_ID() equals the requested document.
    $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($id, true);
    $post = $original_post;
    if ($content === '') {
        status_header(422);
        nocache_headers();
        exit('Elementor page has no rendered content');
    }

    // The token route is not the queried Elementor page, so core skips its normal frontend CSS hook.
    add_action('wp_enqueue_scripts', [\Elementor\Plugin::$instance->frontend, 'enqueue_styles'], 6);

    status_header(200);
    nocache_headers();
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow', true);
    header('Referrer-Policy: no-referrer', true);
    ?>
    <!doctype html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo esc_html(get_the_title($id)); ?></title>
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(['elementor-template-canvas', 'page-template-elementor_canvas', 'elementor-page', 'elementor-page-' . $id]); ?>>
        <?php wp_body_open(); ?>
        <?php echo $content; // Elementor frontend renders the saved page HTML. ?>
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
    exit;
});
