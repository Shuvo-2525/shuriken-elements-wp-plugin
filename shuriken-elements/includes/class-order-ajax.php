<?php
namespace ShurikenElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX Order Received & Auth Flow
 */
class Class_Order_Ajax {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		// Filter WooCommerce checkout redirect URL
		add_filter( 'woocommerce_payment_successful_result', [ $this, 'modify_checkout_redirect' ], 10, 2 );

        // AJAX handler to fetch order received content
        add_action( 'wp_ajax_shuriken_get_order_received', [ $this, 'ajax_get_order_received' ] );
        add_action( 'wp_ajax_nopriv_shuriken_get_order_received', [ $this, 'ajax_get_order_received' ] );

        // AJAX handler for lost password form
        add_action( 'wp_ajax_shuriken_get_lost_password', [ $this, 'ajax_get_lost_password' ] );
        add_action( 'wp_ajax_nopriv_shuriken_get_lost_password', [ $this, 'ajax_get_lost_password' ] );

        // AJAX handler for track order
        add_action( 'wp_ajax_shuriken_track_order', [ $this, 'ajax_track_order' ] );
        add_action( 'wp_ajax_nopriv_shuriken_track_order', [ $this, 'ajax_track_order' ] );
	}

    /**
     * Modify the redirect URL after successful checkout
     * We change it to a hash so the page doesn't reload, and our JS can intercept it.
     */
	public function modify_checkout_redirect( $result, $order_id ) {
        $flow_settings = get_option( 'shuriken_flow_management_settings', [] );
        $disable_redirect = isset($flow_settings['disable_order_redirect']) ? $flow_settings['disable_order_redirect'] : false;

        if ( $disable_redirect && isset( $result['redirect'] ) && $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $order_key = $order->get_order_key();
                // Instead of a full URL, we return a hash with order details
                $result['redirect'] = '#shuriken-order-received|' . $order_id . '|' . $order_key;
            }
        }
        return $result;
	}

    /**
     * AJAX Get Order Received Fragment
     */
    public function ajax_get_order_received() {
        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        $order_key = isset( $_POST['order_key'] ) ? sanitize_text_field( $_POST['order_key'] ) : '';

        if ( ! $order_id || ! $order_key ) {
            wp_send_json_error( 'Invalid request' );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || $order->get_order_key() !== $order_key ) {
            wp_send_json_error( 'Invalid order' );
        }

        ob_start();
        
        // Output WooCommerce Order Received shortcode or template
        echo '<div class="woocommerce">';
        echo '<div class="shuriken-order-received-wrapper" style="padding: 20px;">';
        
        // Print success message
        echo '<div class="woocommerce-order">';
        echo '<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received" style="background:#e8f5e9; color:#2e7d32; padding:15px; border-radius:8px; border-left:4px solid #4caf50;">';
        echo esc_html__( 'Thank you. Your order has been received.', 'woocommerce' );
        echo '</p>';

        // Render the order details
        do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order_id );
        do_action( 'woocommerce_thankyou', $order_id );

        echo '</div>'; // .woocommerce-order
        echo '</div>'; // .shuriken-order-received-wrapper
        echo '</div>'; // .woocommerce

        $html = ob_get_clean();

        wp_send_json_success( [ 'html' => $html ] );
    }

    /**
     * AJAX Get Lost Password Fragment
     */
    public function ajax_get_lost_password() {
        ob_start();
        echo '<div class="woocommerce">';
        echo '<div class="shuriken-lost-password-wrapper" style="padding: 20px;">';
        wc_get_template( 'myaccount/form-lost-password.php' );
        echo '</div>';
        echo '</div>';
        $html = ob_get_clean();

        wp_send_json_success( [ 'html' => $html ] );
    }

    /**
     * AJAX Track Order
     */
    public function ajax_track_order() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            wp_send_json_error( esc_html__( 'WooCommerce is not active.', 'shuriken-elements' ) );
        }

        $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
        $order_email = isset( $_POST['order_email'] ) ? sanitize_email( $_POST['order_email'] ) : '';

        if ( ! $order_id || ! $order_email ) {
            wp_send_json_error( esc_html__( 'Please enter a valid order ID and email.', 'shuriken-elements' ) );
        }

        $order = wc_get_order( $order_id );

        if ( ! $order || $order->get_billing_email() !== $order_email ) {
            wp_send_json_error( esc_html__( 'Sorry, the order could not be found. Please contact us if you are having difficulty finding your order details.', 'shuriken-elements' ) );
        }

        ob_start();
        
        echo '<div class="woocommerce">';
        echo '<div class="shuriken-track-order-details" style="padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">';
        
        // Show Order Status
        $status = wc_get_order_status_name( $order->get_status() );
        $status_color = '#0073aa'; // Default blue
        
        if ( $order->has_status( 'completed' ) ) {
            $status_color = '#4caf50'; // Green
        } elseif ( $order->has_status( 'processing' ) ) {
            $status_color = '#ff9800'; // Orange
        } elseif ( $order->has_status( 'cancelled' ) || $order->has_status( 'failed' ) ) {
            $status_color = '#f44336'; // Red
        }

        echo '<div style="margin-bottom: 20px; text-align: center;">';
        echo '<h4 style="margin: 0; font-size: 18px; color: #333;">Order #' . esc_html( $order->get_order_number() ) . '</h4>';
        echo '<span style="display: inline-block; margin-top: 10px; padding: 5px 15px; border-radius: 20px; background: ' . esc_attr( $status_color ) . '; color: #fff; font-weight: 600; font-size: 14px;">' . esc_html( $status ) . '</span>';
        echo '</div>';

        // Render standard WooCommerce order details template
        wc_get_template( 'order/order-details.php', array( 'order_id' => $order_id ) );
        
        echo '</div>';
        echo '</div>';

        $html = ob_get_clean();

        wp_send_json_success( [ 'html' => $html ] );
    }
}
