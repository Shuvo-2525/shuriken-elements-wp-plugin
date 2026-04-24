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
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
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

        add_submenu_page(
			'shuriken-elements',
			esc_html__( 'URL Management', 'shuriken-elements' ),
			esc_html__( 'URL Management', 'shuriken-elements' ),
			'manage_options',
			'shuriken-url-management',
			[ $this, 'url_management_page_html' ]
		);

        add_submenu_page(
			'shuriken-elements',
			esc_html__( 'Redirect Management', 'shuriken-elements' ),
			esc_html__( 'Redirect Management', 'shuriken-elements' ),
			'manage_options',
			'shuriken-redirect-management',
			[ $this, 'redirect_management_page_html' ]
		);
	}

    /**
     * Enqueue styles for the new settings page.
     */
    public function enqueue_scripts( $hook ) {
        if ( 'shuriken-elements_page_shuriken-url-management' === $hook || 'shuriken-elements_page_shuriken-redirect-management' === $hook ) {
            wp_enqueue_style( 'shuriken-admin-checkout-editor', SHURIKEN_ELEMENTS_URL . 'assets/css/admin-checkout-editor.css', [], '1.0.0' );
        }
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

    /**
	 * URL Management Page HTML
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 */
	public function url_management_page_html() {
		require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/views/url-management-page.php';
	}

    /**
	 * Redirect Management Page HTML
	 *
	 * @since 1.1.0
	 *
	 * @access public
	 */
	public function redirect_management_page_html() {
		require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/views/redirect-management-page.php';
	}
}
