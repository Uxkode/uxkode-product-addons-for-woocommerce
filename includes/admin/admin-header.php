<?php
/**
 * Admin Header Part
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate nonce-protected admin URL for a page.
 *
 * @param string $slug Page slug.
 * @return string Safe admin URL with nonce.
 */
function uxkode_admin_page_url( $slug ) {
    return wp_nonce_url(
        admin_url( 'admin.php?page=' . $slug ),
        'uxkode_admin_action_' . $slug
    );
}

// Sanitize current page safely.
$current_page = '';
if ( isset( $_GET['page'] ) ) {
    $current_page = sanitize_key( wp_unslash( $_GET['page'] ) );

    // Verify nonce if performing actions on this page.
    if ( isset( $_GET['_wpnonce'] ) ) {
        $nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );
        if ( ! wp_verify_nonce( $nonce, 'uxkode_admin_action_' . $current_page ) ) {
            $current_page = ''; // Reset if nonce is invalid
        }
    }
}

// Menu items array
$menu_items = [
    'uxkode-addons-dashboard'   => __( 'Dashboard', 'uxkode-product-addons-for-woocommerce' ),
    'uxkode-product-addons'     => __( 'Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ),
    'uxkode-custom-buttons'     => __( 'Custom Buttons', 'uxkode-product-addons-for-woocommerce' ),
    'uxkode-addons-settings'    => __( 'Settings', 'uxkode-product-addons-for-woocommerce' ),
];
?>

<div class="uxkode-admin-header">
    <div class="uxkode-admin-brand">
        <img src="<?php echo esc_url( UXKODE_ADDONS_ASSETS . 'img/uxkode-product-addons-for-woocommerce-logo.svg' ); ?>" alt="<?php esc_attr_e( 'uxkode Addons Logo', 'uxkode-product-addons-for-woocommerce' ); ?>">
    </div>

    <button class="uxkode-admin-toggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'uxkode-product-addons-for-woocommerce' ); ?>">
        <span class="dashicons dashicons-menu"></span>
    </button>

    <nav class="uxkode-admin-menu">
        <?php foreach ( $menu_items as $slug => $label ) : ?>
            <a href="<?php echo esc_url( uxkode_admin_page_url( $slug ) ); ?>" class="<?php echo ( $current_page === $slug ) ? 'active' : ''; ?>">
                <?php echo esc_html( $label ); ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>