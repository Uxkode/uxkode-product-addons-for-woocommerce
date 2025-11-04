<?php
/**
 * Admin Dashboard Page
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include Admin Header.
require_once UXKODE_ADDONS_PATH . 'includes/admin/admin-header.php';

// Product Add-Ons Stats.
global $wpdb;

$table    = $wpdb->prefix . 'uxkode_woo_addons';
$meta_key = '_uxkode_product_addons_selected';

$total_addons = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    "SELECT COUNT(*) FROM `{$wpdb->prefix}uxkode_woo_addons`"
);

$active_addons = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->prepare(
        "SELECT COUNT(*) FROM `{$wpdb->prefix}uxkode_woo_addons` WHERE status = %d",
        1
    )
);

$inactive_addons = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->prepare(
        "SELECT COUNT(*) FROM `{$wpdb->prefix}uxkode_woo_addons` WHERE status = %d",
        0
    )
);

$active_addons_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->prepare( "SELECT id FROM `{$wpdb->prefix}uxkode_woo_addons` WHERE status = %d", 1 )
);

if ( empty( $active_addons_ids ) && $meta_key !== '_uxkode_product_addons_enabled' ) {
    $used_addons = 0;
} else {
    $results = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->prepare(
            "SELECT meta_value
             FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = %s
             AND p.post_status = %s
             AND p.post_type = %s",
            $meta_key,
            'publish',
            'product'
        )
    );

    $used_ids = array();

    foreach ( $results as $value ) {
        $addons = maybe_unserialize( $value );

        if ( is_array( $addons ) && ! empty( $addons ) ) {
            $valid_addons = array_intersect( $addons, $active_addons_ids );
            if ( ! empty( $valid_addons ) ) {
                $used_ids = array_merge( $used_ids, $valid_addons );
            }
        }
    }

    $used_addons = count( array_unique( $used_ids ) );
}

// Custom Buttons Stats.
$total_products = wp_count_posts( 'product' )->publish;

$args = array(
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Using meta_query is acceptable for this plugin context.
    'meta_query'     => array(
        array(
            'key'     => '_uxkode_custom_buttons_enabled',
            'value'   => 'yes',
            'compare' => '=',
        ),
    ),
);

$products = get_posts( $args );

$used_buttons        = count( $products );
$single_button_count = 0;
$dual_button_count   = 0;

foreach ( $products as $product ) {
    $btn_type = get_post_meta( $product->ID, '_uxkode_custom_buttons_type', true );
    if ( 'dual' === $btn_type ) {
        $dual_button_count++;
    } else {
        $single_button_count++;
    }
}
?>

<div class="uxkode-admin-content-wrapper">
    <h1><?php esc_html_e( 'Dashboard', 'uxkode-product-addons-for-woocommerce' ); ?></h1>

    <div class="uxkode-dashboard-stats">
        <h2><?php esc_html_e( 'Product Add-Ons Stats', 'uxkode-product-addons-for-woocommerce' ); ?></h2>
        <div class="product-addons-stats">
            <div class="uxkode-stat-card">
                <h3><?php echo esc_html( $total_addons ); ?></h3>
                <p><?php esc_html_e( 'Total Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?></p>
            </div>
            <div class="uxkode-stat-card">
                <h3><?php echo esc_html( $used_addons ); ?></h3>
                <p><?php esc_html_e( 'Used Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?></p>
            </div>
            <div class="uxkode-stat-card">
                <h3><?php echo esc_html( $active_addons ); ?></h3>
                <p><?php esc_html_e( 'Active Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?></p>
            </div>
            <div class="uxkode-stat-card">
                <h3><?php echo esc_html( $inactive_addons ); ?></h3>
                <p><?php esc_html_e( 'Inactive Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?></p>
            </div>
        </div>

        <div class="uxkode-spacer"></div>
        
        <h2><?php esc_html_e( 'Custom Buttons Stats', 'uxkode-product-addons-for-woocommerce' ); ?></h2>
        <div class="custom-buttons-stats">
            <div class="uxkode-stat-card">
                <h3><?php echo esc_html( $total_products ); ?></h3>
                <p><?php esc_html_e( 'Total Published Products', 'uxkode-product-addons-for-woocommerce' ); ?></p>
            </div>
            <div class="uxkode-stat-card">
                <h3><?php echo esc_html( $used_buttons ); ?></h3>
                <p><?php esc_html_e( 'Assigned Custom Buttons', 'uxkode-product-addons-for-woocommerce' ); ?></p>
            </div>
            <div class="uxkode-stat-card">
                <h3><?php echo esc_html( $single_button_count ); ?></h3>
                <p><?php esc_html_e( 'Assigned Single Button', 'uxkode-product-addons-for-woocommerce' ); ?></p>
            </div>
            <div class="uxkode-stat-card">
                <h3><?php echo esc_html( $dual_button_count ); ?></h3>
                <p><?php esc_html_e( 'Assigned Dual Button', 'uxkode-product-addons-for-woocommerce' ); ?></p>
            </div>
        </div>
    </div>
</div>