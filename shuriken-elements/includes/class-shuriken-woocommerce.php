<?php
namespace ShurikenElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce Integration Class
 *
 * Handles WooCommerce specific hooks, specifically AJAX fragments for the mobile cart.
 *
 * @since 1.0.0
 */
class Class_Shuriken_WooCommerce {

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
		// Hook into WooCommerce fragments to update our custom cart badge and sidebars
		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart_fragments' ] );

        // Custom quantity buttons in mini-cart
        add_filter( 'woocommerce_widget_cart_item_quantity', [ $this, 'shuriken_cart_item_quantity' ], 99, 3 );

        // AJAX handlers
        add_action( 'wp_ajax_shuriken_update_cart_item_qty', [ $this, 'ajax_update_cart_item_qty' ] );
		add_action( 'wp_ajax_nopriv_shuriken_update_cart_item_qty', [ $this, 'ajax_update_cart_item_qty' ] );
	}

    /**
     * Inject custom quantity buttons into the mini-cart items.
     */
    public function shuriken_cart_item_quantity( $html, $cart_item, $cart_item_key ) {
        $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $cart_item['data'] ), $cart_item, $cart_item_key );
        
        ob_start();
        ?>
        <div class="shuriken-mbm-qty-container" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
            <button class="shuriken-mbm-qty-btn minus" aria-label="Decrease quantity">-</button>
            <span class="shuriken-mbm-qty-val"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
            <button class="shuriken-mbm-qty-btn plus" aria-label="Increase quantity">+</button>
            <span class="shuriken-mbm-qty-price">&times; <?php echo $product_price; ?></span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX handler for updating cart item quantity.
     */
    public function ajax_update_cart_item_qty() {
        // Security check
        check_ajax_referer( 'shuriken_mbm_nonce', 'security' );

        if ( ! isset( $_POST['cart_item_key'] ) || ! isset( $_POST['new_qty'] ) ) {
            wp_send_json_error( 'Missing data' );
        }

        $cart_item_key = sanitize_text_field( $_POST['cart_item_key'] );
        $new_qty = intval( $_POST['new_qty'] );

        if ( $new_qty < 0 ) {
            $new_qty = 0;
        }

        // Update the cart
        $cart = WC()->cart;
        $result = $cart->set_quantity( $cart_item_key, $new_qty );

        if ( false === $result ) {
            wp_send_json_error( 'Failed to update quantity' );
        }

        // Calculate totals so fragments get the correct subtotal
        $cart->calculate_totals();

        // Let WooCommerce handle the standard fragments JSON payload and exit
        \WC_AJAX::get_refreshed_fragments();
        wp_die();
    }

	/**
	 * Cart Fragments
	 *
	 * Ensure our custom cart badge updates via AJAX when a product is added to the cart.
	 *
	 * @param array $fragments
	 * @return array
	 */
	public function cart_fragments( $fragments ) {
		// Update the badge count
		ob_start();
		$cart_count = WC()->cart->get_cart_contents_count();
		?>
		<span class="shuriken-mbm-cart-badge"><?php echo esc_html( $cart_count ); ?></span>
		<?php
		$fragments['span.shuriken-mbm-cart-badge'] = ob_get_clean();

		// Update the sidebar/drawer cart content container
		ob_start();
		$this->render_cart_content();
		$fragments['div.shuriken-mbm-cart-content'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Render the standard WooCommerce Cart content for the sidebar/drawer.
	 * 
	 * We use the native woocommerce_mini_cart() which works perfectly for sidebars.
	 */
	public function render_cart_content() {
		?>
		<div class="shuriken-mbm-cart-content widget_shopping_cart_content">
			<?php woocommerce_mini_cart(); ?>
		</div>
		<?php
	}
}
