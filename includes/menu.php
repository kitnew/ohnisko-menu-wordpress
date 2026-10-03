<?php

if (!defined('ABSPATH')) {
    exit;
}

/*
 * --------------------------------------------------------------------------
 * Shared definitions
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_origin_choices(): array
{
    return [
        'SVK' => 'SVK — Slovensko',
        'ARG' => 'ARG — Argentína',
        'NZ'  => 'NZ — Nový Zéland',
        'NL'  => 'NL — Holandsko',
        'USA' => 'USA — Spojené štáty',
        'JPN' => 'JPN — Japonsko',
        'IRL' => 'IRL — Írsko',
    ];
}


function ohnisko_menu_allergen_choices(): array
{
    return [
        '1'  => '1 — Obilniny obsahujúce lepok',
        '2'  => '2 — Kôrovce',
        '3'  => '3 — Vajcia',
        '4'  => '4 — Ryby',
        '5'  => '5 — Arašidy',
        '6'  => '6 — Sója',
        '7'  => '7 — Mlieko',
        '8'  => '8 — Orechy',
        '9'  => '9 — Zeler',
        '10' => '10 — Horčica',
        '11' => '11 — Sezam',
        '12' => '12 — Oxid siričitý a siričitany',
        '13' => '13 — Vlčí bôb',
        '14' => '14 — Mäkkýše',
    ];
}


/*
 * --------------------------------------------------------------------------
 * Sections
 * --------------------------------------------------------------------------
 *
 * These are stable internal section keys.
 *
 * An item stores only its section key.
 * The renderer decides how the section is styled in the flowing document.
 */

function ohnisko_menu_sections(): array
{
    return [
        'wine_beer_snacks' => [
            'title' => 'WINE & BEER SNACKS',
            'item_variant' => 'standard',
            'layout_group' => 'top',
        ],

        'small_dishes' => [
            'title' => 'MALÉ JEDLÁ',
            'item_variant' => 'standard',
            'layout_group' => 'top',

            'subtitle' =>
                'Sharing is caring. Vyskladajte si do stredu stola vlastné degustačné menu.',
        ],

        'josper_beef' => [
            'title' => 'HOVÄDZIE STEAKY',
            'item_variant' => 'standard',
            'layout_group' => 'josper',

            'intro' =>
                'Steaky dochucujeme kampotským korením, maldon soľou '
                . 'a chorvátskym olivovým olejom od kamoša Anteho.',
        ],

        'josper_rest' => [
            'title' => 'BEST OF THE REST',
            'item_variant' => 'standard',
            'layout_group' => 'josper',
        ],

        'josper_sides' => [
            'title' => 'PRÍLOHY JOSPER GRILL',
            'item_variant' => 'standard',
            'layout_group' => 'josper',
        ],

        'josper_sauces' => [
            'title' => 'OMÁČKY JOSPER GRILL',
            'item_variant' => 'compact',
            'layout_group' => 'josper',

            'shared_price' => '40g / 3.00€',
        ],

        'bbq' => [
            'title' => 'BBQ Z NAŠEJ UDIARNE',
            'item_variant' => 'standard',
            'layout_group' => 'flow',

            'intro' =>
                'BBQ podávame s pampuškami s cesnakom a kôprom, coleslawom, '
                . 'fazuľou na pive, nakladanou zeleninou, farmárskym dressingom '
                . 'z cmaru a melasovou BBQ omáčkou. (1,3,7,9,10)',

            'subtitle' =>
                'Americká klasika so stredoeurópskym twistom.',
        ],

        'sandwich' => [
            'title' => 'SANDWICH',
            'item_variant' => 'standard',
            'layout_group' => 'flow',

            'subtitle' =>
                'S našim domácim brioškovým sendvičovým chlebom.',
        ],

        'desserts_cheese' => [
            'title' => 'SLADKÉ & SYR',
            'item_variant' => 'standard',
            'layout_group' => 'flow',
            'static_before' => 'order_only',
        ],
    ];
}


function ohnisko_menu_section_choices(): array
{
    $choices = [];

    foreach (ohnisko_menu_sections() as $key => $section) {
        $choices[$key] = $section['title'];
    }

    return $choices;
}



/*
 * --------------------------------------------------------------------------
 * Static blocks
 * --------------------------------------------------------------------------
 *
 * These are not products and are therefore not stored as menu-item posts.
 */

function ohnisko_menu_static_blocks(): array
{
    return [
        'intro' => [
            'title' => 'Vitaj u nás!',

            'text' =>
                'Naša kuchyňa vychádza z tradičných techník – grilovania, '
                . 'údenia a fermentácie. Posúvame ich však ďalej a prepájame '
                . 'so svetovými chuťami, vždy s rešpektom k lokálnym surovinám.'
                . "\n\n"
                . 'Jedlo je pre nás láska. V duchu hesla „Sharing is caring“ '
                . 'vás pozývame deliť sa, ochutnávať a objavovať čo najviac '
                . 'z iskrivých chutí, ktoré Ohnisko prináša.'
                . "\n\n"
                . 'Jedlá servírujeme postupne do stredu stola, bez pevne daného poradia.'
                . "\n"
                . 'Je to hostina? Je to neformálny degustačný zážitok? Áno.'
                . "\n"
                . 'Pohodlne sa usaďte a nechajte sa unášať signature atmosférou Ohniska.',
        ],

        'order_only' => [
            'title' => 'NA OBJEDNÁVKU',
            'subtitle' => 'Minimálne 48h vopred',

            'items' => [
                'Grilovaný homár',
                'Pečené prasa',
                'Pečené jahňa',
                'Pečená morka',
            ],

            'text' =>
                'Dary mora kupujeme živé. Preto veľkosť, cena a čas dodania '
                . 'závisí od aktuálnej situácie na trhu. Prílohy ti odporučí '
                . 'podľa sezóny a tvojich prianí náš šéfkuchár.',
        ],

        'chef_credit' => [
            'name' => 'Michael Thíry',
            'instagram' => 'michael_thiry',
        ],

        'dog_menu' => [
            'title' => 'MENU PRE PSY',
            'product_name' => 'N&D Ocean granule',

            'description' =>
                'Superprémiové krmivo s hypoalergénnym zložením. '
                . 'Obsahuje tresku, špaldu aj ovos a vyvážený mix ovocia a zeleniny.',

            'promo' =>
                'Večera pre tvojho psíka je zdarma pri účte nad 5.00€. '
                . 'Psie menu ti prinášajú naši priatelia z www.krmiva.sk.',
        ],
    ];
}


/*
 * --------------------------------------------------------------------------
 * ACF field access
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_field(string $name, int $post_id)
{
    if (function_exists('get_field')) {
        return get_field($name, $post_id);
    }

    return get_post_meta($post_id, $name, true);
}


/*
 * --------------------------------------------------------------------------
 * Menu item normalization
 * --------------------------------------------------------------------------
 *
 * The renderer should work with this normalized structure instead of calling
 * get_field() all over the templates.
 */

function ohnisko_menu_normalize_item(WP_Post $post): array
{
    $post_id = $post->ID;

    $allergens = ohnisko_menu_field('allergens', $post_id);

    if (!is_array($allergens)) {
        $allergens = [];
    }

    $allergens = array_values(
        array_filter(
            array_map('strval', $allergens),
            static fn(string $value): bool => ctype_digit($value)
        )
    );

    usort(
        $allergens,
        static fn(string $a, string $b): int => (int) $a <=> (int) $b
    );

    $price_amount = ohnisko_menu_field('price_amount', $post_id);

    return [
        'id' => $post_id,

        'name' => get_the_title($post_id),

        'section' => (string) ohnisko_menu_field(
            'menu_section',
            $post_id
        ),

        'sort_order' => (int) ohnisko_menu_field(
            'sort_order',
            $post_id
        ),

        'description' => (string) ohnisko_menu_field(
            'description',
            $post_id
        ),

        'note' => (string) ohnisko_menu_field(
            'note',
            $post_id
        ),

        'details' => (string) ohnisko_menu_field(
            'details',
            $post_id
        ),

        'portion' => trim(
            (string) ohnisko_menu_field(
                'portion',
                $post_id
            )
        ),

        'origin' => trim(
            (string) ohnisko_menu_field(
                'origin',
                $post_id
            )
        ),

        'allergens' => $allergens,

        'badge' => (string) ohnisko_menu_field(
            'badge',
            $post_id
        ),

        'spiciness' => max(
            0,
            (int) ohnisko_menu_field(
                'spiciness',
                $post_id
            )
        ),

        'meta_order' => (string) (
            ohnisko_menu_field(
                'meta_order',
                $post_id
            ) ?: 'default'
        ),

        'price' => [
            'type' => (string) (
                ohnisko_menu_field(
                    'price_type',
                    $post_id
                ) ?: 'fixed'
            ),

            'amount' => (
                $price_amount === ''
                || $price_amount === null
            )
                ? null
                : (float) $price_amount,

            'unit' => trim(
                (string) ohnisko_menu_field(
                    'price_unit',
                    $post_id
                )
            ),

            'custom' => trim(
                (string) ohnisko_menu_field(
                    'price_custom',
                    $post_id
                )
            ),
        ],
    ];
}


/*
 * --------------------------------------------------------------------------
 * Fetch active products
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_get_items(): array
{
    static $cache = null;

    if (is_array($cache)) {
        return $cache;
    }

    $posts = get_posts([
        'post_type' => 'ohnisko_menu_item',
        'post_status' => 'publish',
        'posts_per_page' => -1,

        'meta_query' => [
            [
                'key' => 'active',
                'value' => '1',
                'compare' => '=',
            ],
        ],

        'orderby' => 'ID',
        'order' => 'ASC',

        'suppress_filters' => false,
    ]);

    $items = [];

    foreach ($posts as $post) {
        $item = ohnisko_menu_normalize_item($post);

        if ($item['section'] === '') {
            continue;
        }

        $items[] = $item;
    }

    usort(
        $items,
        static function (array $a, array $b): int {
            $order = $a['sort_order'] <=> $b['sort_order'];

            if ($order !== 0) {
                return $order;
            }

            return $a['id'] <=> $b['id'];
        }
    );

    $cache = $items;

    return $cache;
}


/*
 * --------------------------------------------------------------------------
 * Group products for renderer
 * --------------------------------------------------------------------------
 *
 * Result: [section_key => [item, ...]], sorted by sort_order and post ID.
 */

function ohnisko_menu_get_grouped_items(): array
{
    $grouped = [];

    foreach (ohnisko_menu_get_items() as $item) {
        $section = $item['section'];
        if (!isset($grouped[$section])) {
            $grouped[$section] = [];
        }

        $grouped[$section][] = $item;
    }

    return $grouped;
}


/*
 * --------------------------------------------------------------------------
 * Convenient renderer helper
 * --------------------------------------------------------------------------
 */

function ohnisko_menu_get_section_items(string $section): array {
    $grouped = ohnisko_menu_get_grouped_items();

    return $grouped[$section] ?? [];
}

require_once __DIR__ . '/menu-render.php';
