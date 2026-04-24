<?php
namespace ShurikenElements\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin Post States Class
 *
 * Adds visual indicators to the WP Admin Pages/Posts list for blocked URLs.
 *
 * @since 1.1.0
 */
class Class_Admin_Post_States {

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
		add_filter( 'display_post_states', [ $this, 'add_blocked_post_states' ], 10, 2 );
	}

	/**
	 * Add Blocked Post States
     * 
     * @param array $post_states An array of post display states.
     * @param \WP_Post $post The current post object.
     * @return array
	 */
	public function add_blocked_post_states( $post_states, $post ) {
        
        // 1. Check Automatic Blocks (WooCommerce Cart/Checkout)
        $auto_block = get_option( 'shuriken_url_blocks_auto', 'no' );
        if ( $auto_block === 'yes' && class_exists( 'WooCommerce' ) ) {
            $wc_cart_id = wc_get_page_id( 'cart' );
            $wc_checkout_id = wc_get_page_id( 'checkout' );
            
            if ( $post->ID == $wc_cart_id || $post->ID == $wc_checkout_id ) {
                $post_states['shuriken_blocked_auto'] = esc_html__( '&mdash; Blocked by Shuriken (Automatic)', 'shuriken-elements' );
                return $post_states;
            }
        }

        // 2. Check Manual Blocks
        $manual_blocks = get_option( 'shuriken_url_blocks_manual', [] );
        if ( is_array( $manual_blocks ) && isset( $manual_blocks[ $post->ID ] ) ) {
            $post_states['shuriken_blocked_manual'] = esc_html__( '&mdash; Blocked by Shuriken (Manual)', 'shuriken-elements' );
        }

		return $post_states;
	}
}
