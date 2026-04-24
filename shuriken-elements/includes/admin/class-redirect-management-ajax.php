<?php
namespace ShurikenElements\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Redirect Management AJAX Class
 *
 * Handles AJAX requests for the Redirect Management UI.
 *
 * @since 1.1.0
 */
class Class_Redirect_Management_Ajax {

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
		add_action( 'wp_ajax_shuriken_get_redirects', [ $this, 'get_redirects' ] );
		add_action( 'wp_ajax_shuriken_add_redirect', [ $this, 'add_redirect' ] );
		add_action( 'wp_ajax_shuriken_delete_redirect', [ $this, 'delete_redirect' ] );
	}

	/**
	 * Get All Redirects
	 */
	public function get_redirects() {
		check_ajax_referer( 'shuriken_redirect_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'shuriken-elements' ) ] );
		}

		$args = array(
			'post_type'      => 'shuriken_redirect',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC'
		);

		$query = new \WP_Query( $args );
		$redirects = [];

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$redirects[] = [
					'id'     => $post->ID,
					'slug'   => $post->post_name,
					'target' => get_post_meta( $post->ID, '_shuriken_target_url', true ),
					'clicks' => (int) get_post_meta( $post->ID, '_shuriken_clicks', true ),
				];
			}
		}

		wp_send_json_success( [ 'redirects' => $redirects ] );
	}

	/**
	 * Add New Redirect
	 */
	public function add_redirect() {
		check_ajax_referer( 'shuriken_redirect_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'shuriken-elements' ) ] );
		}

		$slug   = isset( $_POST['slug'] ) ? sanitize_title( $_POST['slug'] ) : '';
		$target = isset( $_POST['target'] ) ? esc_url_raw( $_POST['target'] ) : '';

		if ( empty( $slug ) || empty( $target ) ) {
			wp_send_json_error( [ 'message' => __( 'Slug and Target URL are required.', 'shuriken-elements' ) ] );
		}

		// Check if slug already exists
		$existing = get_page_by_path( $slug, OBJECT, [ 'post', 'page', 'shuriken_redirect' ] );
		if ( $existing ) {
			wp_send_json_error( [ 'message' => __( 'This slug is already in use.', 'shuriken-elements' ) ] );
		}

		$post_data = array(
			'post_title'  => $slug,
			'post_name'   => $slug,
			'post_type'   => 'shuriken_redirect',
			'post_status' => 'publish',
		);

		$post_id = wp_insert_post( $post_data, true );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( [ 'message' => $post_id->get_error_message() ] );
		}

		update_post_meta( $post_id, '_shuriken_target_url', $target );
		update_post_meta( $post_id, '_shuriken_clicks', 0 );

		wp_send_json_success( [
			'message' => __( 'Redirect added successfully.', 'shuriken-elements' ),
			'redirect' => [
				'id'     => $post_id,
				'slug'   => $slug,
				'target' => $target,
				'clicks' => 0
			]
		] );
	}

	/**
	 * Delete Redirect
	 */
	public function delete_redirect() {
		check_ajax_referer( 'shuriken_redirect_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'shuriken-elements' ) ] );
		}

		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

		if ( $id <= 0 ) {
			wp_send_json_error( [ 'message' => __( 'Invalid ID.', 'shuriken-elements' ) ] );
		}

		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'shuriken_redirect' ) {
			wp_send_json_error( [ 'message' => __( 'Redirect not found.', 'shuriken-elements' ) ] );
		}

		wp_delete_post( $id, true );

		wp_send_json_success( [ 'message' => __( 'Redirect deleted successfully.', 'shuriken-elements' ) ] );
	}
}
