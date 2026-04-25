<?php
namespace ShurikenElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Class
 *
 * Responsible for hooking into Elementor and registering categories/widgets.
 *
 * @since 1.0.0
 */
class Class_Shuriken_Elements {

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Class_Shuriken_Elements The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Class_Shuriken_Elements An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {
        // Init WooCommerce hooks if WC exists
        if ( class_exists( 'WooCommerce' ) ) {
            require_once SHURIKEN_ELEMENTS_PATH . 'includes/class-shuriken-woocommerce.php';
            \ShurikenElements\Class_Shuriken_WooCommerce::instance();
        }

		// Register Scripts and Styles
		add_action( 'elementor/frontend/after_register_styles', [ $this, 'widget_styles' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'widget_scripts' ] );

		// Register Category
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_elementor_widget_categories' ] );

		// Register widgets
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );

        // Register AJAX Global Search
        add_action( 'wp_ajax_shuriken_ajax_search', [ $this, 'ajax_search' ] );
		add_action( 'wp_ajax_nopriv_shuriken_ajax_search', [ $this, 'ajax_search' ] );
	}

    /**
     * AJAX Search Handler
     */
    public function ajax_search() {
        check_ajax_referer( 'shuriken_mbm_nonce', 'security' );

        $query_str = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
        $query_type = isset($_POST['query_type']) ? sanitize_text_field($_POST['query_type']) : 'all';

        if ( empty( $query_str ) ) {
            wp_send_json_success( [] );
        }

        $args = [
            's' => $query_str,
            'posts_per_page' => 10,
            'post_status' => 'publish',
        ];

        if ( $query_type === 'product' && class_exists( 'WooCommerce' ) ) {
            $args['post_type'] = 'product';
        } else {
            $args['post_type'] = ['post', 'page']; // General search fallback
        }

        $query = new \WP_Query( $args );
        $results = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                
                $price_html = '';
                if ( $query_type === 'product' && class_exists( 'WooCommerce' ) ) {
                    $product = wc_get_product( get_the_ID() );
                    if ( $product ) {
                        $price_html = $product->get_price_html();
                    }
                }

                $results[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'thumbnail' => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ),
                    'price_html' => $price_html
                ];
            }
        }
        wp_reset_postdata();

        wp_send_json_success( $results );
    }

	/**
	 * Register Widget Styles
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function widget_styles() {
		wp_register_style( 'shuriken-mobile-bottom-menu', SHURIKEN_ELEMENTS_URL . 'assets/css/mobile-bottom-menu.css', [], '1.0.0' );
		wp_register_style( 'shuriken-popup-checkout', SHURIKEN_ELEMENTS_URL . 'assets/css/popup-checkout.css', [], '1.0.0' );
        wp_register_style( 'shuriken-floating-cart', SHURIKEN_ELEMENTS_URL . 'assets/css/floating-cart.css', ['shuriken-mobile-bottom-menu'], '1.0.0' );
	}

	/**
	 * Register Widget Scripts
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function widget_scripts() {
		wp_register_script( 'shuriken-mobile-bottom-menu', SHURIKEN_ELEMENTS_URL . 'assets/js/mobile-bottom-menu.js', [ 'jquery', 'elementor-frontend' ], '1.0.0', true );
		wp_register_script( 'shuriken-popup-checkout', SHURIKEN_ELEMENTS_URL . 'assets/js/popup-checkout.js', [ 'jquery', 'elementor-frontend' ], '1.0.0', true );

        wp_localize_script( 'shuriken-mobile-bottom-menu', 'shuriken_obj', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'shuriken_mbm_nonce' ),
        ] );
	}

	/**
	 * Add Elementor Widget Categories
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function add_elementor_widget_categories( $elements_manager ) {

		$elements_manager->add_category(
			'shuriken-blocks',
			[
				'title' => esc_html__( 'Shuriken Blocks', 'shuriken-elements' ),
				'icon' => 'fa fa-plug', // Generic Elementor Icon
			]
		);

	}

	/**
	 * Register Widgets
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets( $widgets_manager ) {

		// Base Path
		$widgets_path = SHURIKEN_ELEMENTS_PATH . 'includes/widgets/';

		// Mobile Bottom Menu
		require_once( $widgets_path . 'class-mobile-bottom-menu.php' );
		$widgets_manager->register( new \ShurikenElements\Widgets\Mobile_Bottom_Menu() );

		// Popup Checkout
		require_once( $widgets_path . 'class-popup-checkout.php' );
		$widgets_manager->register( new \ShurikenElements\Widgets\Popup_Checkout() );

        // Floating Cart
		require_once( $widgets_path . 'class-floating-cart.php' );
		$widgets_manager->register( new \ShurikenElements\Widgets\Floating_Cart() );

	}

}
