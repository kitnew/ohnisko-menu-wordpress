<?php

if (!defined('ABSPATH')) {
    exit;
}


/*
 * --------------------------------------------------------------------------
 * Origin
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_format_origin(string $origin): string
{
    $origin = trim($origin);

    if ($origin === '') {
        return '';
    }

    return '/' . $origin . '/';
}


/*
 * --------------------------------------------------------------------------
 * Allergens
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_format_allergens(array $allergens): string
{
    if ($allergens === []) {
        return '';
    }

    $allergens = array_values(
        array_filter(
            array_map('intval', $allergens),
            static fn(int $value): bool => $value >= 1 && $value <= 14
        )
    );

    if ($allergens === []) {
        return '';
    }

    sort($allergens, SORT_NUMERIC);

    return '(' . implode(',', $allergens) . ')';
}


/*
 * --------------------------------------------------------------------------
 * Price
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_format_price(array $item): string
{
    $price = $item['price'] ?? [];

    $type = $price['type'] ?? 'hidden';

    switch ($type) {
        case 'fixed':
            if ($price['amount'] === null) {
                return '';
            }

            return number_format(
                (float) $price['amount'],
                2,
                '.',
                ''
            ) . '€';

        case 'per_unit':
            if (
                $price['amount'] === null
                || empty($price['unit'])
            ) {
                return '';
            }

            return trim((string) $price['unit'])
                . '/'
                . number_format(
                    (float) $price['amount'],
                    2,
                    '.',
                    ''
                )
                . '€';

        case 'custom':
            return trim(
                (string) ($price['custom'] ?? '')
            );

        case 'hidden':
        default:
            return '';
    }
}


/*
 * --------------------------------------------------------------------------
 * Metadata
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_get_meta_parts(array $item): array
{
    $origin = ohnisko_menu_format_origin(
        (string) ($item['origin'] ?? '')
    );

    $portion = trim(
        (string) ($item['portion'] ?? '')
    );

    $allergens = ohnisko_menu_format_allergens(
        $item['allergens'] ?? []
    );

    $order = $item['meta_order'] ?? 'default';

    if ($order === 'origin_allergens_portion') {
        $parts = [
            $origin,
            $allergens,
            $portion,
        ];
    } else {
        $parts = [
            $origin,
            $portion,
            $allergens,
        ];
    }

    return array_values(
        array_filter(
            $parts,
            static fn(string $part): bool => $part !== ''
        )
    );
}


function ohnisko_menu_format_meta(array $item): string
{
    return implode(
        ' ',
        ohnisko_menu_get_meta_parts($item)
    );
}


/*
 * --------------------------------------------------------------------------
 * Badge
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_badge_label(string $badge): string
{
    return match ($badge) {
        'vege' => 'VEGE',
        'vege_available' => 'AJ VEGE VERZIA',
        default => '',
    };
}


/*
 * --------------------------------------------------------------------------
 * Template loader
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_render_template(
    string $template,
    array $variables = []
): string {
    $template = basename($template);

    $path = dirname(__DIR__)
        . '/templates/'
        . $template
        . '.php';

    if (!is_file($path)) {
        return '';
    }

    extract(
        $variables,
        EXTR_SKIP
    );

    ob_start();

    require $path;

    return (string) ob_get_clean();
}


/*
 * --------------------------------------------------------------------------
 * Menu item
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_render_item(
    array $item,
    string $variant = 'standard'
): string {
    return ohnisko_menu_render_template(
        'menu-item',
        [
            'item' => $item,
            'variant' => $variant,
        ]
    );
}


/*
 * --------------------------------------------------------------------------
 * Section
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_render_section(
    string $section_key
): string {
    $sections = ohnisko_menu_sections();

    if (!isset($sections[$section_key])) {
        return '';
    }

    $section = $sections[$section_key];

    $items = ohnisko_menu_get_section_items($section_key);
    if ($items === []) {
        return '';
    }

    return ohnisko_menu_render_template(
        'menu-section',
        [
            'section_key' => $section_key,
            'section' => $section,
            'items' => $items,
        ]
    );
}
