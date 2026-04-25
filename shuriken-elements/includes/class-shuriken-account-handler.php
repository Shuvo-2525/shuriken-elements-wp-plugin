<?php
namespace ShurikenElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Account Handler Class
 *
 * Handles AJAX login, registration, and account content for the mobile menu.
 *
 * @since 1.2.0
 */
class Class_Shuriken_Account_Handler {

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
		// AJAX handlers for getting content
		add_action( 'wp_ajax_shuriken_get_account_content', [ $this, 'ajax_get_account_content' ] );
		add_action( 'wp_ajax_nopriv_shuriken_get_account_content', [ $this, 'ajax_get_account_content' ] );

		// AJAX handlers for login/registration
		add_action( 'wp_ajax_shuriken_ajax_login', [ $this, 'ajax_login' ] );
		add_action( 'wp_ajax_nopriv_shuriken_ajax_login', [ $this, 'ajax_login' ] );
		
		add_action( 'wp_ajax_shuriken_ajax_register', [ $this, 'ajax_register' ] );
		add_action( 'wp_ajax_nopriv_shuriken_ajax_register', [ $this, 'ajax_register' ] );
	}

	/**
	 * AJAX: Get account content (Dashboard or Login/Register forms)
	 */
	public function ajax_get_account_content() {
		check_ajax_referer( 'shuriken_mbm_nonce', 'security' );

		ob_start();
		
		if ( is_user_logged_in() ) {
			$this->render_account_dashboard();
		} else {
			$this->render_login_registration_forms();
		}

		$html = ob_get_clean();
		wp_send_json_success( $html );
	}

	/**
	 * Render Account Dashboard
	 */
	private function render_account_dashboard() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<p>' . esc_html__( 'WooCommerce is not active.', 'shuriken-elements' ) . '</p>';
			return;
		}

		$current_user = wp_get_current_user();
		?>
		<div class="shuriken-account-dashboard">
			<div class="shuriken-account-welcome">
				<div class="shuriken-account-avatar">
					<?php echo get_avatar( $current_user->ID, 60 ); ?>
				</div>
				<div class="shuriken-account-info">
					<h4><?php printf( esc_html__( 'Hello, %s', 'shuriken-elements' ), esc_html( $current_user->display_name ) ); ?></h4>
					<p><?php echo esc_html( $current_user->user_email ); ?></p>
				</div>
			</div>

			<ul class="shuriken-account-nav">
				<?php
				$items = wc_get_account_menu_items();
				foreach ( $items as $endpoint => $label ) : ?>
					<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
						<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
					</li>
				<?php endforeach; ?>
                <li class="woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--customer-logout">
                    <a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?>"><?php echo esc_html__( 'Logout', 'shuriken-elements' ); ?></a>
                </li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render Login/Registration Forms
	 */
	private function render_login_registration_forms() {
		?>
		<div class="shuriken-account-forms">
			<div class="shuriken-form-tabs">
				<button class="shuriken-form-tab active" data-target="shuriken-login-form"><?php esc_html_e( 'Login', 'shuriken-elements' ); ?></button>
				<?php if ( get_option( 'users_can_register' ) || ( class_exists( 'WooCommerce' ) && 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) ) : ?>
					<button class="shuriken-form-tab" data-target="shuriken-register-form"><?php esc_html_e( 'Register', 'shuriken-elements' ); ?></button>
				<?php endif; ?>
			</div>

			<div class="shuriken-form-container">
				<!-- Login Form -->
				<form id="shuriken-login-form" class="shuriken-ajax-form active" method="post">
					<div class="shuriken-form-row">
						<label for="shuriken_user_login"><?php esc_html_e( 'Username or email', 'shuriken-elements' ); ?> <span class="required">*</span></label>
						<input type="text" name="username" id="shuriken_user_login" required />
					</div>
					<div class="shuriken-form-row">
						<label for="shuriken_user_pass"><?php esc_html_e( 'Password', 'shuriken-elements' ); ?> <span class="required">*</span></label>
						<input type="password" name="password" id="shuriken_user_pass" required />
					</div>
					<div class="shuriken-form-row shuriken-form-row-flex">
						<label class="shuriken-rememberme">
							<input type="checkbox" name="rememberme" value="forever" /> <?php esc_html_e( 'Remember me', 'shuriken-elements' ); ?>
						</label>
						<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="shuriken-lost-pass"><?php esc_html_e( 'Lost password?', 'shuriken-elements' ); ?></a>
					</div>
					<div class="shuriken-form-row">
						<?php wp_nonce_field( 'shuriken-login', 'shuriken-login-nonce' ); ?>
						<button type="submit" class="shuriken-button"><?php esc_html_e( 'Log in', 'shuriken-elements' ); ?></button>
					</div>
					<div class="shuriken-form-message"></div>
				</form>

				<!-- Register Form -->
				<?php if ( get_option( 'users_can_register' ) || ( class_exists( 'WooCommerce' ) && 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) ) : ?>
					<form id="shuriken-register-form" class="shuriken-ajax-form" method="post" style="display: none;">
						<div class="shuriken-form-row">
							<label for="shuriken_reg_email"><?php esc_html_e( 'Email address', 'shuriken-elements' ); ?> <span class="required">*</span></label>
							<input type="email" name="email" id="shuriken_reg_email" required />
						</div>
						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<div class="shuriken-form-row">
								<label for="shuriken_reg_username"><?php esc_html_e( 'Username', 'shuriken-elements' ); ?> <span class="required">*</span></label>
								<input type="text" name="username" id="shuriken_reg_username" required />
							</div>
						<?php endif; ?>
						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
							<div class="shuriken-form-row">
								<label for="shuriken_reg_password"><?php esc_html_e( 'Password', 'shuriken-elements' ); ?> <span class="required">*</span></label>
								<input type="password" name="password" id="shuriken_reg_password" required />
							</div>
						<?php endif; ?>
						<div class="shuriken-form-row">
							<?php wp_nonce_field( 'shuriken-register', 'shuriken-register-nonce' ); ?>
							<button type="submit" class="shuriken-button"><?php esc_html_e( 'Register', 'shuriken-elements' ); ?></button>
						</div>
						<div class="shuriken-form-message"></div>
					</form>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX: Login handler
	 */
	public function ajax_login() {
		check_ajax_referer( 'shuriken-login', 'security' );

		$info = array();
		$info['user_login']    = sanitize_user( $_POST['username'] );
		$info['user_password'] = $_POST['password'];
		$info['remember']      = isset( $_POST['rememberme'] ) ? true : false;

		$user_signon = wp_signon( $info, is_ssl() );

		if ( is_wp_error( $user_signon ) ) {
			wp_send_json_error( $user_signon->get_error_message() );
		} else {
			wp_send_json_success( esc_html__( 'Login successful! Redirecting...', 'shuriken-elements' ) );
		}
	}

	/**
	 * AJAX: Register handler
	 */
	public function ajax_register() {
		check_ajax_referer( 'shuriken-register', 'security' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			wp_send_json_error( esc_html__( 'WooCommerce is required for registration.', 'shuriken-elements' ) );
		}

		$email    = sanitize_email( $_POST['email'] );
		$username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
		$password = isset( $_POST['password'] ) ? $_POST['password'] : '';

		try {
			$customer_id = wc_create_new_customer( $email, $username, $password );

			if ( is_wp_error( $customer_id ) ) {
				throw new \Exception( $customer_id->get_error_message() );
			}

			// Automatically log in the new user
			wp_set_auth_cookie( $customer_id, true );
			
			wp_send_json_success( esc_html__( 'Registration successful! Redirecting...', 'shuriken-elements' ) );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}
}
