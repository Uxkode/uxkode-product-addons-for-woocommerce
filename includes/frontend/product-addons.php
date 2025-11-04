<?php
/**
 * Product Add-Ons Template.
 *
 * Renders optional Product Add-Ons on the single product page.
 * Handles checkbox toggle and input fields for user-provided data.
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail early if not on single product page or CRUD class is missing.
if ( ! is_product() || ! class_exists( 'Product_Addons_CRUD' ) ) {
	return;
}

global $product;

$product_id = $product->get_id();

// Get product add-on settings and selected add-ons.
$enabled  = get_post_meta( $product_id, '_uxkode_product_addons_enabled', true );
$selected = get_post_meta( $product_id, '_uxkode_product_addons_selected', true );
$selected = is_array( $selected ) ? $selected : [];

// Bail early if add-ons are not enabled or none are selected.
if ( 'yes' !== $enabled || empty( $selected ) ) {
	return;
}

// Retrieve add-ons data by IDs.
$addons = Product_Addons_CRUD::get_addons_by_ids( $selected );

// Bail early if no valid add-ons found.
if ( empty( $addons ) ) {
	return;
}
?>
<div class="uxkode-addons-section">
	<div class="uxkode-heading">
		<h3><?php esc_html_e( 'Optional Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?></h3>
		<p><?php esc_html_e( 'Select Your Product Add-Ons below.', 'uxkode-product-addons-for-woocommerce' ); ?></p>
	</div>

	<div class="uxkode-addons-wrapper">
		<?php foreach ( $addons as $addon ) : ?>
			<?php
			$addon_id   = (int) $addon->id;
			$field_name = 'uxkode_product_addons[' . $addon_id . '][value]';
			?>
			<div class="uxkode-addon-wrapper">
				<div class="uxkode-checkbox-wrapper">
					<input type="checkbox"
						class="uxkode-addon-toggle"
						id="uxkode-addon-<?php echo esc_attr( $addon_id ); ?>"
						name="uxkode_product_addons[<?php echo esc_attr( $addon_id ); ?>][enabled]"
						data-addon-id="<?php echo esc_attr( $addon_id ); ?>"
					/>
					
					<label for="uxkode-addon-<?php echo esc_attr( $addon_id ); ?>">
						<?php echo esc_html( $addon->title ); ?> 
						(<?php echo wp_kses_post( wc_price( (float) $addon->price ) ); ?>)
					</label>
				</div>

    			<?php if ( $addon->type !== 'none' ) : ?>
				<div class="uxkode-addon-field" style="display:none;">
					<?php
					// Render input field based on add-on type.
					switch ( $addon->type ) {
						case 'number':
							?>
							<input type="number"
								name="<?php echo esc_attr( $field_name ); ?>"
								placeholder="<?php echo esc_attr( $addon->title ); ?>"
								class="uxkode-input-field">
							<?php
							break;

						case 'textarea':
							?>
							<textarea
								name="<?php echo esc_attr( $field_name ); ?>"
								rows="3"
								placeholder="<?php echo esc_attr( $addon->title ); ?>"
								class="uxkode-textarea-field"></textarea>
							<?php
							break;

						default:
							?>
							<input type="text"
								name="<?php echo esc_attr( $field_name ); ?>"
								placeholder="<?php echo esc_attr( $addon->title ); ?>"
								class="uxkode-input-field">
							<?php
							break;
					}
					?>
				</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>