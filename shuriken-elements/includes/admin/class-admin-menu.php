<?php
namespace ShurikenElements\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin Menu Class
 *
 * Responsible for creating the WP Dashboard menu.
 *
 * @since 1.0.0
 */
class Class_Admin_Menu {

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Class_Admin_Menu The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Class_Admin_Menu An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
	}

	/**
	 * Register Admin Menu
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function register_admin_menu() {
		add_menu_page(
			esc_html__( 'Shuriken Elements', 'shuriken-elements' ), // Page title
			esc_html__( 'Shuriken Elements', 'shuriken-elements' ), // Menu title
			'manage_options',                                       // Capability
			'shuriken-elements',                                    // Menu slug
			[ $this, 'admin_page_html' ],                           // Callback function
			'dashicons-superhero',                                  // Icon URL or dashicon (generic superhero for now)
			58                                                      // Position
		);
	}

	/**
	 * Admin Page HTML
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_page_html() {
		// Include the view file for the settings page
		require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/views/settings-page.php';
	}
}
