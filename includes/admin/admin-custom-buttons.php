<?php
/**
 * Admin Custom Buttons Page.
 *
 * Allows admin to set global custom button styles.
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once UXKODE_ADDONS_PATH . 'includes/admin/admin-header.php';

// Handle reset to default styles.
if ( isset( $_GET['uxkode_reset_defaults'] ) && '1' === $_GET['uxkode_reset_defaults'] && check_admin_referer( 'uxkode_reset_defaults_nonce' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    delete_option( 'uxkode_custom_buttons_styles' );
    $redirect_url = add_query_arg( 'uxkode_reset_notice', '1', remove_query_arg( array( 'uxkode_reset_defaults', '_wpnonce' ) ) );
    wp_safe_redirect( $redirect_url );
    exit;
}

// Show reset success notice.
if ( isset( $_GET['uxkode_reset_notice'] ) && '1' === $_GET['uxkode_reset_notice'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    echo '<div class="updated notice"><p>' .
        esc_html__( 'Settings have been reset to defaults.', 'uxkode-product-addons-for-woocommerce' ) .
        '</p></div>';
}

// Save settings.
if (
    isset( $_POST['uxkode_custom_buttons_nonce'] ) &&
    wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['uxkode_custom_buttons_nonce'] ) ), 'uxkode_custom_buttons_save' )
) {
    $settings = array(
        'uxkode_button1' => array(
            'bg_color'           => isset( $_POST['uxkode_button1_bg_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button1_bg_color'] ) ) : '',
            'text_color'         => isset( $_POST['uxkode_button1_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button1_text_color'] ) ) : '',
            'border_color'       => isset( $_POST['uxkode_button1_border_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button1_border_color'] ) ) : '',
            'bg_hover_color'     => isset( $_POST['uxkode_button1_bg_hover_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button1_bg_hover_color'] ) ) : '',
            'text_hover_color'   => isset( $_POST['uxkode_button1_text_hover_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button1_text_hover_color'] ) ) : '',
            'border_hover_color' => isset( $_POST['uxkode_button1_border_hover_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button1_border_hover_color'] ) ) : '',
        ),
        'uxkode_button2' => array(
            'bg_color'           => isset( $_POST['uxkode_button2_bg_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button2_bg_color'] ) ) : '',
            'text_color'         => isset( $_POST['uxkode_button2_text_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button2_text_color'] ) ) : '',
            'border_color'       => isset( $_POST['uxkode_button2_border_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button2_border_color'] ) ) : '',
            'bg_hover_color'     => isset( $_POST['uxkode_button2_bg_hover_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button2_bg_hover_color'] ) ) : '',
            'text_hover_color'   => isset( $_POST['uxkode_button2_text_hover_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button2_text_hover_color'] ) ) : '',
            'border_hover_color' => isset( $_POST['uxkode_button2_border_hover_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['uxkode_button2_border_hover_color'] ) ) : '',
        ),
    );

    update_option( 'uxkode_custom_buttons_styles', $settings );

    // Redirect to same page with success notice.
    $redirect_url = add_query_arg(
        'uxkode_saved_notice',
        '1',
        remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) )
    );
    wp_safe_redirect( $redirect_url );
    exit;
}
// Show save success notice.
$notice_flag = isset( $_GET['uxkode_saved_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['uxkode_saved_notice'] ) ) : '';

if ( '1' === $notice_flag ) {
    echo '<div class="updated notice"><p>' .
        esc_html__( 'Settings saved successfully.', 'uxkode-product-addons-for-woocommerce' ) .
        '</p></div>';
}

// Get saved settings.
$settings    = get_option( 'uxkode_custom_buttons_styles', array() );
$custom_btn1 = isset( $settings['uxkode_button1'] ) ? $settings['uxkode_button1'] : array();
$custom_btn2 = isset( $settings['uxkode_button2'] ) ? $settings['uxkode_button2'] : array();
?>

<div class="uxkode-admin-content-wrapper">
    <h1><?php esc_html_e( 'Custom Buttons', 'uxkode-product-addons-for-woocommerce' ); ?></h1>
    <p><?php esc_html_e( 'Set global styling for Custom Buttons. Leave blank to use default CSS variables.', 'uxkode-product-addons-for-woocommerce' ); ?></p>

    <form action="" method="post">
        <?php wp_nonce_field( 'uxkode_custom_buttons_save', 'uxkode_custom_buttons_nonce' ); ?>

        <h2><?php esc_html_e( 'Button 1 Styling', 'uxkode-product-addons-for-woocommerce' ); ?></h2>
        <table class="form-table uxkode-style-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Normal Style', 'uxkode-product-addons-for-woocommerce' ); ?></th>
                    <th><?php esc_html_e( 'Hover Style', 'uxkode-product-addons-for-woocommerce' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Live Preview -->
                <tr class="preview-row">
                    <td colspan="2" class="live-preview-cell">
                        <button type="button" class="uxkode-custom-btn-1"><?php esc_html_e( 'Live Preview', 'uxkode-product-addons-for-woocommerce' ); ?></button>
                    </td>
                </tr>
                <!-- Background -->
                <tr>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Background Color', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button1_bg_color" value="<?php echo esc_attr( isset( $custom_btn1['bg_color'] ) ? $custom_btn1['bg_color'] : '#ff6b35' ); ?>">
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Background Hover', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button1_bg_hover_color" value="<?php echo esc_attr( isset( $custom_btn1['bg_hover_color'] ) ? $custom_btn1['bg_hover_color'] : '#1890ff' ); ?>">
                        </div>
                    </td>
                </tr>
                <!-- Text -->
                <tr>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Text Color', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button1_text_color" value="<?php echo esc_attr( isset( $custom_btn1['text_color'] ) ? $custom_btn1['text_color'] : '#ffffff' ); ?>">
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Text Hover', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button1_text_hover_color" value="<?php echo esc_attr( isset( $custom_btn1['text_hover_color'] ) ? $custom_btn1['text_hover_color'] : '#ffffff' ); ?>">
                        </div>
                    </td>
                </tr>
                <!-- Border -->
                <tr>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Border Color', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button1_border_color" value="<?php echo esc_attr( isset( $custom_btn1['border_color'] ) ? $custom_btn1['border_color'] : '#ff6b35' ); ?>">
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Border Hover', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button1_border_hover_color" value="<?php echo esc_attr( isset( $custom_btn1['border_hover_color'] ) ? $custom_btn1['border_hover_color'] : '#1890ff' ); ?>">
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <h2><?php esc_html_e( 'Button 2 Styling', 'uxkode-product-addons-for-woocommerce' ); ?></h2>
        <table class="form-table uxkode-style-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Normal Style', 'uxkode-product-addons-for-woocommerce' ); ?></th>
                    <th><?php esc_html_e( 'Hover Style', 'uxkode-product-addons-for-woocommerce' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Live Preview -->
                <tr class="preview-row">
                    <td colspan="2" class="live-preview-cell">
                        <button type="button" class="uxkode-custom-btn-2"><?php esc_html_e( 'Live Preview', 'uxkode-product-addons-for-woocommerce' ); ?></button>
                    </td>
                </tr>
                <!-- Background -->
                <tr>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Background Color', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button2_bg_color" value="<?php echo esc_attr( isset( $custom_btn2['bg_color'] ) ? $custom_btn2['bg_color'] : '#ffffff' ); ?>">
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Background Hover', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button2_bg_hover_color" value="<?php echo esc_attr( isset( $custom_btn2['bg_hover_color'] ) ? $custom_btn2['bg_hover_color'] : '#1890ff' ); ?>">
                        </div>
                    </td>
                </tr>
                <!-- Text -->
                <tr>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Text Color', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button2_text_color" value="<?php echo esc_attr( isset( $custom_btn2['text_color'] ) ? $custom_btn2['text_color'] : '#ff6b35' ); ?>">
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Text Hover', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button2_text_hover_color" value="<?php echo esc_attr( isset( $custom_btn2['text_hover_color'] ) ? $custom_btn2['text_hover_color'] : '#ffffff' ); ?>">
                        </div>
                    </td>
                </tr>
                <!-- Border -->
                <tr>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Border Color', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button2_border_color" value="<?php echo esc_attr( isset( $custom_btn2['border_color'] ) ? $custom_btn2['border_color'] : '#ff6b35' ); ?>">
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <label><?php esc_html_e( 'Border Hover', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                            <input type="color" name="uxkode_button2_border_hover_color" value="<?php echo esc_attr( isset( $custom_btn2['border_hover_color'] ) ? $custom_btn2['border_hover_color'] : '#1890ff' ); ?>">
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="uxkode-flex">
            <?php submit_button( __( 'Save Settings', 'uxkode-product-addons-for-woocommerce' ) ); ?>
            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'uxkode_reset_defaults', '1' ), 'uxkode_reset_defaults_nonce' ) ); ?>" class="uxkode-button-outline" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to reset all button styles?', 'uxkode-product-addons-for-woocommerce' ) ); ?>');">
                <?php esc_html_e( 'Reset to Defaults', 'uxkode-product-addons-for-woocommerce' ); ?>
            </a>
        </div>
    </form>
</div>