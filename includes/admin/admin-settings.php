<?php
/**
 * Admin Settings Page
 *
 * Displays plugin information, documentation, and support options.
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the admin header template.
 */
require_once UXKODE_ADDONS_PATH . 'includes/admin/admin-header.php';
?>

<div class="uxkode-admin-content-wrapper">
	<h1>
		<?php esc_html_e( 'Uxkode Product Addons Settings', 'uxkode-product-addons-for-woocommerce' ); ?>
	</h1>

	<p>
		<?php esc_html_e(
			'Welcome to the Admin Settings page. Manage plugin info, documentation, and support links here.',
			'uxkode-product-addons-for-woocommerce'
		); ?>
	</p>

	<!-- Plugin Info Section -->
	<div class="uxkode-admin-section">
		<h2>
			<?php esc_html_e( 'Plugin Info', 'uxkode-product-addons-for-woocommerce' ); ?>
		</h2>

		<p>
			<?php
			echo wp_kses_post(
				__(
					'<strong>Uxkode Product Addons for WooCommerce</strong> allows you to create unlimited custom Product Add-Ons with custom pricing and optional customer inputs. It also lets you add customizable single or dual Custom CTA Buttons to boost engagement and conversions.',
					'uxkode-product-addons-for-woocommerce'
				)
			);
			?>
		</p>

	<p>
		<?php
		printf(
			/* translators: %s: The plugin version number. */
			esc_html__( 'Version: %s', 'uxkode-product-addons-for-woocommerce' ),
			defined( 'UXKODE_ADDONS_VERSION' ) ? esc_html( UXKODE_ADDONS_VERSION ) : '1.0.0'
		);
		?>
	</p>

	<p>
		<?php
		printf(
			/* translators: %s: The plugin author name. */
			esc_html__( 'Author: %s', 'uxkode-product-addons-for-woocommerce' ),
			esc_html( 'Uxkode' )
		);
		?>
	</p>
	</div>

	<!-- Documentation Section -->
	<div class="uxkode-admin-section">
		<h2>
			<?php esc_html_e( 'Documentation', 'uxkode-product-addons-for-woocommerce' ); ?>
		</h2>

		<p>
			<?php esc_html_e(
				'Read the documentation to learn how to use all features of the plugin.',
				'uxkode-product-addons-for-woocommerce'
			); ?>
		</p>

		<div>
			<a href="https://uxkode.github.io/docs-uxkode-product-addons-for-woocommerce/" target="_blank" class="uxkode-primary-btn">
				<?php esc_html_e( 'View Documentation', 'uxkode-product-addons-for-woocommerce' ); ?>
			</a>
		</div>
	</div>

	<!-- Support Section -->
	<div class="uxkode-admin-section">
		<h2>
			<?php esc_html_e( 'Get Support', 'uxkode-product-addons-for-woocommerce' ); ?>
		</h2>

		<p>
			<?php esc_html_e(
				'Need help? Visit our support forum or contact us directly.',
				'uxkode-product-addons-for-woocommerce'
			); ?>
		</p>

		<div class="uxkode-flex">
			<a href="https://wordpress.org/support/plugin/uxkode-product-addons-for-woocommerce/" target="_blank" class="uxkode-primary-btn">
				<?php esc_html_e( 'WordPress.org Support', 'uxkode-product-addons-for-woocommerce' ); ?>
			</a>

			<a href="mailto:uzzal@uxkode.com" class="uxkode-secondary-btn">
				<?php esc_html_e( 'Contact Support', 'uxkode-product-addons-for-woocommerce' ); ?>
			</a>
		</div>
	</div>
</div>