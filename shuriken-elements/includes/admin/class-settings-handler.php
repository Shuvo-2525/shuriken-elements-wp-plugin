<?php
namespace ShurikenElements\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Settings Handler Class
 *
 * Handles the registration and saving of Shuriken Elements settings.
 *
 * @since 1.1.0
 */
class Class_Settings_Handler {

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
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Register Settings
	 */
	public function register_settings() {
		// URL Blocking settings group
		$group = 'shuriken_elements_url_blocking_settings';

		// Automatic Blocking
		register_setting( $group, 'shuriken_url_blocks_auto', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'no'
		] );

		// Manual Blocking (Associative array of page/post IDs to actions)
		register_setting( $group, 'shuriken_url_blocks_manual', [
			'sanitize_callback' => [ $this, 'sanitize_manual_blocks' ],
			'default'           => []
		] );

        // 404 Action
		register_setting( $group, 'shuriken_url_blocks_404_action', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'default'
		] );

        // 404 Custom URL
		register_setting( $group, 'shuriken_url_blocks_404_custom_url', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_url',
			'default'           => ''
		] );
	}

    /**
     * Sanitize manual block rules
     */
    public function sanitize_manual_blocks( $input ) {
        if ( ! is_array( $input ) ) {
            return [];
        }

        $sanitized = [];
        foreach ( $input as $post_id => $data ) {
            $id = intval( $post_id );
            if ( $id <= 0 || ! is_array( $data ) ) {
                continue;
            }
            
            $action = isset( $data['action'] ) ? sanitize_text_field( $data['action'] ) : 'none';
            $url    = isset( $data['url'] ) ? sanitize_url( $data['url'] ) : '';

            // Only save if an action is actually set
            if ( in_array( $action, ['home', '404', 'custom'] ) ) {
                $sanitized[ $id ] = [
                    'action' => $action,
                    'url'    => $url,
                ];
            }
        }
        return $sanitized;
    }
}
