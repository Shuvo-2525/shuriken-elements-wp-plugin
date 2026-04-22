<?php
namespace ShurikenElements\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkout Field Editor Admin Class
 */
class Class_Checkout_Field_Editor {

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
        add_action( 'wp_ajax_shuriken_save_checkout_fields', [ $this, 'ajax_save_fields' ] );
        add_action( 'wp_ajax_shuriken_reset_checkout_fields', [ $this, 'ajax_reset_fields' ] );
	}

	public function add_submenu() {
		add_submenu_page(
			'shuriken-elements',
			esc_html__( 'Checkout Fields', 'shuriken-elements' ),
			esc_html__( 'Checkout Fields', 'shuriken-elements' ),
			'manage_options',
			'shuriken-checkout-editor',
			[ $this, 'render_page' ]
		);
	}

	public function enqueue_scripts( $hook ) {
		if ( 'shuriken-elements_page_shuriken-checkout-editor' !== $hook ) {
			return;
		}

        wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'shuriken-admin-checkout-editor', SHURIKEN_ELEMENTS_URL . 'assets/css/admin-checkout-editor.css', [], '1.0.0' );
		wp_enqueue_script( 'shuriken-admin-checkout-editor', SHURIKEN_ELEMENTS_URL . 'assets/js/admin-checkout-editor.js', [ 'jquery', 'jquery-ui-sortable' ], '1.0.0', true );

        wp_localize_script( 'shuriken-admin-checkout-editor', 'shuriken_checkout_obj', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'shuriken_checkout_nonce' ),
        ] );
	}

	public function render_page() {
		require_once SHURIKEN_ELEMENTS_PATH . 'includes/admin/views/checkout-editor-page.php';
	}

    /**
     * Get fields for a section
     */
    public function get_fields( $section ) {
        $saved = get_option( 'shuriken_wc_fields_' . $section );
        if ( is_array( $saved ) && ! empty( $saved ) ) {
            // Sort by priority
            uasort( $saved, function($a, $b) {
                $p1 = isset($a['priority']) ? (int) $a['priority'] : 10;
                $p2 = isset($b['priority']) ? (int) $b['priority'] : 10;
                return $p1 - $p2;
            });
            return $saved;
        }

        // Return WC Defaults if nothing saved
        return $this->get_default_fields( $section );
    }

    /**
     * Get default WooCommerce fields for a section
     */
    private function get_default_fields( $section ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return [];
        }

        $fields = [];
        if ( 'billing' === $section ) {
            $fields = WC()->countries->get_address_fields( WC()->countries->get_base_country(), 'billing_' );
            // Add email and phone which are not in get_address_fields
            $fields['billing_email'] = [
                'type' => 'email',
                'label' => __('Email address', 'woocommerce'),
                'class' => ['form-row-wide'],
                'required' => true,
                'priority' => 110,
            ];
            $fields['billing_phone'] = [
                'type' => 'tel',
                'label' => __('Phone', 'woocommerce'),
                'class' => ['form-row-wide'],
                'required' => true,
                'priority' => 100,
            ];
        } elseif ( 'shipping' === $section ) {
            $fields = WC()->countries->get_address_fields( WC()->countries->get_base_country(), 'shipping_' );
        } elseif ( 'additional' === $section ) {
            $fields['order_comments'] = [
                'type' => 'textarea',
                'label' => __('Order notes', 'woocommerce'),
                'class' => ['form-row-wide'],
                'placeholder' => __('Notes about your order, e.g. special notes for delivery.', 'woocommerce'),
                'required' => false,
                'priority' => 10,
            ];
        }

        // Format for our UI
        $formatted = [];
        foreach ( $fields as $key => $field ) {
            $formatted[$key] = [
                'type' => isset($field['type']) ? $field['type'] : 'text',
                'label' => isset($field['label']) ? $field['label'] : '',
                'placeholder' => isset($field['placeholder']) ? $field['placeholder'] : '',
                'required' => isset($field['required']) ? $field['required'] : false,
                'class' => isset($field['class']) ? $field['class'] : ['form-row-wide'],
                'priority' => isset($field['priority']) ? $field['priority'] : 10,
                'default' => isset($field['default']) ? $field['default'] : '',
                'validate' => isset($field['validate']) ? $field['validate'] : [],
                'show_in_email' => true, // Defaults to true
                'show_in_order' => true,
                'enabled' => true,
                'custom' => false
            ];
        }

        // Sort by priority
        uasort( $formatted, function($a, $b) {
            $p1 = isset($a['priority']) ? (int) $a['priority'] : 10;
            $p2 = isset($b['priority']) ? (int) $b['priority'] : 10;
            return $p1 - $p2;
        });

        return $formatted;
    }

    /**
     * AJAX Save Fields
     */
    public function ajax_save_fields() {
        check_ajax_referer( 'shuriken_checkout_nonce', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        $fields_data = isset( $_POST['fields'] ) ? json_decode( stripslashes( $_POST['fields'] ), true ) : [];

        if ( empty( $fields_data ) ) {
            wp_send_json_error( 'No data provided' );
        }

        // Process sections
        $sections = ['billing', 'shipping', 'additional'];
        foreach ( $sections as $section ) {
            if ( isset( $fields_data[$section] ) && is_array( $fields_data[$section] ) ) {
                
                $formatted_section = [];
                foreach ( $fields_data[$section] as $field ) {
                    $name = sanitize_key( $field['name'] );
                    if ( empty( $name ) ) continue;

                    $validate = [];
                    if ( isset( $field['validate'] ) && is_array( $field['validate'] ) ) {
                        $validate = array_map( 'sanitize_text_field', $field['validate'] );
                    }

                    $formatted_section[$name] = [
                        'type' => sanitize_text_field( $field['type'] ),
                        'label' => sanitize_text_field( $field['label'] ),
                        'placeholder' => sanitize_text_field( $field['placeholder'] ),
                        'required' => rest_sanitize_boolean( $field['required'] ),
                        'enabled' => rest_sanitize_boolean( $field['enabled'] ),
                        'custom' => rest_sanitize_boolean( $field['custom'] ),
                        'priority' => (int) $field['priority'],
                        'class' => [ sanitize_text_field( $field['class'] ) ],
                        'default' => sanitize_text_field( isset($field['default']) ? $field['default'] : '' ),
                        'validate' => $validate,
                        'show_in_email' => isset($field['show_in_email']) ? rest_sanitize_boolean( $field['show_in_email'] ) : true,
                        'show_in_order' => isset($field['show_in_order']) ? rest_sanitize_boolean( $field['show_in_order'] ) : true,
                    ];
                }

                update_option( 'shuriken_wc_fields_' . $section, $formatted_section );
            }
        }

        wp_send_json_success();
    }

    /**
     * AJAX Reset Fields
     */
    public function ajax_reset_fields() {
        check_ajax_referer( 'shuriken_checkout_nonce', 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied' );
        }

        delete_option( 'shuriken_wc_fields_billing' );
        delete_option( 'shuriken_wc_fields_shipping' );
        delete_option( 'shuriken_wc_fields_additional' );

        wp_send_json_success();
    }

}
