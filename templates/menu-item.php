<?php

if (!defined('ABSPATH')) {
    exit;
}

$badge = ohnisko_menu_badge_label((string) ($item['badge'] ?? ''));
$price = ohnisko_menu_format_price($item);
$meta = ohnisko_menu_format_meta($item);
?>
<article class="menu-item menu-item--<?php echo esc_attr($variant); ?>">
    <header class="menu-item__header">
        <h3 class="menu-item__name"><?php echo esc_html((string) $item['name']); ?></h3>
        <?php if ($price !== '') : ?>
            <span class="menu-item__price"><?php echo esc_html($price); ?></span>
        <?php endif; ?>
    </header>
    <?php if ($meta !== '') : ?>
        <p class="menu-item__meta"><?php echo esc_html($meta); ?></p>
    <?php endif; ?>
    <?php if ($badge !== '') : ?>
        <span class="menu-item__badge"><?php echo esc_html($badge); ?></span>
    <?php endif; ?>
    <?php if ((int) ($item['spiciness'] ?? 0) > 0) : ?>
        <span class="menu-item__spiciness" aria-label="Spicy">
            <?php for ($i = 0; $i < min(8, (int) $item['spiciness']); $i++) : ?>
                <img src="<?php echo esc_url(OHNISKO_MENU_URL . 'assets/icon-chilli.svg'); ?>" alt="" aria-hidden="true">
            <?php endfor; ?>
        </span>
    <?php endif; ?>
    <?php foreach (['note', 'description', 'details'] as $field) : ?>
        <?php if (trim((string) ($item[$field] ?? '')) !== '') : ?>
            <p class="menu-item__<?php echo esc_attr($field); ?>"><?php echo nl2br(esc_html(trim((string) $item[$field]))); ?></p>
        <?php endif; ?>
    <?php endforeach; ?>
</article>
