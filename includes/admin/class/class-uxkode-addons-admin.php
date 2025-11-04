<?php
/**
 * Admin-specific functionalities for Uxkode Product Addons.
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Uxkode_Addons_Admin
 *
 * Handles all admin-related hooks, menu, assets, and WooCommerce product tabs.
 */
class Uxkode_Addons_Admin {

	/**
	 * Initialize admin hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'uxkode_register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'uxkode_enqueue_assets' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'uxkode_admin_custom_buttons_inline_assets' ] );

		add_filter( 'woocommerce_product_data_tabs', [ __CLASS__, 'uxkode_product_addons_data_tab' ] );
		add_action( 'woocommerce_product_data_panels', [ __CLASS__, 'render_uxkode_product_addons_data_tab_content' ] );
		add_action( 'woocommerce_process_product_meta', [ __CLASS__, 'save_uxkode_product_addons_data_tab_meta' ] );
		add_action( 'woocommerce_process_product_meta_variable', [ __CLASS__, 'save_uxkode_product_addons_data_tab_meta' ] );

		add_filter( 'woocommerce_product_data_tabs', [ __CLASS__, 'uxkode_custom_buttons_data_tab' ] );
		add_action( 'woocommerce_product_data_panels', [ __CLASS__, 'render_uxkode_custom_buttons_data_tab_content' ] );
		add_action( 'woocommerce_process_product_meta', [ __CLASS__, 'save_uxkode_custom_buttons_data_tab_meta' ] );
		add_action( 'woocommerce_process_product_meta_variable', [ __CLASS__, 'save_uxkode_custom_buttons_data_tab_meta' ] );
	}

	/**
	 * Register admin menu and submenus.
	 */
	public static function uxkode_register_menu() {
		add_menu_page(
			__( 'Uxkode Addons Dashboard', 'uxkode-product-addons-for-woocommerce' ),
			__( 'Uxkode Addons', 'uxkode-product-addons-for-woocommerce' ),
			'manage_options',
			'uxkode-addons-dashboard',
			[ __CLASS__, 'render_uxkode_addons_dashboard' ],
			UXKODE_ADDONS_ASSETS . 'img/uxkode-addons-dashboard-icon.svg',
			56
		);

		add_submenu_page(
			'uxkode-addons-dashboard',
			__( 'Dashboard', 'uxkode-product-addons-for-woocommerce' ),
			__( 'Dashboard', 'uxkode-product-addons-for-woocommerce' ),
			'manage_options',
			'uxkode-addons-dashboard',
			[ __CLASS__, 'render_uxkode_addons_dashboard' ]
		);

		add_submenu_page(
			'uxkode-addons-dashboard',
			__( 'Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ),
			__( 'Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ),
			'manage_options',
			'uxkode-product-addons',
			[ __CLASS__, 'render_uxkode_product_addons' ]
		);

		add_submenu_page(
			'uxkode-addons-dashboard',
			__( 'Custom Buttons', 'uxkode-product-addons-for-woocommerce' ),
			__( 'Custom Buttons', 'uxkode-product-addons-for-woocommerce' ),
			'manage_options',
			'uxkode-custom-buttons',
			[ __CLASS__, 'render_uxkode_custom_buttons' ]
		);

		add_submenu_page(
			'uxkode-addons-dashboard',
			__( 'Settings', 'uxkode-product-addons-for-woocommerce' ),
			__( 'Settings', 'uxkode-product-addons-for-woocommerce' ),
			'manage_options',
			'uxkode-addons-settings',
			[ __CLASS__, 'render_uxkode_addons_settings' ]
		);
	}

	/**
	 * Register & Enqueue admin CSS and JS assets.
	 */
	public static function uxkode_enqueue_assets( $hook_suffix ) {
		$allowed_pages = [
			'toplevel_page_uxkode-addons-dashboard',
			'uxkode-addons_page_uxkode-product-addons',
			'uxkode-addons_page_uxkode-custom-buttons',
			'uxkode-addons_page_uxkode-addons-settings',
		];
		if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) ) {
			$screen = get_current_screen();
			if ( isset( $screen->post_type ) && 'product' === $screen->post_type ) {
				$allowed_pages[] = $hook_suffix;
			}
		}
		if ( ! in_array( $hook_suffix, $allowed_pages, true ) ) {
			return;
		}

		wp_register_style(
			'uxkode-addons-global-css',
			UXKODE_ADDONS_ASSETS .
			'css/global-style.css',
			[],
			UXKODE_ADDONS_VERSION
		);
		wp_register_style(
			'uxkode-addons-admin-css',
			UXKODE_ADDONS_ASSETS .
			'css/admin-style.css',
			[ 'uxkode-addons-global-css' ],
			UXKODE_ADDONS_VERSION
		);
		wp_register_script(
			'uxkode-addons-admin-js',
			UXKODE_ADDONS_ASSETS .
			'js/admin-script.js',
			[],
			UXKODE_ADDONS_VERSION,
			true
		);

		wp_enqueue_style( 'uxkode-addons-global-css' );
		wp_enqueue_style( 'uxkode-addons-admin-css' );
		wp_enqueue_script( 'uxkode-addons-admin-js' );

		// Inline CSS/JS for Custom Buttons page
		if ( strpos( $hook_suffix, 'uxkode-custom-buttons' ) !== false ) {
			self::uxkode_admin_custom_buttons_inline_assets();
		}
	}

	/**
	 * Inline CSS & JS for Custom Buttons page.
	 */
	public static function uxkode_admin_custom_buttons_inline_assets() {
		// Build inline CSS dynamically for both buttons
		$settings    = get_option( 'uxkode_custom_buttons_styles', [] );
		$custom_btns = [
			1 => $settings['uxkode_button1'] ?? [],
			2 => $settings['uxkode_button2'] ?? [],
		];

		$inline_css = ":root {\n";
		foreach ( $custom_btns as $i => $btn ) {
			$inline_css .= sprintf(
				"--uxkode-custom-btn%d-bg-color: %s;\n  --uxkode-custom-btn%d-text-color: %s;\n  --uxkode-custom-btn%d-border-color: %s;\n  --uxkode-custom-btn%d-bg-hover-color: %s;\n  --uxkode-custom-btn%d-text-hover-color: %s;\n  --uxkode-custom-btn%d-border-hover-color: %s;\n",
				$i,
				esc_attr( $btn['bg_color'] ?? ($i === 1 ? '#ff6b35' : '#ffffff') ),
				$i,
				esc_attr( $btn['text_color'] ?? ($i === 1 ? '#ffffff' : '#ff6b35') ),
				$i,
				esc_attr( $btn['border_color'] ?? ($i === 1 ? '#ff6b35' : '#ff6b35') ),
				$i,
				esc_attr( $btn['bg_hover_color'] ?? '#1890ff' ),
				$i,
				esc_attr( $btn['text_hover_color'] ?? '#ffffff' ),
				$i,
				esc_attr( $btn['border_hover_color'] ?? '#1890ff' )
			);
		}
		$inline_css .= "}";
		wp_add_inline_style( 'uxkode-addons-admin-css', $inline_css );

		$inline_js = "
			document.addEventListener('DOMContentLoaded', function() {
				const url = new URL(window.location.href);
				if (url.searchParams.has('uxkode_reset_notice')) {
					url.searchParams.delete('uxkode_reset_notice');
					window.history.replaceState({}, document.title, url.toString());
				}
			});
		";
		wp_add_inline_script( 'uxkode-addons-admin-js', $inline_js );
	}

	/**
	 * Render the main Dashboard page.
	 */
	public static function render_uxkode_addons_dashboard() {
		require_once UXKODE_ADDONS_PATH . 'includes/admin/admin-dashboard.php';
	}

	/**
	 * Render the Product Add-Ons page.
	 */
	public static function render_uxkode_product_addons() {
		require_once UXKODE_ADDONS_PATH . 'includes/admin/admin-product-addons.php';
	}

	/**
	 * Render the Custom Buttons page.
	 */
	public static function render_uxkode_custom_buttons() {
		require_once UXKODE_ADDONS_PATH . 'includes/admin/admin-custom-buttons.php';
	}

	/**
	 * Render the Settings page.
	 */
	public static function render_uxkode_addons_settings() {
		require_once UXKODE_ADDONS_PATH . 'includes/admin/admin-settings.php';
	}

	/**
	 * Add Product Add-Ons tab to WooCommerce product data tabs.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array Modified tabs.
	 */
	public static function uxkode_product_addons_data_tab( $tabs ) {
		$tabs['uxkode_product_addons'] = [
			'label'    => __( 'Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ),
			'target'   => 'uxkode-product-addons-data',
			'class'    => [ 'show_if_simple', 'show_if_variable' ],
			'priority' => 65,
		];
		return $tabs;
	}

	/**
	 * Render Product Add-Ons tab content with nonce and proper sanitization.
	 */
	public static function render_uxkode_product_addons_data_tab_content() {
		global $post;
		$product_id = $post->ID;

		$enabled = get_post_meta( $product_id, '_uxkode_product_addons_enabled', true );
		$enabled = ( $enabled === 'yes' ) ? 'yes' : 'no';

		$selected_addons = get_post_meta( $product_id, '_uxkode_product_addons_selected', true );
		$selected_addons = is_array( $selected_addons ) ? array_map( 'absint', $selected_addons ) : [];

		if ( ! class_exists( 'Product_Addons_CRUD' ) ) {
			require_once UXKODE_ADDONS_PATH . 'includes/admin/class/class-uxkode-addons-crud.php';
		}
		$all_addons = Product_Addons_CRUD::get_addons( [ 'status' => 1 ] );

		wp_nonce_field( 'uxkode_product_addons_action', 'uxkode_product_addons_nonce' );
		?>
		<div id="uxkode-product-addons-data" class="panel woocommerce_options_panel" style="display: none;">
			<?php
			woocommerce_wp_checkbox( [
				'id'    => '_uxkode_product_addons_enabled',
				'label' => __( 'Enable Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ),
				'value' => $enabled,
			] );
			?>
			<div class="uxkode-available-addons" id="_uxkode_product_addons_selected" style="<?php echo esc_attr( $enabled === 'yes' ? 'flex' : 'none' ); ?>">
				<?php if ( ! empty( $all_addons ) ) : ?>
					<h3><?php esc_html_e( 'Product Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?></h3>
					<span class="uxkode-product-addons-label"><?php esc_html_e( 'Select Add-Ons', 'uxkode-product-addons-for-woocommerce' ); ?></span>
					<select name="_uxkode_product_addons_selected[]" multiple="multiple" class="uxkode-product-addons-select">
						<?php foreach ( $all_addons as $addon ) : ?>
							<option value="<?php echo esc_attr( absint( $addon->id ) ); ?>" <?php selected( in_array( absint( $addon->id ), $selected_addons, true ), true ); ?>>
								<?php
								/* translators: 1: addon title, 2: addon price with currency symbol. */
								echo esc_html(
									sprintf(
										'%1$s (%2$s)',
										$addon->title,
										get_woocommerce_currency_symbol() . number_format( (float) $addon->price, 2 )
									)
								);
								?>
							</option>
						<?php endforeach; ?>
					</select>
					<span class="uxkode-description"><?php esc_html_e( 'Hold Ctrl/Cmd to select multiple Add-Ons.', 'uxkode-product-addons-for-woocommerce' ); ?></span>
				<?php else : ?>
					<p><?php esc_html_e( 'No active Add-Ons are available!', 'uxkode-product-addons-for-woocommerce' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save Product Add-Ons meta data with nonce and proper sanitization.
	 *
	 * @param int $post_id Product ID.
	 */
	public static function save_uxkode_product_addons_data_tab_meta( $post_id ) {
		// Only run for product post type.
		if ( 'product' !== get_post_type( $post_id ) ) {
			return;
		}

		// Avoid autosaves and revisions.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Properly sanitized nonce.
		if ( ! isset( $_POST['uxkode_product_addons_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['uxkode_product_addons_nonce'] ) );

		if ( ! wp_verify_nonce( $nonce, 'uxkode_product_addons_action' ) ) {
			return;
		}
		
		// Enabled checkbox.
		$addons_enabled = isset( $_POST['_uxkode_product_addons_enabled'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_uxkode_product_addons_enabled', $addons_enabled );

		// Selected Add-Ons (array of IDs)
		$addons_selected_ids = [];

		if ( ! empty( $_POST['_uxkode_product_addons_selected'] ) && is_array( $_POST['_uxkode_product_addons_selected'] ) ) {
			$addons_selected_ids = array_map( 'absint', $_POST['_uxkode_product_addons_selected'] );
		}
		update_post_meta( $post_id, '_uxkode_product_addons_selected', $addons_selected_ids );
	}

	/**
	 * Add Custom Buttons tab to WooCommerce product data tabs.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array Modified tabs.
	 */
	public static function uxkode_custom_buttons_data_tab( $tabs ) {
		$tabs['uxkode_custom_buttons'] = [
			'label'    => __( 'Custom Buttons', 'uxkode-product-addons-for-woocommerce' ),
			'target'   => 'uxkode-custom-buttons-data',
			'class'    => [ 'show_if_simple', 'show_if_variable' ],
			'priority' => 66,
		];
		return $tabs;
	}

	/**
	 * Render Custom Buttons tab content with nonce and proper sanitization.
	 */
	public static function render_uxkode_custom_buttons_data_tab_content() {
		global $post;
		$product_id = $post->ID;

		$btn_enabled = get_post_meta( $product_id, '_uxkode_custom_buttons_enabled', true );
		$btn_enabled = ( 'yes' === $btn_enabled ) ? 'yes' : 'no';

		$btn_type = get_post_meta( $product_id, '_uxkode_custom_buttons_type', true );
		$btn_type = in_array( $btn_type, array( 'single', 'dual' ), true ) ? $btn_type : 'single';

		$custom_btn = get_post_meta( $product_id, '_uxkode_custom_buttons', true );
		$custom_btn = is_array( $custom_btn ) ? wp_unslash( $custom_btn ) : array();
		?>
		<div id="uxkode-custom-buttons-data" class="panel woocommerce_options_panel" style="display: none;">
			<?php
			wp_nonce_field( 'uxkode_custom_buttons_action', 'uxkode_custom_buttons_nonce' );

			woocommerce_wp_checkbox(
				array(
					'id'    => '_uxkode_custom_buttons_enabled',
					'label' => __( 'Enable Custom Buttons', 'uxkode-product-addons-for-woocommerce' ),
					'value' => $btn_enabled,
				)
			);
			?>
			<div class="uxkode-available-custom-btn" id="_uxkode_custom_buttons_selected" style="<?php echo esc_attr( ( 'yes' === $btn_enabled ) ? 'flex' : 'none' ); ?>">
				<h3><?php esc_html_e( 'Custom Buttons', 'uxkode-product-addons-for-woocommerce' ); ?></h3>
				<div class="uxkode-custom-btn-type">
					<h4><?php esc_html_e( 'Button Type', 'uxkode-product-addons-for-woocommerce' ); ?></h4>
					<label>
						<input type="radio" name="_uxkode_custom_buttons_type" value="single" <?php checked( $btn_type, 'single' ); ?> />
						<?php esc_html_e( 'Single Button', 'uxkode-product-addons-for-woocommerce' ); ?>
					</label>
					<label>
						<input type="radio" name="_uxkode_custom_buttons_type" value="dual" <?php checked( $btn_type, 'dual' ); ?> />
						<?php esc_html_e( 'Dual Buttons', 'uxkode-product-addons-for-woocommerce' ); ?>
					</label>
				</div>
				<?php
				for ( $i = 1; $i <= 2; $i++ ) :
					$btn_label  = isset( $custom_btn[ $i ]['label'] ) ? $custom_btn[ $i ]['label'] : '';
					$btn_url    = isset( $custom_btn[ $i ]['url'] ) ? $custom_btn[ $i ]['url'] : '';
					$btn_target = isset( $custom_btn[ $i ]['target'] ) ? $custom_btn[ $i ]['target'] : '_self';

					$btn_hidden_style = ( 'single' === $btn_type && 2 === $i ) ? 'display:none;' : '';
					?>
					<div class="uxkode-custom-btn-group" data-button-index="<?php echo esc_attr( $i ); ?>" style="<?php echo esc_attr( $btn_hidden_style ); ?>">
						<div class="uxkode-custom-btn-input">
							<?php
							/* translators: %d: button index (1 or 2). */
							$label_placeholder = sprintf( esc_html__( 'Button %d Text', 'uxkode-product-addons-for-woocommerce' ), $i );
							?>
							<span><?php echo esc_html( $label_placeholder ); ?></span>
							<input type="text" name="_uxkode_custom_buttons[<?php echo esc_attr( $i ); ?>][label]" placeholder="<?php echo esc_attr( $label_placeholder ); ?>" value="<?php echo esc_attr( $btn_label ); ?>" />
						</div>
						<div class="uxkode-custom-btn-input">
							<?php
							/* translators: %d: button index (1 or 2). */
							$url_placeholder = sprintf( esc_html__( 'Enter Button %d URL', 'uxkode-product-addons-for-woocommerce' ), $i );
							?>
							<span>
								<?php 
								/* translators: %d: button index (1 or 2). */
								printf( esc_html__( 'Button %d URL', 'uxkode-product-addons-for-woocommerce' ), esc_html( $i ) ); 
								?>
							</span>
							<input type="url" name="_uxkode_custom_buttons[<?php echo esc_attr( $i ); ?>][url]" placeholder="<?php echo esc_attr( $url_placeholder ); ?>" value="<?php echo esc_url( $btn_url ); ?>" />
						</div>
						<div class="uxkode-custom-btn-input">
							<?php
							/* translators: %d: button index (1 or 2). */
							$target_label = sprintf( esc_html__( 'Button %d Target', 'uxkode-product-addons-for-woocommerce' ), esc_html( $i ) );
							?>
							<span><?php echo esc_html( $target_label ); ?></span>
							<select name="_uxkode_custom_buttons[<?php echo esc_attr( $i ); ?>][target]">
								<option value="_self" <?php selected( $btn_target, '_self' ); ?>><?php esc_html_e( 'Same Tab', 'uxkode-product-addons-for-woocommerce' ); ?></option>
								<option value="_blank" <?php selected( $btn_target, '_blank' ); ?>><?php esc_html_e( 'New Tab', 'uxkode-product-addons-for-woocommerce' ); ?></option>
							</select>
						</div>
					</div>
					<div class="uxkode-separator"></div>
				<?php endfor; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Save Custom Buttons meta data with nonce and proper sanitization.
	 *
	 * @param int $post_id Product ID.
	 */
	public static function save_uxkode_custom_buttons_data_tab_meta( $post_id ) {

		// Sanitize $post_id.
		$post_id = absint( $post_id );

		// Only run for product post type.
		if ( 'product' !== get_post_type( $post_id ) ) {
			return;
		}

		// Avoid autosaves and revisions.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['uxkode_custom_buttons_nonce'] ) ) {
			return;
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['uxkode_custom_buttons_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'uxkode_custom_buttons_action' ) ) {
			return;
		}

		// Enabled checkbox.
		$btn_enabled = isset( $_POST['_uxkode_custom_buttons_enabled'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_uxkode_custom_buttons_enabled', $btn_enabled );

		// Button type.
		$btn_type = 'single';
		if ( isset( $_POST['_uxkode_custom_buttons_type'] ) ) {
			$type_raw  = sanitize_text_field( wp_unslash( $_POST['_uxkode_custom_buttons_type'] ) );
			$btn_type  = $type_raw;
			if ( ! in_array( $btn_type, [ 'single', 'dual' ], true ) ) {
				$btn_type = 'single';
			}
		}
		update_post_meta( $post_id, '_uxkode_custom_buttons_type', $btn_type );

		// Custom buttons array.
		$uxkode_buttons = [];

		if ( isset( $_POST['_uxkode_custom_buttons'] ) && is_array( $_POST['_uxkode_custom_buttons'] ) ) {
			// Sanitize every value in the array before unslashing.
			$sanitized_buttons = map_deep( wp_unslash( $_POST['_uxkode_custom_buttons'] ), 'sanitize_text_field' );

			foreach ( $sanitized_buttons as $btn_index => $btn_data ) {
				if ( ! is_array( $btn_data ) ) {
					continue;
				}

				$label  = isset( $btn_data['label'] ) ? $btn_data['label'] : '';
				$url    = isset( $btn_data['url'] ) ? esc_url_raw( $btn_data['url'] ) : '';
				$target = isset( $btn_data['target'] ) ? sanitize_key( $btn_data['target'] ) : '_self';
				if ( ! in_array( $target, [ '_self', '_blank' ], true ) ) {
					$target = '_self';
				}

				$uxkode_buttons[ $btn_index ] = [
					'label'  => $label,
					'url'    => $url,
					'target' => $target,
				];
			}
		}
		update_post_meta( $post_id, '_uxkode_custom_buttons', $uxkode_buttons );
	}
}