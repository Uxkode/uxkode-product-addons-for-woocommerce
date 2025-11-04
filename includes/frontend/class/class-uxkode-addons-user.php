<?php
/**
 * Frontend User Functionality for Uxkode Product Addons
 *
 * Handles rendering of product add-ons, validation, cart & order meta,
 * and dynamic pricing for WooCommerce products.
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Uxkode_Addons_User
 *
 * Responsible for handling all frontend-related hooks, scripts, and dynamic
 * product add-ons functionality.
 */
class Uxkode_Addons_User {

	/**
	 * Initialize frontend hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'uxkode_enqueue_assets' ] );

		add_action( 'woocommerce_before_add_to_cart_button', [ __CLASS__, 'render_uxkode_product_addons' ] );
		add_filter( 'woocommerce_add_to_cart_validation', [ __CLASS__, 'validate_uxkode_product_addons' ], 10, 3 );
		add_filter( 'woocommerce_add_cart_item_data', [ __CLASS__, 'save_uxkode_product_addons_cart_data' ], 10, 2 );
		add_filter( 'woocommerce_get_item_data', [ __CLASS__, 'display_uxkode_product_addons_cart_data' ], 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', [ __CLASS__, 'save_uxkode_product_addons_order_meta' ], 10, 4 );
		add_action( 'woocommerce_before_calculate_totals', [ __CLASS__, 'uxkode_product_addons_adjust_price' ], 10 );

		add_action( 'woocommerce_before_add_to_cart_button', [ __CLASS__, 'render_uxkode_custom_buttons' ] );
	}

	/**
	 * Register & Enqueue frontend CSS and JS assets.
	 *
	 * @return void
	 */
	public static function uxkode_enqueue_assets() {
		wp_register_style(
			'uxkode-addons-global-css',
			UXKODE_ADDONS_ASSETS . 'css/global-style.css',
			[],
			UXKODE_ADDONS_VERSION
		);
		wp_register_style(
			'uxkode-addons-css',
			UXKODE_ADDONS_ASSETS . 'css/style.css',
			[ 'uxkode-addons-global-css' ],
			UXKODE_ADDONS_VERSION
		);
		wp_register_script(
			'uxkode-addons-js',
			UXKODE_ADDONS_ASSETS . 'js/script.js',
			[],
			UXKODE_ADDONS_VERSION,
			true
		);

		wp_enqueue_style( 'uxkode-addons-global-css' );
		wp_enqueue_style( 'uxkode-addons-css' );
		wp_enqueue_script( 'uxkode-addons-js' );

		self::uxkode_custom_buttons_dynamic_styles();
	}

	/**
	 * Output dynamic styles for Custom Buttons.
	 *
	 * Reads user-defined button styles from the database
	 * and injects them as inline CSS variables.
	 *
	 * @return void
	 */
	public static function uxkode_custom_buttons_dynamic_styles() {
		$settings = get_option( 'uxkode_custom_buttons_styles', [] );

		if ( empty( $settings ) ) {
			return;
		}

		$custom_btn1 = $settings['uxkode_button1'] ?? [];
		$custom_btn2 = $settings['uxkode_button2'] ?? [];

		$dynamic_styles = ":root {\n";

		// Button 1.
		foreach ( [
			'bg_color' => 'bg-color',
			'text_color' => 'text-color',
			'border_color' => 'border-color',
			'bg_hover_color' => 'bg-hover-color',
			'text_hover_color' => 'text-hover-color',
			'border_hover_color' => 'border-hover-color',
		] as $key => $css_var ) {
			if ( ! empty( $custom_btn1[ $key ] ) ) {
				$dynamic_styles .= "  --uxkode-custom-btn1-{$css_var}: " . esc_attr( $custom_btn1[ $key ] ) . ";\n";
			}
		}

		// Button 2.
		foreach ( [
			'bg_color' => 'bg-color',
			'text_color' => 'text-color',
			'border_color' => 'border-color',
			'bg_hover_color' => 'bg-hover-color',
			'text_hover_color' => 'text-hover-color',
			'border_hover_color' => 'border-hover-color',
		] as $key => $css_var ) {
			if ( ! empty( $custom_btn2[ $key ] ) ) {
				$dynamic_styles .= "  --uxkode-custom-btn2-{$css_var}: " . esc_attr( $custom_btn2[ $key ] ) . ";\n";
			}
		}

		$dynamic_styles .= "}";

		wp_add_inline_style( 'uxkode-addons-css', $dynamic_styles );
	}

	/**
	 * Render Product Add-Ons frontend template.
	 *
	 * @return void
	 */
	public static function render_uxkode_product_addons() {
		require_once UXKODE_ADDONS_PATH . 'includes/frontend/product-addons.php';

		// WP Nonce field for security.
		wp_nonce_field( 'uxkode_product_addons_action', 'uxkode_product_addons_nonce' );
	}

	/**
	 * Validate Add-Ons fields during add-to-cart.
	 *
	 * @param bool $passed     Whether validation passed.
	 * @param int  $product_id Product ID.
	 * @param int  $quantity   Quantity being added.
	 * @return bool
	 */
	public static function validate_uxkode_product_addons( $passed, $product_id, $quantity ) {
		// Verify nonce before processing.
		if ( ! isset( $_POST['uxkode_product_addons_nonce'] ) || 
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['uxkode_product_addons_nonce'] ) ), 'uxkode_product_addons_action' ) ) {
			return $passed;
		}
		if ( empty( $_POST['uxkode_product_addons'] ) || ! is_array( $_POST['uxkode_product_addons'] ) ) {
			return $passed;
		}

		// Sanitize, validate, and safely fetch Product Add-Ons from POST.
		$rawProductAddons = [];
		if ( isset( $_POST['uxkode_product_addons'] ) && is_array( $_POST['uxkode_product_addons'] ) ) {
			$posted = map_deep( wp_unslash( $_POST['uxkode_product_addons'] ), 'sanitize_text_field' );

			foreach ( $posted as $id => $addon ) {
				$id = absint( $id );
				if ( ! is_array( $addon ) ) {
					continue;
				}

				$enabled = ! empty( $addon['enabled'] ) ? 1 : 0;
				$value   = isset( $addon['value'] ) ? sanitize_text_field( $addon['value'] ) : '';

				$addon_obj = Product_Addons_CRUD::get_addon( $id );
				if ( ! $addon_obj || $addon_obj->type === 'none' ) {
					continue;
				}

				$rawProductAddons[ $id ] = [
					'enabled' => $enabled,
					'value'   => $value,
					'title'   => $addon_obj->title,
					'type'    => $addon_obj->type,
					'price'   => (float) $addon_obj->price,
				];
			}
		}

		foreach ( $rawProductAddons as $id => $addon ) {
			if ( isset( $addon['enabled'] ) ) {
				$value = isset( $addon['value'] ) ? sanitize_text_field( $addon['value'] ) : '';

				$addon_obj = Product_Addons_CRUD::get_addon( $id );

				if ( ! $addon_obj || $addon_obj->type === 'none' ) {
					continue;
				}

				if ( '' === $value ) {
					wc_add_notice(
						sprintf(
							/* translators: %s: value of selected Add-On */
							__( 'Please provide a value for "%s".', 'uxkode-product-addons-for-woocommerce' ),
							$addon_obj->title
						),
						'error'
					);
					return false;
				}
			}
		}

		return $passed;
	}

	/**
	 * Save selected Add-Ons to cart item data.
	 *
	 * @param array $cart_item_data Existing cart item data.
	 * @param int   $product_id     Product ID.
	 * @return array
	 */
	public static function save_uxkode_product_addons_cart_data( $cart_item_data, $product_id ) {
		if ( ! isset( $_POST['uxkode_product_addons_nonce'] ) || 
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['uxkode_product_addons_nonce'] ) ), 'uxkode_product_addons_action' ) ) {
			return $cart_item_data;
		}
		if ( empty( $_POST['uxkode_product_addons'] ) || ! is_array( $_POST['uxkode_product_addons'] ) ) {
			return $cart_item_data;
		}
		$saved = [];

		// Sanitize and validate before saving to cart
		$rawProductAddons = [];
		if ( isset( $_POST['uxkode_product_addons'] ) && is_array( $_POST['uxkode_product_addons'] ) ) {
			$posted = map_deep( wp_unslash( $_POST['uxkode_product_addons'] ), 'sanitize_text_field' );

			foreach ( $posted as $id => $addon ) {
				$id = absint( $id );
				if ( ! is_array( $addon ) ) {
					continue;
				}

				$enabled = ! empty( $addon['enabled'] ) ? 1 : 0;
				$value   = isset( $addon['value'] ) ? sanitize_text_field( $addon['value'] ) : '';

				$addon_obj = Product_Addons_CRUD::get_addon( $id );
				if ( ! $addon_obj || $addon_obj->type === 'none' ) {
					continue;
				}

				$rawProductAddons[ $id ] = [
					'enabled' => $enabled,
					'value'   => $value,
					'title'   => $addon_obj->title,
					'type'    => $addon_obj->type,
					'price'   => (float) $addon_obj->price,
				];
			}
		}

		foreach ( $rawProductAddons as $id => $addon ) {
			if ( isset( $addon['enabled'] ) ) {
				$addon_obj = Product_Addons_CRUD::get_addon( $id );

				$saved[ $id ] = [
					'title' => $addon_obj->title,
					'type'  => $addon_obj->type,
					'price' => (float) $addon_obj->price,
					'value' => isset( $addon['value'] ) ? sanitize_text_field( $addon['value'] ) : '',
				];
			}
		}

		if ( ! empty( $saved ) ) {
			$cart_item_data['_uxkode_product_addons_selected'] = $saved;
		}

		// Store original price to ensure proper price recalculations.
		if ( ! isset( $cart_item_data['uxkode_original_price'] ) ) {
			if ( isset( $_POST['variation_id'] ) && absint( wp_unslash( $_POST['variation_id'] ) ) > 0 ) {
				$variation = wc_get_product( absint( $_POST['variation_id'] ) );

				if ( $variation ) {
					$cart_item_data['uxkode_original_price'] = (float) $variation->get_price( 'edit' );
				}
			} else {
				$product = wc_get_product( $product_id );

				if ( $product ) {
					$cart_item_data['uxkode_original_price'] = (float) $product->get_price( 'edit' );
				}
			}
		}

		return $cart_item_data;
	}

	/**
	 * Display selected add-ons in cart and checkout.
	 *
	 * @param array $item_data Cart item display data.
	 * @param array $cart_item Cart item object.
	 * @return array
	 */
	public static function display_uxkode_product_addons_cart_data( $item_data, $cart_item ) {
		if ( empty( $cart_item['_uxkode_product_addons_selected'] ) ) {
			return $item_data;
		}

		foreach ( $cart_item['_uxkode_product_addons_selected'] as $addon ) {

			$display_value = '';
			if ( isset( $addon['type'] ) && $addon['type'] !== 'none' ) {
				$display_value = $addon['value'] ? wp_kses_post( $addon['value'] ) : esc_html__( '-', 'uxkode-product-addons-for-woocommerce' );
			}

			$item_data[] = [
				'name'  => $addon['title'] . ' (+' . wp_strip_all_tags( wc_price( $addon['price'] ) ) . ')',
				'value' => $display_value,
			];
		}

		return $item_data;
	}

	/**
	 * Save selected Add-Ons as order item meta.
	 *
	 * @param WC_Order_Item_Product $item          Order item object.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $values        Cart item values.
	 * @param WC_Order              $order         Order object.
	 * @return void
	 */
	public static function save_uxkode_product_addons_order_meta( $item, $cart_item_key, $values, $order ) {
		if ( empty( $values['_uxkode_product_addons_selected'] ) ) {
			return;
		}

		foreach ( $values['_uxkode_product_addons_selected'] as $addon ) {
			$item->add_meta_data(
				$addon['title']. ' (+' . wp_strip_all_tags( wc_price( $addon['price'])) . ')',
				$addon['value'] ? $addon['value'] : esc_html__( '-', 'uxkode-product-addons-for-woocommerce' )
			);
		}
	}

	/**
	 * Adjust the product price in the cart based on selected add-ons.
	 *
	 * @param WC_Cart $cart WooCommerce cart object.
	 * @return void
	 */
	public static function uxkode_product_addons_adjust_price( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( empty( $cart_item['_uxkode_product_addons_selected'] ) ) {
				continue;
			}

			$product = $cart_item['data'];

			$base = isset( $cart_item['uxkode_original_price'] )
				? (float) $cart_item['uxkode_original_price']
				: (float) $product->get_price( 'edit' );

			$extra = 0;

			foreach ( $cart_item['_uxkode_product_addons_selected'] as $addon ) {
				$extra += (float) $addon['price'];
			}

			$product->set_price( $base + $extra );
		}
	}

	/**
	 * Render Custom Buttons frontend template.
	 *
	 * @return void
	 */
	public static function render_uxkode_custom_buttons() {
		require_once UXKODE_ADDONS_PATH . 'includes/frontend/custom-buttons.php';
	}
}

// Initialize the frontend class.
Uxkode_Addons_User::init();