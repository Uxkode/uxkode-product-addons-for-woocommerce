<?php
/**
 * Custom Buttons Template.
 *
 * Outputs Custom Buttons on the single product page,
 * with user-defined styles rendered as dynamic CSS variables.
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$product_id   = $product->get_id();
$btn_enabled  = get_post_meta( $product_id, '_uxkode_custom_buttons_enabled', true );
$btn_type     = get_post_meta( $product_id, '_uxkode_custom_buttons_type', true );
$uxkode_buttons = get_post_meta( $product_id, '_uxkode_custom_buttons', true );

// Bail early if not enabled or invalid data.
if ( 'yes' !== $btn_enabled || empty( $uxkode_buttons ) || ! is_array( $uxkode_buttons ) ) {
	return;
}

// Limit to single or dual button output.
$wrapper_class = ( 'dual' === $btn_type ) ? 'uxkode-dual-buttons' : 'uxkode-single-button';
$max_buttons   = ( 'dual' === $btn_type ) ? 2 : 1;
$uxkode_buttons  = array_values( array_slice( $uxkode_buttons, 0, $max_buttons, true ) );
?>

<div class="uxkode-custom-buttons-wrapper <?php echo esc_attr( $wrapper_class ); ?>">
	<?php foreach ( $uxkode_buttons as $btn_index => $btn_data ) : ?>
		<?php
		$btn_label  = isset( $btn_data['label'] ) ? esc_html( $btn_data['label'] ) : '';
		$btn_url    = isset( $btn_data['url'] ) ? esc_url( $btn_data['url'] ) : '';
		$btn_target = ( isset( $btn_data['target'] ) && in_array( $btn_data['target'], [ '_self', '_blank' ], true ) )
			? $btn_data['target']
			: '_self';

		if ( empty( $btn_label ) || empty( $btn_url ) ) {
			continue;
		}
		?>
		<a class="uxkode-custom-btn uxkode-custom-btn-<?php echo esc_attr( $btn_index + 1 ); ?>"
			href="<?php echo esc_url( $btn_url ); ?>"
			target="<?php echo esc_attr( $btn_target ); ?>">
			<?php echo esc_html( $btn_label ); ?>
		</a>
	<?php endforeach; ?>
</div>