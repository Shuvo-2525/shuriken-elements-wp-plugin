<?php
namespace ShurikenElements\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flow Management Admin Class
 */
class Class_Flow_Management {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_submenu' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // AJAX handlers for saving/resetting
        add_action( 'wp_ajax_shuriken_save_flow_settings', [ $this, 'ajax_save_settings' ] );
	}

	public function add_submenu() {
		add_submenu_page(
			'shuriken-elements',
			esc_html__( 'Flow Management', 'shuriken-elements' ),
			esc_html__( 'Flow Management', 'shuriken-elements' ),
			'manage_options',
			'shuriken-flow-management',
			[ $this, 'render_page' ]
		);
	}

	public function enqueue_scripts( $hook ) {
		if ( 'shuriken-elements_page_shuriken-flow-management' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'shuriken-admin-checkout-editor', SHURIKEN_ELEMENTS_URL . 'assets/css/admin-checkout-editor.css', [], '1.0.0' );
	}

	public function render_page() {
		require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/views/flow-management-page.php';
	}

    public function ajax_save_settings() {
        check_ajax_referer( 'shuriken_flow_nonce', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $settings = isset( $_POST['settings'] ) ? json_decode( stripslashes( $_POST['settings'] ), true ) : [];
        update_option( 'shuriken_flow_management_settings', $settings );

        if ( isset( $settings['allow_guest_checkout'] ) ) {
            update_option( 'woocommerce_enable_guest_checkout', $settings['allow_guest_checkout'] ? 'yes' : 'no' );
        }

        wp_send_json_success();
    }
}
