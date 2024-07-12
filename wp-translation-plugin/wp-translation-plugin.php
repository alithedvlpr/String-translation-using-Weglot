<?php
/**
 * Plugin Name: WP Translation Plugin
 * Description: A plugin to translate strings into multiple languages and store them in the database.
 * Version: 1.0
 * Author: Imran Ali
 * Text Domain: wp-translation-plugin
 */

defined('ABSPATH') or die('No script kiddies please!');

// Include the necessary files
require_once plugin_dir_path(__FILE__) . 'class-wp-translation-plugin.php';
require_once plugin_dir_path(__FILE__) . 'wp-translation-table.php';

// Initialize the plugin
function wp_translation_plugin_init() {
    $plugin = new WP_Translation_Plugin();
    $plugin->init();
}
add_action('plugins_loaded', 'wp_translation_plugin_init');

// Register the activation hook to create the table
register_activation_hook(__FILE__, 'wp_translation_plugin_create_table');
