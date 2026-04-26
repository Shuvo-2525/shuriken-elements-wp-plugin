<?php
/**
 * Plugin Name: Shuriken Elements
 * Description: An awesome Elementor Addon packed with robust widgets.
 * Plugin URI:  https://shurikenit.com
 * Author:      Mohammad Rafiq Shuvo
 * Author URI:  https://shurikenit.com
 * Version:     1.3.0
 * Text Domain: shuriken-elements
 *
 * Elementor tested up to: 3.20.0
 * Elementor Pro tested up to: 3.20.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'SHURIKEN_ELEMENTS_VERSION', '1.3.0' );
define( 'SHURIKEN_ELEMENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SHURIKEN_ELEMENTS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Elementor Shuriken Elements Plugin Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
final class Shuriken_Elements_Plugin {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '1.3.0';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.4';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Shuriken_Elements_Plugin The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Shuriken_Elements_Plugin An instance of the class.
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
		add_action( 'plugins_loaded', [ $this, 'init' ] );
	}

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init() {
		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		// Once we get here, We have passed all validation checks so we can safely include our plugin required files.
		$this->includes();
	}

	/**
	 * Includes
	 *
	 * Loads core files of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function includes() {
		require_once SHURIKEN_ELEMENTS_PATH . 'includes/class-shuriken-elements.php';
		require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/class-admin-menu.php';
        require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/class-checkout-field-editor.php';
        require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/class-settings-handler.php';
        require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/class-admin-post-states.php';
        require_once SHURIKEN_ELEMENTS_PATH . 'includes/class-shuriken-wc-checkout-fields.php';
        require_once SHURIKEN_ELEMENTS_PATH . 'includes/class-shuriken-url-blocker.php';
        
        // New Redirect Management Files
        require_once SHURIKEN_ELEMENTS_PATH . 'includes/class-shuriken-redirect-cpt.php';
        require_once SHURIKEN_ELEMENTS_PATH . 'includes/class-shuriken-redirect-engine.php';
        require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/class-redirect-management-ajax.php';
        
        // Initialize core functionality
		\ShurikenElements\Class_Shuriken_Elements::instance();
        
        // Initialize Admin menu
        \ShurikenElements\Admin\Class_Admin_Menu::instance();

        // Initialize Settings Handler
        \ShurikenElements\Admin\Class_Settings_Handler::instance();

        // Initialize Admin Post States
        \ShurikenElements\Admin\Class_Admin_Post_States::instance();

        // Initialize Checkout Field Editor Admin
        \ShurikenElements\Admin\Class_Checkout_Field_Editor::instance();

        // Initialize WooCommerce Frontend Checkout Fields logic
        \ShurikenElements\Class_Shuriken_WC_Checkout_Fields::instance();

        // Initialize URL Blocker
        \ShurikenElements\Class_Shuriken_URL_Blocker::instance();

        // Initialize Redirect Management
        \ShurikenElements\Class_Shuriken_Redirect_CPT::instance();
        \ShurikenElements\Class_Shuriken_Redirect_Engine::instance();
        \ShurikenElements\Admin\Class_Redirect_Management_Ajax::instance();

        // Initialize Account Handler
        require_once SHURIKEN_ELEMENTS_PATH . 'includes/class-shuriken-account-handler.php';
        \ShurikenElements\Class_Shuriken_Account_Handler::instance();
	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'shuriken-elements' ),
			'<strong>' . esc_html__( 'Shuriken Elements', 'shuriken-elements' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'shuriken-elements' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'shuriken-elements' ),
			'<strong>' . esc_html__( 'Shuriken Elements', 'shuriken-elements' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'shuriken-elements' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'shuriken-elements' ),
			'<strong>' . esc_html__( 'Shuriken Elements', 'shuriken-elements' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'shuriken-elements' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}
}

// Instantiate Shuriken_Elements_Plugin.
Shuriken_Elements_Plugin::instance();
