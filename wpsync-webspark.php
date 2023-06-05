<?php
/*
 * Plugin Name:       Wpsync Webspar
 * Plugin URI:        https://github.com/taras-fydria/wpsync-webspark
 * Description:       Synchronization of the database of goods with balances through the API.
 * Version:           1
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Taras Fydria
 * Author URI:        https://github.com/taras-fydria
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       wpsync-webspark
 */


use WpsyncWebspark\Plugin;

if (!defined('ABSPATH') || !class_exists('WooCommerce')) {
    return;
}

require_once plugin_dir_path(__FILE__) . 'inc/Singleton.php';
require_once plugin_dir_path(__FILE__) . 'Plugin.php';


Plugin::get_instance();