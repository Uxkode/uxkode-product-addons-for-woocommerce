<?php
/**
 * Plugin Name: Uxkode Product Addons for WooCommerce
 * Requires Plugins: woocommerce
 * Description: Add unlimited custom Product Add-Ons with optional customer inputs and single or dual Custom Buttons to your WooCommerce products.
 * Version: 1.0.1
 * Author: Uxkode
 * Author URI: https://www.uxkode.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Tags: woocommerce, product-addons, custom-buttons, woocommerce-addons, uxkode-addons
 * Requires at least: 5.8
 * Tested up to: 6.8
 * Requires PHP: 7.4
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -------------------
// Plugin Constants.
// -------------------
if ( ! defined( 'UXKODE_ADDONS_NAME' ) ) {
	define( 'UXKODE_ADDONS_NAME', 'Uxkode Product Addons for WooCommerce' );
}
if ( ! defined( 'UXKODE_ADDONS_SLUG' ) ) {
	define( 'UXKODE_ADDONS_SLUG', 'uxkode-product-addons-for-woocommerce' );
}
if ( ! defined( 'UXKODE_ADDONS_VERSION' ) ) {
	define( 'UXKODE_ADDONS_VERSION', '1.0.1' );
}
if ( ! defined( 'UXKODE_ADDONS_DB_VERSION' ) ) {
	define( 'UXKODE_ADDONS_DB_VERSION', '1.0.0' );
}
if ( ! defined( 'UXKODE_ADDONS_FILE' ) ) {
	define( 'UXKODE_ADDONS_FILE', __FILE__ );
}
if ( ! defined( 'UXKODE_ADDONS_PATH' ) ) {
	define( 'UXKODE_ADDONS_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'UXKODE_ADDONS_URL' ) ) {
	define( 'UXKODE_ADDONS_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'UXKODE_ADDONS_ASSETS' ) ) {
	define( 'UXKODE_ADDONS_ASSETS', UXKODE_ADDONS_URL . 'assets/' );
}

// Minimum requirements.
if ( ! defined( 'UXKODE_ADDONS_MIN_PHP' ) ) {
	define( 'UXKODE_ADDONS_MIN_PHP', '7.4' );
}
if ( ! defined( 'UXKODE_ADDONS_MIN_WP' ) ) {
	define( 'UXKODE_ADDONS_MIN_WP', '5.8' );
}
if ( ! defined( 'UXKODE_ADDONS_MIN_WC' ) ) {
	define( 'UXKODE_ADDONS_MIN_WC', '6.0' );
}
if ( ! defined( 'UXKODE_ADDONS_MIN_MYSQL' ) ) {
	define( 'UXKODE_ADDONS_MIN_MYSQL', '5.6' );
}

/**
 * Check plugin requirements.
 *
 * @since 1.0.0
 *
 * @global wpdb  $wpdb  WordPress database abstraction object.
 * @global string $wp_version The WordPress version string.
 *
 * @return array List of errors if requirements are not met.
 */
function uxkode_addons_check_requirements() {
	global $wpdb, $wp_version;

	$errors = array();

	// PHP.
	if ( version_compare( PHP_VERSION, UXKODE_ADDONS_MIN_PHP, '<' ) ) {
		$errors[] = sprintf( 'PHP %s or higher is required.', UXKODE_ADDONS_MIN_PHP );
	}

	// WordPress core version.
	$installed_wp_version = isset( $wp_version ) ? $wp_version : get_bloginfo( 'version' );

	if ( version_compare( $installed_wp_version, UXKODE_ADDONS_MIN_WP, '<' ) ) {
		$errors[] = sprintf( 'WordPress %s or higher is required.', UXKODE_ADDONS_MIN_WP );
	}

	// MySQL / MariaDB.
	if ( version_compare( $wpdb->db_version(), UXKODE_ADDONS_MIN_MYSQL, '<' ) ) {
		$errors[] = sprintf( 'MySQL %s or higher is required.', UXKODE_ADDONS_MIN_MYSQL );
	}

	return $errors;
}

/**
 * Show notice if WooCommerce is missing or inactive.
 *
 * @since 1.0.0
 */
function uxkode_addons_wc_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$plugin = filter_input( INPUT_GET, 'plugin', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	if ( 'install-plugin' === $action && 'woocommerce' === $plugin ) {
		return;
	}

	if ( ! file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
		$install_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'install-plugin',
					'plugin' => 'woocommerce',
				),
				self_admin_url( 'update.php' )
			),
			'install-plugin_woocommerce'
		);

		printf(
			'<div class="notice notice-error"><p><strong>%s</strong></p><p><a href="%s" class="button button-primary">%s</a></p></div>',
			esc_html( 'Uxkode Product Addons for WooCommerce requires WooCommerce to be installed.' ),
			esc_url( $install_url ),
			esc_html( 'Install WooCommerce' )
		);
		return;
	}

	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$activate_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'activate',
					'plugin' => 'woocommerce/woocommerce.php',
				),
				self_admin_url( 'plugins.php' )
			),
			'activate-plugin_woocommerce/woocommerce.php'
		);

		printf(
			'<div class="notice notice-warning"><p><strong>%s</strong></p><p><a href="%s" class="button button-primary">%s</a></p></div>',
			esc_html( 'Uxkode Product Addons for WooCommerce is not working because WooCommerce is inactive.' ),
			esc_url( $activate_url ),
			esc_html( 'Activate WooCommerce' )
		);
	}
}
add_action( 'admin_notices', 'uxkode_addons_wc_notice' );

/**
 * Display general admin notices for PHP/WP/MySQL requirements.
 *
 * @since 1.0.0
 */
function uxkode_addons_admin_notice() {
	$errors = uxkode_addons_check_requirements();

	if ( empty( $errors ) ) {
		return;
	}

	echo '<div class="notice notice-error"><p><strong>' . esc_html( 'Uxkode Product Addons cannot run because:' ) . '</strong></p><ul>';

	foreach ( $errors as $error ) {
		echo '<li>' . esc_html( $error ) . '</li>';
	}

	echo '</ul></div>';
}
add_action( 'admin_notices', 'uxkode_addons_admin_notice' );

/**
 * Plugin activation: create or update database table.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 */
function uxkode_addons_activate() {
	$errors = uxkode_addons_check_requirements();

	if ( ! empty( $errors ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			'<p>' . implode( '<br>', array_map( 'esc_html', $errors ) ) . '</p>',
			esc_html( 'Plugin Activation Error' ),
			array( 'back_link' => true )
		);
	}

	global $wpdb;

	$table           = $wpdb->prefix . 'uxkode_woo_addons';
	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$sql = "CREATE TABLE {$table} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		title VARCHAR(255) NOT NULL,
		type VARCHAR(50) NOT NULL DEFAULT 'none',
		price DECIMAL(10,2) NOT NULL DEFAULT 0,
		status TINYINT(1) NOT NULL DEFAULT 1,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
	) {$charset_collate};";

	dbDelta( $sql );

	$current_db_version = get_option( 'uxkode_addons_db_version', '0' );
	if ( version_compare( $current_db_version, UXKODE_ADDONS_DB_VERSION, '<' ) ) {
		update_option( 'uxkode_addons_db_version', UXKODE_ADDONS_DB_VERSION );
	}
}
register_activation_hook( __FILE__, 'uxkode_addons_activate' );

/**
 * Add custom body class to admin pages.
 *
 * @since 1.0.0
 *
 * @param string $classes Existing admin body classes.
 * @return string
 */
function uxkode_add_admin_body_class( $classes ) {
	if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
		return $classes;
	}

	$screen = get_current_screen();

	if ( $screen && isset( $screen->id ) && false !== strpos( $screen->id, 'uxkode-' ) ) {
		$classes .= ' uxkode-addons-body';
	}

	return $classes;
}
add_filter( 'admin_body_class', 'uxkode_add_admin_body_class' );

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 */
function uxkode_addons_init_plugin() {
	$errors = uxkode_addons_check_requirements();

	if ( ! empty( $errors ) || ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$loader_path = UXKODE_ADDONS_PATH . 'includes/class/class-uxkode-addons-loader.php';
	if ( file_exists( $loader_path ) ) {
		require_once $loader_path;
		if ( class_exists( 'Uxkode_Addons_Loader' ) && method_exists( 'Uxkode_Addons_Loader', 'init' ) ) {
			Uxkode_Addons_Loader::init();
		}
	}
}
add_action( 'plugins_loaded', 'uxkode_addons_init_plugin' );

/**
 * Add Settings link before the Deactivate link.
 *
 * @since 1.0.0
 *
 * @param array $links Plugin action links.
 * @return array
 */
function uxkode_addons_action_links( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=uxkode-addons-dashboard' ) ) . '">' . esc_html( 'Dashboard' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'uxkode_addons_action_links' );

/**
 * Add Extra Meta links on the plugin page.
 *
 * @since 1.0.0
 *
 * @param array  $links Existing row meta links.
 * @param string $file  Plugin file.
 * @return array
 */
function uxkode_addons_row_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) === $file ) {
		$row_meta = array(
			'docs' => '<a href="https://uxkode.github.io/docs-uxkode-product-addons-for-woocommerce/" target="_blank" rel="noopener noreferrer">' . esc_html( 'Read Documentation' ) . '</a>',
		);
		$links    = array_merge( $links, $row_meta );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'uxkode_addons_row_meta', 10, 2 );
