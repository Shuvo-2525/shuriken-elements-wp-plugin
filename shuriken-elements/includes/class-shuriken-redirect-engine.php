<?php
namespace ShurikenElements;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Redirect Engine Class
 *
 * Handles the actual redirection on the frontend.
 *
 * @since 1.1.0
 */
class Class_Shuriken_Redirect_Engine {

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
		add_action( 'template_redirect', [ $this, 'handle_redirect' ], 1 );
	}

	/**
	 * Handle Redirect
	 */
	public function handle_redirect() {
		if ( is_admin() ) {
            return;
        }

		global $wp;
		$request_slug = trim( $wp->request, '/' );

		if ( empty( $request_slug ) ) {
            return;
        }

		// Search for a 'shuriken_redirect' post with this slug
		$args = array(
			'name'           => $request_slug,
			'post_type'      => 'shuriken_redirect',
			'post_status'    => 'publish',
			'posts_per_page' => 1
		);

		$redirect_query = new WP_Query( $args );

		if ( $redirect_query->have_posts() ) {
			$redirect_query->the_post();
			$post_id = get_the_ID();
			
			// Get Target
			$target_url = get_post_meta( $post_id, '_shuriken_target_url', true );

			if ( $target_url ) {
				// 1. Increment Counter
				$current_clicks = (int) get_post_meta( $post_id, '_shuriken_clicks', true );
				update_post_meta( $post_id, '_shuriken_clicks', $current_clicks + 1 );

				// 2. Perform Redirect
				wp_redirect( $target_url, 301 );
				exit;
			}
		}
		wp_reset_postdata();
	}
}
