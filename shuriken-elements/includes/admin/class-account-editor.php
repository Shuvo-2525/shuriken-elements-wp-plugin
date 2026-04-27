<?php
namespace ShurikenElements\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account Editor Admin Class
 */
class Class_Account_Editor {

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
        add_action( 'wp_ajax_shuriken_save_account_fields', [ $this, 'ajax_save_fields' ] );
        add_action( 'wp_ajax_shuriken_reset_account_fields', [ $this, 'ajax_reset_fields' ] );
	}

	public function add_submenu() {
		add_submenu_page(
			'shuriken-elements',
			esc_html__( 'Account Management', 'shuriken-elements' ),
			esc_html__( 'Account Management', 'shuriken-elements' ),
			'manage_options',
			'shuriken-account-editor',
			[ $this, 'render_page' ]
		);
	}

	public function enqueue_scripts( $hook ) {
		if ( 'shuriken-elements_page_shuriken-account-editor' !== $hook ) {
			return;
		}

        wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'shuriken-admin-checkout-editor', SHURIKEN_ELEMENTS_URL . 'assets/css/admin-checkout-editor.css', [], '1.0.0' );
		
        // We will reuse the same checkout editor JS for now, or you can duplicate it if you need specific logic.
        // Actually, we'll create a generic admin-editor.js or just write a small script inside the view if it differs.
        // Let's use the same one, as it handles the UI drag and drop.
		// wp_enqueue_script( 'shuriken-admin-checkout-editor', SHURIKEN_ELEMENTS_URL . 'assets/js/admin-checkout-editor.js', [ 'jquery', 'jquery-ui-sortable' ], '1.0.0', true );
        // We will create a specific JS for account editor if needed, or enqueue it inside the view.
	}

	public function render_page() {
		require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/views/account-editor-page.php';
	}

    public function ajax_save_fields() {
        check_ajax_referer( 'shuriken_account_nonce', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $fields_data = isset( $_POST['fields'] ) ? json_decode( stripslashes( $_POST['fields'] ), true ) : [];
        update_option( 'shuriken_account_management_fields', $fields_data );

        wp_send_json_success();
    }

    public function ajax_reset_fields() {
        check_ajax_referer( 'shuriken_account_nonce', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        delete_option( 'shuriken_account_management_fields' );
        wp_send_json_success();
    }
}
