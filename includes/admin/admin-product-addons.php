<?php
/**
 * Product Add-Ons Admin Page
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include Admin Header
require_once UXKODE_ADDONS_PATH . 'includes/admin/admin-header.php';

// Include CRUD Class
if ( ! class_exists( 'Product_Addons_CRUD' ) ) {
    require_once UXKODE_ADDONS_PATH . 'includes/admin/class/class-product-addons-crud.php';
}
?>
<div class="uxkode-admin-content-wrapper">
    <?php
    $editing_id = 0;
    $title      = '';
    $type       = 'none';
    $price      = 0;
    $status     = 1;

    // Secure edit handling with nonce + capability check
    if ( isset( $_GET['edit_addon'], $_GET['_wpnonce'] ) ) {

        $edit_id = absint( $_GET['edit_addon'] );
        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'uxkode_edit_addon_' . $edit_id ) ) {
            wp_die( esc_html__( 'Security check failed!', 'uxkode-product-addons-for-woocommerce' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to edit this Add-On.', 'uxkode-product-addons-for-woocommerce' ) );
        }

        $addon = Product_Addons_CRUD::get_addon( $edit_id );
        if ( $addon ) {
            $editing_id = $addon->id;
            $title      = $addon->title;
            $type       = $addon->type;
            $price      = (float) $addon->price;
            $status     = (int) $addon->status;
        }
    }

    // Handle form actions: add, edit, delete
    if ( isset( $_POST['uxkode_product_addons_action'] ) && check_admin_referer( 'uxkode_product_addons_nonce', 'uxkode_product_addons_nonce_field' ) ) {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'uxkode-product-addons-for-woocommerce' ) );
        }

        $action = sanitize_key( $_POST['uxkode_product_addons_action'] );

        if ( 'add' === $action || 'edit' === $action ) {
            $data = [
                'title'  => isset( $_POST['addon_title'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_title'] ) ) : '',
                'type'   => isset( $_POST['addon_type'] ) ? sanitize_text_field( wp_unslash( $_POST['addon_type'] ) ) : 'none',
                'price'  => isset( $_POST['addon_price'] ) ? (float) $_POST['addon_price'] : 0,
                'status' => isset( $_POST['addon_status'] ) ? (int) $_POST['addon_status'] : 1,
            ];

            if ( 'add' === $action ) {
                $result = Product_Addons_CRUD::insert_addon( $data );
                if ( is_wp_error( $result ) ) {
                    $error_message = $result->get_error_message();
                } else {
                    wp_safe_redirect( admin_url( 'admin.php?page=uxkode-product-addons&added=1' ) );
                    exit;
                }
            } else {
                $editing_id = isset( $_POST['addon_id'] ) ? absint( $_POST['addon_id'] ) : 0;
                $result     = Product_Addons_CRUD::update_addon( $editing_id, $data );
                if ( is_wp_error( $result ) ) {
                    $error_message = $result->get_error_message();
                } else {
                    wp_safe_redirect( admin_url( 'admin.php?page=uxkode-product-addons&updated=1' ) );
                    exit;
                }
            }
        }

        if ( 'delete' === $action ) {
            $del_id = isset( $_POST['addon_id'] ) ? absint( $_POST['addon_id'] ) : 0;
            $result = Product_Addons_CRUD::delete_addon( $del_id );

            if ( is_wp_error( $result ) ) {
                $error_message = $result->get_error_message();
            } else {
                wp_safe_redirect( admin_url( 'admin.php?page=uxkode-product-addons&deleted=1' ) );
                exit;
            }
        }
    }

    $addons = Product_Addons_CRUD::get_addons();
    ?>
    <div class="uxkode-addons-container">
        <?php if ( isset( $error_message ) ) : ?>
        <div class="notice notice-error">
            <p><?php echo esc_html( $error_message ); ?></p>
        </div>
        <?php endif; ?>

        <?php if ( isset( $_GET['added'] ) && $_GET['added'] == 1 ) : ?>
        <div class="notice notice-success">
            <p><?php esc_html_e( 'Add-On added successfully!', 'uxkode-product-addons-for-woocommerce' ); ?></p>
        </div>
        <?php endif; ?>

        <?php if ( isset( $_GET['updated'] ) && $_GET['updated'] == 1 ) : ?>
        <div class="notice notice-success">
            <p><?php esc_html_e( 'Add-On updated successfully!', 'uxkode-product-addons-for-woocommerce' ); ?></p>
        </div>
        <?php endif; ?>

        <?php if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 ) : ?>
        <div class="notice notice-success">
            <p><?php esc_html_e( 'Add-On deleted successfully!', 'uxkode-product-addons-for-woocommerce' ); ?></p>
        </div>
        <?php endif; ?>
        <h1>
            <?php esc_html_e( 'Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?>
        </h1>
        <h3>
            <?php esc_html_e( 'Create Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?>
        </h3>

        <form method="post" class="uxkode-admin-form">
            <?php wp_nonce_field( 'uxkode_product_addons_nonce', 'uxkode_product_addons_nonce_field' ); ?>
            <input type="hidden" name="uxkode_product_addons_action" value="<?php echo $editing_id ? 'edit' : 'add'; ?>">
            <input type="hidden" name="addon_id" value="<?php echo esc_attr( $editing_id ); ?>">

            <table class="form-table">
                <tr>
                    <th>
                        <label for="addon_title"><?php esc_html_e( 'Title', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="addon_title" id="addon_title" required value="<?php echo esc_attr( $title ); ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="addon_price"><?php esc_html_e( 'Price', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                    </th>
                    <td>
                        <input type="number" step="0.01" name="addon_price" id="addon_price" required value="<?php echo esc_attr( $price ); ?>">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="addon_type"><?php esc_html_e( 'Input Type', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                    </th>
                    <td>
                        <select name="addon_type" id="addon_type">
                            <option value="none" <?php selected( $type, 'none' ); ?>>
                                <?php esc_html_e( 'None', 'uxkode-product-addons-for-woocommerce' ); ?>
                            </option>
                            <option value="text" <?php selected( $type, 'text' ); ?>>
                                <?php esc_html_e( 'Text', 'uxkode-product-addons-for-woocommerce' ); ?>
                            </option>
                            <option value="number" <?php selected( $type, 'number' ); ?>>
                                <?php esc_html_e( 'Number', 'uxkode-product-addons-for-woocommerce' ); ?>
                            </option>
                            <option value="textarea" <?php selected( $type, 'textarea' ); ?>>
                                <?php esc_html_e( 'Textarea', 'uxkode-product-addons-for-woocommerce' ); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="addon_status"><?php esc_html_e( 'Status', 'uxkode-product-addons-for-woocommerce' ); ?></label>
                    </th>
                    <td>
                        <select name="addon_status" id="addon_status">
                            <option value="1" <?php selected( $status, 1 ); ?>><?php esc_html_e( 'Active', 'uxkode-product-addons-for-woocommerce' ); ?></option>
                            <option value="0" <?php selected( $status, 0 ); ?>><?php esc_html_e( 'Inactive', 'uxkode-product-addons-for-woocommerce' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <p>
                <button type="submit" class="uxkode-primary-btn">
                    <?php echo $editing_id ? esc_html__( 'Update Add-On', 'uxkode-product-addons-for-woocommerce' ) : esc_html__( 'Create Add-On', 'uxkode-product-addons-for-woocommerce' ); ?>
                </button>
                <?php if ( $editing_id ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=uxkode-product-addons' ) ); ?>" class="uxkode-secondary-btn"><?php esc_html_e( 'Cancel', 'uxkode-product-addons-for-woocommerce' ); ?></a>
                <?php endif; ?>
            </p>
        </form>

        <h3>
            <?php esc_html_e( 'Existing Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?>
        </h3>

        <table class="wp-list-table widefat fixed striped uxkode-addons-table">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Sl No', 'uxkode-product-addons-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Title', 'uxkode-product-addons-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Price', 'uxkode-product-addons-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Input Type', 'uxkode-product-addons-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Status', 'uxkode-product-addons-for-woocommerce' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'uxkode-product-addons-for-woocommerce' ); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if ( ! empty( $addons ) ) : ?>
                <?php $sl = 1; ?>
                <?php foreach ( $addons as $addon ) : ?>
                    <tr>
                        <td><?php echo esc_html( $sl++ ); ?></td>
                        <td><?php echo esc_html( $addon->title ); ?></td>
                        <td><?php echo esc_html( get_woocommerce_currency_symbol() . number_format( (float) $addon->price, 2 ) ); ?></td>
                        <td><?php echo esc_html( ucfirst( $addon->type ) ); ?></td>
                        <td><?php echo $addon->status ? esc_html__( 'Active', 'uxkode-product-addons-for-woocommerce' ) : esc_html__( 'Inactive', 'uxkode-product-addons-for-woocommerce' ); ?></td>
                        <td>
                            <a href="<?php
                                $edit_url = add_query_arg( [
                                    'page'       => 'uxkode-product-addons',
                                    'edit_addon' => $addon->id,
                                    '_wpnonce'   => wp_create_nonce( 'uxkode_edit_addon_' . $addon->id ),
                                ], admin_url( 'admin.php' ) );
                                echo esc_url( $edit_url );
                            ?>" class="button">
                                <?php esc_html_e( 'Edit', 'uxkode-product-addons-for-woocommerce' ); ?>
                            </a>

                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field( 'uxkode_product_addons_nonce', 'uxkode_product_addons_nonce_field' ); ?>
                                <input type="hidden" name="uxkode_product_addons_action" value="delete">
                                <input type="hidden" name="addon_id" value="<?php echo esc_attr( $addon->id ); ?>">
                                <button type="submit" class="button button-link-delete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this Add-On?', 'uxkode-product-addons-for-woocommerce' ) ); ?>');">
                                    <?php esc_html_e( 'Delete', 'uxkode-product-addons-for-woocommerce' ); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6"><?php esc_html_e( 'No Add-Ons are found!', 'uxkode-product-addons-for-woocommerce' ); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>