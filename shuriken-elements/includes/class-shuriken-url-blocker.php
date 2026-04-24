<?php
namespace ShurikenElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * URL Blocker Class
 *
 * Handles frontend URL blocking based on settings.
 *
 * @since 1.1.0
 */
class Class_Shuriken_URL_Blocker {

	/**
	 * Instance
	 */
	private static $_instance = null;

	/**
	 * Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'template_redirect', [ $this, 'process_url_blocks' ] );
	}

	/**
	 * Process URL Blocks
	 */
	public function process_url_blocks() {
        // 1. Handle 404 Misspelled URLs
        if ( is_404() ) {
            $action_404 = get_option( 'shuriken_url_blocks_404_action', 'default' );
            if ( $action_404 === 'home' ) {
                wp_safe_redirect( home_url() );
                exit;
            } elseif ( $action_404 === 'custom' ) {
                $custom_url = get_option( 'shuriken_url_blocks_404_custom_url', '' );
                if ( ! empty( $custom_url ) ) {
                    wp_redirect( esc_url( $custom_url ) );
                    exit;
                }
            }
            return; // Allow default 404
        }

        // 2. Determine if the current page should be blocked
        $is_blocked = false;
        $redirect_dest = 'home'; // default

        // Check Automatic Blocking
        $auto_block = get_option( 'shuriken_url_blocks_auto', 'no' );
        if ( $auto_block === 'yes' && class_exists( 'WooCommerce' ) ) {
            if ( is_cart() || is_checkout() ) {
                // Do not block if we are actually processing an AJAX request or order pay page
                if ( ! wp_doing_ajax() && ! is_wc_endpoint_url( 'order-pay' ) && ! is_wc_endpoint_url( 'order-received' ) ) {
                    $is_blocked = true;
                }
            }
        }

        // Check Manual Blocking
        $custom_url = '';
        $is_valid_page = is_singular();
        $current_id = get_queried_object_id();

        if ( class_exists( 'WooCommerce' ) && is_shop() ) {
            $is_valid_page = true;
            $current_id = wc_get_page_id( 'shop' );
        }

        if ( ! $is_blocked && $is_valid_page ) {
            $manual_blocks = get_option( 'shuriken_url_blocks_manual', [] );
            
            if ( is_array( $manual_blocks ) && isset( $manual_blocks[ $current_id ] ) ) {
                $is_blocked = true;
                $redirect_dest = $manual_blocks[ $current_id ]['action'];
                $custom_url = isset( $manual_blocks[ $current_id ]['url'] ) ? $manual_blocks[ $current_id ]['url'] : '';
            }
        }

        // 3. Execute Block Action
        if ( $is_blocked ) {
            if ( $redirect_dest === 'home' ) {
                wp_safe_redirect( home_url() );
                exit;
            } elseif ( $redirect_dest === '404' ) {
                global $wp_query;
                $wp_query->set_404();
                status_header( 404 );
                nocache_headers();
                // Let WordPress load the 404 template naturally
            } elseif ( $redirect_dest === 'custom' && ! empty( $custom_url ) ) {
                wp_redirect( esc_url( $custom_url ) );
                exit;
            }
        }
	}
}
