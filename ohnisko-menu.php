<?php
/**
 * Plugin Name: Ohnisko Menu
 * Description: Structured menu management and PDF generation for Ohnisko.
 * Version: 0.1.0
 * Author: Ohnisko
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OHNISKO_MENU_DIR', plugin_dir_path(__FILE__));
define('OHNISKO_MENU_URL', plugin_dir_url(__FILE__));

require_once OHNISKO_MENU_DIR . 'includes/runtime.php';
require_once OHNISKO_MENU_DIR . 'includes/content-types.php';
require_once OHNISKO_MENU_DIR . 'includes/acf-fields.php';
require_once OHNISKO_MENU_DIR . 'includes/menu.php';
require_once OHNISKO_MENU_DIR . 'includes/print-endpoint.php';
require_once OHNISKO_MENU_DIR . 'includes/pdf-e2e-admin.php';
