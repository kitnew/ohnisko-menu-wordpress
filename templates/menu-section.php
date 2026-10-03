<?php

if (!defined('ABSPATH')) {
    exit;
}

$title = (string) ($section['title'] ?? '');
$subtitle = (string) ($section['subtitle'] ?? '');
$intro = (string) ($section['intro'] ?? '');
$shared_price = (string) ($section['shared_price'] ?? '');
$columns = in_array($section_key, ['wine_beer_snacks', 'small_dishes', 'bbq'], true)
    ? ' menu-items--columns'
    : '';
?>
<section class="menu-section menu-section--<?php echo esc_attr($section_key); ?>">
    <header class="menu-section__header">
        <h2><?php echo esc_html($title); ?></h2>
        <?php if ($subtitle !== '') : ?>
            <p class="menu-section__subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
        <?php if ($intro !== '') : ?>
            <p class="menu-section__intro"><?php echo esc_html($intro); ?></p>
        <?php endif; ?>
        <?php if ($shared_price !== '') : ?>
            <p class="menu-section__shared-price"><?php echo esc_html($shared_price); ?></p>
        <?php endif; ?>
    </header>

    <div class="menu-items<?php echo esc_attr($columns); ?>">
        <?php foreach ($items as $item) : ?>
            <?php echo ohnisko_menu_render_item($item, (string) ($section['item_variant'] ?? 'standard')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php endforeach; ?>
    </div>
</section>
