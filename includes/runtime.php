<?php

if (!defined('OHNISKO_MENU_DIR')) {
    define('OHNISKO_MENU_DIR', dirname(__DIR__) . '/');
}

function ohnisko_menu_runtime_dir(): string
{
    return dirname(OHNISKO_MENU_DIR, 5) . '/ohnisko-pdf-runtime';
}

function ohnisko_menu_print_token(): string
{
    $config_file = ohnisko_menu_runtime_dir() . '/config.json';
    if (!is_file($config_file)) {
        return '';
    }

    $config = json_decode((string) file_get_contents($config_file), true);
    return is_array($config) && is_string($config['print_token'] ?? null)
        ? $config['print_token']
        : '';
}
