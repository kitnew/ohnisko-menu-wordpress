<?php
/** One-shot menu snapshot importer. Run through WP-CLI after loading the plugin. */

if (!defined('ABSPATH') || !function_exists('wp_insert_post')) {
    fwrite(STDERR, "Run this file with wp eval-file from a WordPress installation.\n");
    exit(1);
}

require_once dirname(__DIR__) . '/includes/menu.php';

$cli_args = isset($args) && is_array($args) ? $args : [];
$options = [];
foreach ($cli_args as $arg) {
    if (is_string($arg) && str_starts_with($arg, '--')) {
        [$key, $value] = array_pad(explode('=', substr($arg, 2), 2), 2, '1');
        $options[$key] = $value;
    }
}
foreach ($_SERVER['argv'] ?? [] as $arg) {
    if (is_string($arg) && preg_match('/^--([a-z-]+)(?:=(.*))?$/', $arg, $match)) {
        $options[$match[1]] = $match[2] ?? '1';
    }
}
$file = $options['file'] ?? ($cli_args[0] ?? (__DIR__ . '/menu-2026.json'));
$dry_run = array_key_exists('dry-run', $options)
    || (($cli_args[1] ?? '') === 'dry-run')
    || (($cli_args[1] ?? '') === '--dry-run');
$raw = is_file($file) ? file_get_contents($file) : false;
$snapshot = is_string($raw) ? json_decode($raw, true) : null;
if (!is_array($snapshot) || !isset($snapshot['items']) || !is_array($snapshot['items'])) {
    fwrite(STDERR, "Invalid or unreadable snapshot: {$file}\n");
    exit(1);
}

$sections = ohnisko_menu_sections();
$origins = array_keys(ohnisko_menu_origin_choices());
$allergen_keys = array_map('intval', array_keys(ohnisko_menu_allergen_choices()));
$field_keys = [
    'menu_section'=>'field_ohnisko_menu_section', 'sort_order'=>'field_ohnisko_sort_order',
    'description'=>'field_ohnisko_description', 'note'=>'field_ohnisko_note', 'details'=>'field_ohnisko_details',
    'portion'=>'field_ohnisko_portion', 'origin'=>'field_ohnisko_origin', 'allergens'=>'field_ohnisko_allergens',
    'badge'=>'field_ohnisko_badge', 'spiciness'=>'field_ohnisko_spiciness', 'price_type'=>'field_ohnisko_price_type',
    'price_amount'=>'field_ohnisko_price_amount', 'price_unit'=>'field_ohnisko_price_unit',
    'price_custom'=>'field_ohnisko_price_custom', 'meta_order'=>'field_ohnisko_meta_order', 'active'=>'field_ohnisko_active',
];
$allowed = array_merge(['import_id', 'title', 'section'], array_keys($field_keys));
$errors = [];
$seen_ids = $seen_order = [];
foreach ($snapshot['items'] as $index => $item) {
    $row = $index + 1;
    if (!is_array($item)) { $errors[] = "Item {$row}: must be an object"; continue; }
    $unknown = array_diff(array_keys($item), $allowed);
    if ($unknown) $errors[] = "Item {$row}: unknown fields " . implode(', ', $unknown);
    foreach (['import_id','title','section','sort_order','price_type'] as $required) {
        if (!array_key_exists($required, $item)) $errors[] = "Item {$row}: missing {$required}";
    }
    $id = $item['import_id'] ?? '';
    if (!is_string($id) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $id)) $errors[] = "Item {$row}: invalid import_id";
    elseif (isset($seen_ids[$id])) $errors[] = "Item {$row}: duplicate import_id {$id}";
    else $seen_ids[$id] = true;
    if (!isset($sections[$item['section'] ?? ''])) $errors[] = "Item {$row}: invalid section";
    if (!isset($item['title']) || !is_string($item['title']) || trim($item['title']) === '') $errors[] = "Item {$row}: title is required";
    if (!isset($item['sort_order']) || !is_int($item['sort_order']) || $item['sort_order'] < 0) $errors[] = "Item {$row}: sort_order must be a nonnegative integer";
    else {
        $order_key = ($item['section'] ?? '') . ':' . $item['sort_order'];
        if (isset($seen_order[$order_key])) $errors[] = "Item {$row}: duplicate sort_order in section";
        $seen_order[$order_key] = true;
    }
    if (!in_array($item['price_type'] ?? '', ['fixed','per_unit','custom','hidden'], true)) $errors[] = "Item {$row}: invalid price_type";
    else {
        $type = $item['price_type'];
        if (in_array($type, ['fixed','per_unit'], true) && (!isset($item['price_amount']) || !is_numeric($item['price_amount']) || $item['price_amount'] < 0)) $errors[] = "Item {$row}: {$type} requires nonnegative price_amount";
        if ($type === 'per_unit' && empty($item['price_unit'])) $errors[] = "Item {$row}: per_unit requires price_unit";
        if ($type === 'custom' && empty($item['price_custom'])) $errors[] = "Item {$row}: custom requires price_custom";
        if (($type === 'fixed' && (isset($item['price_unit']) || isset($item['price_custom'])))
            || ($type === 'per_unit' && isset($item['price_custom']))
            || ($type === 'custom' && (isset($item['price_amount']) || isset($item['price_unit'])))
            || ($type === 'hidden' && (isset($item['price_amount']) || isset($item['price_unit']) || isset($item['price_custom'])))) $errors[] = "Item {$row}: price values do not match price_type";
    }
    if (isset($item['origin']) && $item['origin'] !== '' && !in_array($item['origin'], $origins, true)) $errors[] = "Item {$row}: invalid origin";
    if (isset($item['badge']) && !in_array($item['badge'], ['', 'vege', 'vege_available'], true)) $errors[] = "Item {$row}: invalid badge";
    if (isset($item['meta_order']) && !in_array($item['meta_order'], ['default','origin_allergens_portion'], true)) $errors[] = "Item {$row}: invalid meta_order";
    if (isset($item['allergens']) && (!is_array($item['allergens']) || array_diff($item['allergens'], $allergen_keys) || count($item['allergens']) !== count(array_unique($item['allergens'])))) $errors[] = "Item {$row}: allergens must be unique values from 1 to 14";
    if (isset($item['spiciness']) && (!is_int($item['spiciness']) || $item['spiciness'] < 0 || $item['spiciness'] > 8)) $errors[] = "Item {$row}: spiciness must be an integer from 0 to 8";
    if (isset($item['active']) && !is_bool($item['active'])) $errors[] = "Item {$row}: active must be boolean";
    foreach (['description','note','details','portion','price_unit','price_custom'] as $text_field) {
        if (isset($item[$text_field]) && !is_string($item[$text_field])) $errors[] = "Item {$row}: {$text_field} must be text";
    }
}
if ($errors) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}
if (!function_exists('update_field')) {
    fwrite(STDERR, "ACF update_field() is unavailable; activate ACF before importing.\n");
    exit(1);
}

$counts = ['Created'=>0, 'Updated'=>0, 'Skipped'=>0, 'Errors'=>0];
foreach ($snapshot['items'] as $item) {
    $matches = get_posts(['post_type'=>'ohnisko_menu_item', 'post_status'=>'any', 'posts_per_page'=>2, 'fields'=>'ids', 'meta_key'=>'_ohnisko_import_id', 'meta_value'=>$item['import_id']]);
    if (count($matches) > 1) {
        echo "ERROR {$item['import_id']}: multiple posts have this import_id\n"; $counts['Errors']++; continue;
    }
    $existing = $matches[0] ?? 0;
    $action = $existing ? 'UPDATE' : 'CREATE';
    echo "{$action} {$item['import_id']}: {$item['title']}\n";
    if ($dry_run) { $counts[$existing ? 'Updated' : 'Created']++; continue; }
    try {
        $post_id = $existing ? wp_update_post(['ID'=>$existing, 'post_title'=>$item['title'], 'post_status'=>'publish'], true)
            : wp_insert_post(['post_type'=>'ohnisko_menu_item', 'post_title'=>$item['title'], 'post_status'=>'publish'], true);
        if (is_wp_error($post_id)) throw new RuntimeException($post_id->get_error_message());
        update_post_meta($post_id, '_ohnisko_import_id', $item['import_id']);
        $values = [
            'menu_section'=>$item['section'], 'sort_order'=>$item['sort_order'], 'description'=>$item['description'] ?? '',
            'note'=>$item['note'] ?? '', 'details'=>$item['details'] ?? '', 'portion'=>$item['portion'] ?? '',
            'origin'=>$item['origin'] ?? '', 'allergens'=>array_map('strval', $item['allergens'] ?? []), 'badge'=>$item['badge'] ?? '',
            'spiciness'=>$item['spiciness'] ?? 0, 'price_type'=>$item['price_type'], 'price_amount'=>$item['price_amount'] ?? '',
            'price_unit'=>$item['price_unit'] ?? '', 'price_custom'=>$item['price_custom'] ?? '',
            'meta_order'=>$item['meta_order'] ?? 'default', 'active'=>$item['active'] ?? true,
        ];
        foreach ($field_keys as $field => $key) update_field($key, $values[$field], $post_id);
        $counts[$existing ? 'Updated' : 'Created']++;
    } catch (Throwable $error) {
        echo "ERROR {$item['import_id']}: {$error->getMessage()}\n"; $counts['Errors']++;
    }
}
printf("Summary: Created %d, Updated %d, Skipped %d, Errors %d%s\n", $counts['Created'], $counts['Updated'], $counts['Skipped'], $counts['Errors'], $dry_run ? ' (dry-run)' : '');
if ($counts['Errors']) exit(1);
