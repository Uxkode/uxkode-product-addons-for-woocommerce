<?php
/**
 * Main Uxkode Product Addons Plugin Loader
 *
 * Handles initialization of admin and frontend functionalities.
 *
 * @package Uxkode_Addons_WooCommerce
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Uxkode_Addons_Loader
 *
 * Responsible for loading the plugin's admin and frontend functionality.
 */
class Uxkode_Addons_Loader {

	/**
	 * Initialize the plugin.
	 *
	 * Loads admin or frontend classes based on the current context.
	 *
	 * @return void
	 */
	public static function init() {

		// Load admin functionality.
		if ( is_admin() ) {

			if ( ! class_exists( 'Uxkode_Addons_Admin' ) ) {
				require_once UXKODE_ADDONS_PATH . 'includes/admin/class/class-uxkode-addons-admin.php';
			}

			if ( ! class_exists( 'Product_Addons_CRUD' ) ) {
				require_once UXKODE_ADDONS_PATH . 'includes/admin/class/class-product-addons-crud.php';
			}

			Uxkode_Addons_Admin::init();

		} else {

			// Load frontend functionality.
			if ( ! class_exists( 'Product_Addons_CRUD' ) ) {
				require_once UXKODE_ADDONS_PATH . 'includes/admin/class/class-product-addons-crud.php';
			}

			if ( ! class_exists( 'Uxkode_Addons_User' ) ) {
				require_once UXKODE_ADDONS_PATH . 'includes/frontend/class/class-uxkode-addons-user.php';
			}

			Uxkode_Addons_User::init();
		}
	}
}