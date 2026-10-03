<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', function () {
    if (isset($_GET['ohnisko_menu'])) {
        $current = get_option('ohnisko_menu_current_pdf', []);
        $id = is_array($current) ? (string) ($current['job_id'] ?? '') : '';
        $job_file = ohnisko_menu_runtime_dir() . '/jobs/' . $id . '.json';
        $job = preg_match('/^[a-f0-9]{32}$/', $id) && is_file($job_file)
            ? json_decode((string) file_get_contents($job_file), true)
            : null;
        $pdf = is_array($job) && ($job['status'] ?? '') === 'ready'
            ? ohnisko_menu_runtime_dir() . '/generated/' . basename((string) ($job['filename'] ?? ''))
            : '';
        if ($pdf === '' || !is_file($pdf)) {
            status_header(404);
            nocache_headers();
            exit('No published menu');
        }
        nocache_headers();
        header('Content-Type: application/pdf');
        header('Content-Length: ' . (string) filesize($pdf));
        header('Content-Disposition: inline; filename="' . basename($pdf) . '"');
        readfile($pdf);
        exit;
    }

    if (!isset($_GET['ohnisko_print'])) {
        return;
    }

    $expected_token = (string) get_option('ohnisko_menu_print_token', '');
    $provided_token = isset($_GET['token'])
        ? sanitize_text_field(wp_unslash($_GET['token']))
        : '';

    if (
        $expected_token === '' ||
        $provided_token === '' ||
        !hash_equals($expected_token, $provided_token)
    ) {
        status_header(403);
        nocache_headers();
        exit('Forbidden');
    }

    nocache_headers();
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Robots-Tag: noindex, nofollow', true);

    $static = ohnisko_menu_static_blocks();
    $print_css = OHNISKO_MENU_DIR . 'assets/print.css';
    $print_js = OHNISKO_MENU_DIR . 'assets/print.js';
    ?>
    <!doctype html>
    <html lang="sk">
    <head>
        <meta charset="utf-8">
        <meta name="robots" content="noindex,nofollow">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ohnisko Menu</title>
        <link rel="preload" as="image" href="<?php echo esc_url(OHNISKO_MENU_URL . 'assets/brand-pattern.png'); ?>" data-print-critical="1">
        <link rel="stylesheet" href="<?php echo esc_url(OHNISKO_MENU_URL . 'assets/print.css?ver=' . (is_file($print_css) ? filemtime($print_css) : '1')); ?>">
        <script defer src="<?php echo esc_url(OHNISKO_MENU_URL . 'assets/print.js?ver=' . (is_file($print_js) ? filemtime($print_js) : '1')); ?>"></script>
        <script defer src="<?php echo esc_url(OHNISKO_MENU_URL . 'assets/vendor/paged.polyfill.min.js'); ?>"></script>
    </head>
    <body>
        <main class="menu-document">
            <header class="menu-intro">
                <img class="menu-brand" src="<?php echo esc_url(OHNISKO_MENU_URL . 'assets/ohnisko-logo-color.png'); ?>" alt="Ohnisko — Fire Dining & Brew Bar">
                <p class="menu-subbrand">JEDÁLNY LÍSTOK</p>
                <h1><?php echo esc_html($static['intro']['title']); ?></h1>
                <p><?php echo nl2br(esc_html($static['intro']['text'])); ?></p>
            </header>

            <?php foreach (['wine_beer_snacks', 'small_dishes'] as $section_key) : ?>
                <?php echo ohnisko_menu_render_section($section_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endforeach; ?>

            <section class="menu-josper">
                <h2>JOSPER GRILL COMBO</h2>
                <p class="menu-section__subtitle">
                    Jedna príloha a jedno maslo/omáčka sú zdarma pri objednaní mäsa z tejto kategórie.
                </p>
                <div class="menu-josper__grid">
                    <?php foreach (['josper_beef', 'josper_rest', 'josper_sides', 'josper_sauces'] as $section_key) : ?>
                        <?php echo ohnisko_menu_render_section($section_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endforeach; ?>
                </div>
            </section>

            <?php echo ohnisko_menu_render_section('bbq'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php echo ohnisko_menu_render_section('sandwich'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <?php $order_only = $static['order_only']; ?>
            <section class="menu-static menu-order-only">
                <h2><?php echo esc_html($order_only['title']); ?></h2>
                <p class="menu-section__subtitle"><?php echo esc_html($order_only['subtitle']); ?></p>
                <p><?php echo esc_html(implode(' · ', $order_only['items'])); ?></p>
                <p><?php echo esc_html($order_only['text']); ?></p>
            </section>

            <?php echo ohnisko_menu_render_section('desserts_cheese'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

            <footer class="menu-footer">
                <p class="menu-footer__chef">
                    Šéfkuchár: <?php echo esc_html($static['chef_credit']['name']); ?>
                </p>
                <section class="menu-static menu-dog-menu">
                    <h2><?php echo esc_html($static['dog_menu']['title']); ?></h2>
                    <h3><?php echo esc_html($static['dog_menu']['product_name']); ?></h3>
                    <p><?php echo esc_html($static['dog_menu']['description']); ?></p>
                    <p><?php echo esc_html($static['dog_menu']['promo']); ?></p>
                </section>
                <section class="menu-allergens" aria-label="Alergény">
                    <h2>Alergény</h2>
                    <p>1. Obilniny obsahujúce lepok · 2. Kôrovce · 3. Vajcia · 4. Ryby · 5. Arašidy · 6. Sója · 7. Mlieko · 8. Orechy · 9. Zeler · 10. Horčica · 11. Sezam · 12. Oxid siričitý a siričitany · 13. Vlčí bôb · 14. Mäkkýše.</p>
                    <p>Váha mäsa je uvádzaná v surovom stave. Neodporúča sa konzumovať tepelne nespracované mäso a vajcia deťom, tehotným a dojčiacim ženám a ľuďom s oslabenou imunitou.</p>
                </section>
                <section class="menu-review">
                    <p>VERÍME, ŽE SA TI U NÁS PÁČILO. BUDEME RADI, AK SI NÁJDEŠ CHVÍĽKU A NECHÁŠ NÁM TVOJU RECENZIU. ĎAKUJEME ZA NÁVŠTEVU.</p>
                    <p>www.ohnisko.com · @ohnisko_ke · @Ohnisko.ke</p>
                </section>
            </footer>
        </main>
    </body>
    </html>
    <?php
    exit;
});

add_action('admin_post_ohnisko_menu_pdf', function (): void {
    if (!current_user_can('manage_options')) wp_die('Forbidden', '', ['response' => 403]);
    $id = isset($_GET['job']) ? sanitize_text_field(wp_unslash($_GET['job'])) : '';
    check_admin_referer('ohnisko_menu_pdf_' . $id);
    if (!preg_match('/^[a-f0-9]{32}$/', $id)) wp_die('Invalid version', '', ['response' => 404]);
    $job_file = ohnisko_menu_runtime_dir() . '/jobs/' . $id . '.json';
    $job = is_file($job_file) ? json_decode((string) file_get_contents($job_file), true) : null;
    if (!is_array($job) || ($job['status'] ?? '') !== 'ready') wp_die('Version is not ready', '', ['response' => 404]);
    $filename = basename((string) ($job['filename'] ?? ''));
    if ($filename === '' || !preg_match('/^[A-Za-z0-9._-]+\.pdf$/i', $filename)) wp_die('Invalid version', '', ['response' => 404]);
    $pdf = ohnisko_menu_runtime_dir() . '/generated/' . $filename;
    if (!is_file($pdf)) wp_die('PDF is missing', '', ['response' => 404]);
    nocache_headers();
    header('Content-Type: application/pdf');
    header('Content-Length: ' . (string) filesize($pdf));
    $download = isset($_GET['download']) && $_GET['download'] === '1';
    header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"');
    readfile($pdf);
    exit;
});
