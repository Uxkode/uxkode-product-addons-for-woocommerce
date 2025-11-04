<?php
/**
 * Uxkode Product Addons for WooCommerce Uninstall
 *
 * Executed when the plugin is deleted via the WordPress admin.
 * Removes all plugin-related data, including options, custom tables, and post meta.
 *
 * @package Uxkode_Addons_WooCommerce
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

/**
 * Delete Plugin Options
 */
$plugin_options = [
    'uxkode_addons_db_version',
    'uxkode_custom_buttons_styles',
];

foreach ( $plugin_options as $option ) {
    delete_option( $option );
}

/**
 * Delete Plugin Tables
 */
$tables = [
    $wpdb->prefix . 'uxkode_woo_addons',
];

foreach ( $tables as $table ) {
    // Sanitize table name to prevent SQL injection
    $sanitized_table = esc_sql( $table );
    
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared
    $wpdb->query( "DROP TABLE IF EXISTS `" . $sanitized_table . "`" );
}

/**
 * Delete Post Meta (product-level meta fields)
 */
$meta_keys = [
    '_uxkode_product_addons_enabled',
    '_uxkode_product_addons_selected',
    '_uxkode_custom_buttons_enabled',
    '_uxkode_custom_buttons_type',
    '_uxkode_custom_buttons',
];

foreach ( $meta_keys as $meta_key ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
            $meta_key
        )
    );
}