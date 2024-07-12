<?php

// Function to create the table
function wp_translation_plugin_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'translations';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        string text NOT NULL,
        language varchar(10) NOT NULL,
        translation text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
