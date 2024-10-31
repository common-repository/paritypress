<?php

/**
 * Plugin Name:         ParityPress - Parity Pricing with Discount Rules
 * Description:         ParityPress boosts marketing via Geo-based discounts, enhancing engagement & conversion.
 * Plugin URI:          https://wordpress.org/plugins/paritypress
 * Author:              ParityDiscounts
 * Author URI:          https://profiles.wordpress.org/paritydiscounts
 * Text Domain:         parity-press
 * Domain Path:         /languages
 *
 * Version:             1.0.2
 * Requires PHP:        7.2
 * Requires at least:   5.0
 * Tested up to:        6.4
 * License:             GPL v2 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

define('PARITY_PRESS_VERSION', '1.0.2');
define('PARITY_PRESS_PLUGIN_FILE', __FILE__);
define('PARITY_PRESS_PLUGIN_ASSETS', plugins_url('assets', __FILE__));
define('PARITY_PRESS_STORE_URL', 'https://paritypress.com/');

$app = require_once __DIR__ . '/bootstrap.php';

add_action('plugins_loaded', function () use ($app) {
    do_action('parity_press_loaded', $app);

    load_plugin_textdomain('parity-press', false, dirname(plugin_basename(__FILE__)) . '/languages');

    $app->boot();
});

add_action('init', function () {
    do_action('parity_press_init');
});
