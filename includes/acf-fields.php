<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/menu.php';

add_action('acf/include_fields', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $section_choices = ohnisko_menu_section_choices();
    $origin_choices = ohnisko_menu_origin_choices();
    $allergen_choices = ohnisko_menu_allergen_choices();

    acf_add_local_field_group([
        'key' => 'group_ohnisko_menu_item',
        'title' => 'Menu Item Details',

        'fields' => [

            /*
             * ---------------------------------------------------------
             * PLACEMENT
             * ---------------------------------------------------------
             */

            [
                'key' => 'field_ohnisko_tab_placement',
                'label' => 'Placement',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ],

            [
                'key' => 'field_ohnisko_menu_section',
                'label' => 'Menu Section',
                'name' => 'menu_section',
                'type' => 'select',
                'instructions' => 'Determines where this item appears in the menu.',
                'required' => 1,
                'choices' => $section_choices,
                'default_value' => '',
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
            ],

            [
                'key' => 'field_ohnisko_sort_order',
                'label' => 'Order',
                'name' => 'sort_order',
                'type' => 'number',
                'instructions' => 'Order inside the menu section. Recommended values: 10, 20, 30, ...',
                'required' => 1,
                'default_value' => 0,
                'min' => 0,
                'step' => 1,
            ],

            /*
             * ---------------------------------------------------------
             * CONTENT
             * ---------------------------------------------------------
             */

            [
                'key' => 'field_ohnisko_tab_content',
                'label' => 'Content',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ],

            [
                'key' => 'field_ohnisko_description',
                'label' => 'Description',
                'name' => 'description',
                'type' => 'textarea',
                'instructions' => 'Main product description shown below the item heading.',
                'required' => 0,
                'rows' => 4,
                'new_lines' => '',
            ],

            [
                'key' => 'field_ohnisko_note',
                'label' => 'Note',
                'name' => 'note',
                'type' => 'textarea',
                'instructions' => 'Optional secondary text shown before the description. Example: "(podľa aktuálnej dostupnosti)".',
                'required' => 0,
                'rows' => 2,
                'new_lines' => '',
            ],

            [
                'key' => 'field_ohnisko_details',
                'label' => 'Additional Details',
                'name' => 'details',
                'type' => 'textarea',
                'instructions' => 'Optional extra lines shown after the description. Example: DRESSING / SYR options.',
                'required' => 0,
                'rows' => 3,
                'new_lines' => '',
            ],

            /*
             * ---------------------------------------------------------
             * PRODUCT DATA
             * ---------------------------------------------------------
             */

            [
                'key' => 'field_ohnisko_tab_product',
                'label' => 'Product Data',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ],

            [
                'key' => 'field_ohnisko_portion',
                'label' => 'Weight / Portion',
                'name' => 'portion',
                'type' => 'text',
                'instructions' => 'Exact display value. Examples: 100g, 250g, 0,45l, 60g / 1ks.',
                'required' => 0,
            ],

            [
                'key' => 'field_ohnisko_origin',
                'label' => 'Origin',
                'name' => 'origin',
                'type' => 'select',
                'instructions' => 'Country code. The renderer adds /.../ automatically.',
                'required' => 0,
                'choices' => $origin_choices,
                'default_value' => false,
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
            ],

            [
                'key' => 'field_ohnisko_allergens',
                'label' => 'Allergens',
                'name' => 'allergens',
                'type' => 'checkbox',
                'instructions' => 'Select all allergens that apply to this item.',
                'required' => 0,
                'choices' => $allergen_choices,
                'layout' => 'horizontal',
                'return_format' => 'value',
            ],

            [
                'key' => 'field_ohnisko_badge',
                'label' => 'Badge',
                'name' => 'badge',
                'type' => 'select',
                'instructions' => 'Optional dietary marker shown in the menu.',
                'required' => 0,
                'choices' => [
                    'vege'           => 'VEGE',
                    'vege_available' => 'AJ VEGE VERZIA',
                ],
                'default_value' => false,
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
            ],

            [
                'key' => 'field_ohnisko_spiciness',
                'label' => 'Spiciness',
                'name' => 'spiciness',
                'type' => 'number',
                'instructions' => 'Number of chilli markers. 0 means no marker.',
                'required' => 0,
                'default_value' => 0,
                'min' => 0,
                'max' => 8,
                'step' => 1,
            ],

            /*
             * ---------------------------------------------------------
             * PRICE
             * ---------------------------------------------------------
             */

            [
                'key' => 'field_ohnisko_tab_price',
                'label' => 'Price',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ],

            [
                'key' => 'field_ohnisko_price_type',
                'label' => 'Price Type',
                'name' => 'price_type',
                'type' => 'select',
                'required' => 1,
                'choices' => [
                    'fixed'    => 'Fixed price',
                    'per_unit' => 'Price per unit',
                    'hidden'   => 'No visible price',
                    'custom'   => 'Custom display',
                ],
                'default_value' => 'fixed',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
            ],

            [
                'key' => 'field_ohnisko_price_amount',
                'label' => 'Price Amount',
                'name' => 'price_amount',
                'type' => 'number',
                'instructions' => 'Numeric price without €.',
                'required' => 1,
                'min' => 0,
                'step' => 0.01,

                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_ohnisko_price_type',
                            'operator' => '==',
                            'value' => 'fixed',
                        ],
                    ],
                    [
                        [
                            'field' => 'field_ohnisko_price_type',
                            'operator' => '==',
                            'value' => 'per_unit',
                        ],
                    ],
                ],
            ],

            [
                'key' => 'field_ohnisko_price_unit',
                'label' => 'Price Unit',
                'name' => 'price_unit',
                'type' => 'text',
                'instructions' => 'Example: 100g. Final output will be 100g/17.50€.',
                'required' => 1,

                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_ohnisko_price_type',
                            'operator' => '==',
                            'value' => 'per_unit',
                        ],
                    ],
                ],
            ],

            [
                'key' => 'field_ohnisko_price_custom',
                'label' => 'Custom Price Display',
                'name' => 'price_custom',
                'type' => 'text',
                'instructions' => 'Exact price string used only for exceptional cases.',
                'required' => 1,

                'conditional_logic' => [
                    [
                        [
                            'field' => 'field_ohnisko_price_type',
                            'operator' => '==',
                            'value' => 'custom',
                        ],
                    ],
                ],
            ],

            /*
             * ---------------------------------------------------------
             * DISPLAY
             * ---------------------------------------------------------
             */

            [
                'key' => 'field_ohnisko_tab_display',
                'label' => 'Display',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ],

            [
                'key' => 'field_ohnisko_meta_order',
                'label' => 'Metadata Order',
                'name' => 'meta_order',
                'type' => 'select',
                'instructions' => 'Normally leave this at Default.',
                'required' => 1,
                'choices' => [
                    'default'                  => 'Default: Origin → Portion → Allergens',
                    'origin_allergens_portion' => 'Origin → Allergens → Portion',
                ],
                'default_value' => 'default',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
            ],

            /*
             * ---------------------------------------------------------
             * STATE
             * ---------------------------------------------------------
             */

            [
                'key' => 'field_ohnisko_tab_state',
                'label' => 'State',
                'name' => '',
                'type' => 'tab',
                'placement' => 'top',
                'endpoint' => 0,
            ],

            [
                'key' => 'field_ohnisko_active',
                'label' => 'Active',
                'name' => 'active',
                'type' => 'true_false',
                'instructions' => 'Inactive items are excluded from the generated menu.',
                'required' => 0,
                'ui' => 1,
                'default_value' => 1,
            ],
        ],

        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'ohnisko_menu_item',
                ],
            ],
        ],

        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'active' => true,
    ]);
});
