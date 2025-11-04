<?php
/**
 * Database CRUD for Product Add-Ons
 *
 * Handles Create, Read, Update, Delete operations for WooCommerce Product Add-Ons.
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Product_Addons_CRUD
 *
 * Provides CRUD operations for Product Add-Ons.
 */
class Product_Addons_CRUD {

    /**
     * Get the database table name.
     *
     * @return string Table name.
     */
    protected static function table() {
        global $wpdb;
        return $wpdb->prefix . 'uxkode_woo_addons';
    }

    /**
     * Retrieve all or filtered Add-Ons.
     *
     * @param array $args Optional arguments like status.
     * @return array List of Add-Ons.
     */
    public static function get_addons( $args = [] ) {
        global $wpdb;

        if ( isset( $args['status'] ) ) {
            return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                $wpdb->prepare(
                    "SELECT * FROM `{$wpdb->prefix}uxkode_woo_addons` WHERE status = %d ORDER BY id DESC",
                    (int) $args['status']
                )
            );
        } else {
            return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                "SELECT * FROM `{$wpdb->prefix}uxkode_woo_addons` ORDER BY id DESC"
            );
        }
    }

    /**
     * Get Add-Ons by IDs.
     *
     * @param array $ids Array of Add-On IDs.
     * @return array List of Add-Ons.
     */
    public static function get_addons_by_ids( $ids ) {
        global $wpdb;

        $ids = array_values( array_filter( array_map( 'absint', (array) $ids ) ) );

        if ( empty( $ids ) ) {
            return [];
        }

        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $query = "SELECT * FROM `{$wpdb->prefix}uxkode_woo_addons` WHERE id IN ({$placeholders})";
        
        return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare( $query, ...$ids ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        );
    }

    /**
     * Get a single Add-On by ID.
     *
     * @param int $id Add-On ID.
     * @return object|null Add-On object or null.
     */
    public static function get_addon( $id ) {
        global $wpdb;

        return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare( 
                "SELECT * FROM `{$wpdb->prefix}uxkode_woo_addons` WHERE id = %d", 
                absint( $id ) 
            )
        );
    }

    /**
     * Insert a new Add-On.
     *
     * @param array $data Add-On data.
     * @return int|WP_Error Inserted ID or WP_Error.
     */
    public static function insert_addon( $data ) {
        global $wpdb;

        $table  = self::table();
        $title  = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
        $type   = isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'none';
        $price  = isset( $data['price'] ) ? floatval( $data['price'] ) : 0;
        $status = isset( $data['status'] ) ? (int) $data['status'] : 1;

        if ( '' === $title ) {
            return new WP_Error( 'uxkode_product_addons_title_required', __( 'Title is required.', 'uxkode-product-addons-for-woocommerce' ) );
        }

        $inserted = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $table,
            [
                'title'  => $title,
                'type'   => $type,
                'price'  => $price,
                'status' => $status,
            ],
            [ '%s', '%s', '%f', '%d' ]
        );

        if ( ! $inserted ) {
            return new WP_Error( 'uxkode_product_addons_insert_failed', __( 'Failed to create addon.', 'uxkode-product-addons-for-woocommerce' ) );
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Update an existing Add-On.
     *
     * @param int   $id Add-On ID.
     * @param array $data Add-On data.
     * @return true|WP_Error True on success, WP_Error on failure.
     */
    public static function update_addon( $id, $data ) {
        global $wpdb;

        $table  = self::table();
        $title  = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
        $type   = isset( $data['type'] ) ? sanitize_text_field( $data['type'] ) : 'none';
        $price  = isset( $data['price'] ) ? floatval( $data['price'] ) : 0;
        $status = isset( $data['status'] ) ? (int) $data['status'] : 1;

        if ( '' === $title ) {
            return new WP_Error( 'uxkode_product_addons_title_required', __( 'Title is required.', 'uxkode-product-addons-for-woocommerce' ) );
        }

        $updated = $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $table,
            [
                'title'  => $title,
                'type'   => $type,
                'price'  => $price,
                'status' => $status,
            ],
            [ 'id' => absint( $id ) ],
            [ '%s', '%s', '%f', '%d' ],
            [ '%d' ]
        );

        if ( false === $updated ) {
            return new WP_Error( 'uxkode_product_addons_update_failed', __( 'Failed to update addon.', 'uxkode-product-addons-for-woocommerce' ) );
        }

        return true;
    }

    /**
     * Delete an Add-On.
     *
     * @param int $id Add-On ID.
     * @return true|WP_Error True on success, WP_Error on failure.
     */
    public static function delete_addon( $id ) {
        global $wpdb;

        $table   = self::table();
        $deleted = $wpdb->delete( $table, [ 'id' => absint( $id ) ], [ '%d' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

        if ( ! $deleted ) {
            return new WP_Error( 'uxkode_product_addons_delete_failed', __( 'Failed to delete addon.', 'uxkode-product-addons-for-woocommerce' ) );
        }

        return true;
    }
}