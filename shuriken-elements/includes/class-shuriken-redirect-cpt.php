<?php
namespace ShurikenElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Redirect CPT Class
 *
 * Registers the hidden Custom Post Type for Redirects.
 *
 * @since 1.1.0
 */
class Class_Shuriken_Redirect_CPT {

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
		add_action( 'init', [ $this, 'register_cpt' ] );
	}

	/**
	 * Register Custom Post Type
	 */
	public function register_cpt() {
		$args = array(
			'public'             => false,
			'show_ui'            => false, // Hidden from WP menu, we use custom UI
			'show_in_menu'       => false,
			'supports'           => array( 'title' ),
			'capability_type'    => 'post',
			'rewrite'            => false,
		);

		register_post_type( 'shuriken_redirect', $args );
	}
}
