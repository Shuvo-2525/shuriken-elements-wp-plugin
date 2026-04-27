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

		add_action( 'wp_ajax_shuriken_ajax_logout', [ $this, 'ajax_logout' ] );

		// AJAX handler for WC endpoints
		add_action( 'wp_ajax_shuriken_get_wc_endpoint', [ $this, 'ajax_get_wc_endpoint_content' ] );
	}

	/**
	 * AJAX: Get account content (Dashboard or Login/Register forms)
	 */
	public function ajax_get_account_content() {
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
				$saved_endpoints = get_option( 'shuriken_account_management_fields', [] );
				if ( empty( $saved_endpoints ) ) {
					// Fallback to default
					$saved_endpoints = [
						'dashboard'       => [ 'label' => 'Dashboard', 'enabled' => true, 'icon' => 'dashicons-dashboard' ],
						'orders'          => [ 'label' => 'Orders', 'enabled' => true, 'icon' => 'dashicons-cart' ],
						'downloads'       => [ 'label' => 'Downloads', 'enabled' => true, 'icon' => 'dashicons-download' ],
						'edit-address'    => [ 'label' => 'Addresses', 'enabled' => true, 'icon' => 'dashicons-location-alt' ],
						'edit-account'    => [ 'label' => 'Account details', 'enabled' => true, 'icon' => 'dashicons-admin-users' ],
						'customer-logout' => [ 'label' => 'Logout', 'enabled' => true, 'icon' => 'dashicons-migrate' ],
					];
				}

				foreach ( $saved_endpoints as $endpoint => $data ) : 
					if ( ! isset( $data['enabled'] ) || ! $data['enabled'] ) {
						continue;
					}
					$label = isset( $data['label'] ) ? $data['label'] : $endpoint;
					$icon  = isset( $data['icon'] ) ? $data['icon'] : '';
					$url   = wc_get_account_endpoint_url( $endpoint );
					// Logout url needs special handling
					if ( $endpoint === 'customer-logout' ) {
						$url = wc_logout_url( wc_get_page_permalink( 'myaccount' ) );
					}
					?>
					<li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>" style="padding: 10px 20px; border-bottom: 1px solid #f0f0f0; display:flex; align-items:center; gap:10px;">
						<?php if ( $icon ) : ?>
							<span class="dashicons <?php echo esc_attr( $icon ); ?>" style="color:#888;"></span>
						<?php endif; ?>
						<a href="<?php echo esc_url( $url ); ?>" style="font-weight:500; color:#333; text-decoration:none; width:100%;"><?php echo esc_html( $label ); ?></a>
					</li>
				<?php endforeach; ?>
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
						<div class="shuriken-form-toggle" style="margin-top: 15px; text-align: center; font-size: 14px;">
							<?php esc_html_e( 'New user?', 'shuriken-elements' ); ?> <a href="#" class="shuriken-toggle-register" style="font-weight: 600; color: #0073aa; text-decoration: none;"><?php esc_html_e( 'Click here to sign up', 'shuriken-elements' ); ?></a>
						</div>
				</form>

				<!-- Register Form -->
					<form id="shuriken-register-form" class="shuriken-ajax-form" method="post" style="display: none;">
						<?php
						$signup_fields = get_option( 'shuriken_signup_fields', [] );
						if ( empty( $signup_fields ) ) {
							$signup_fields = [
								'billing_first_name' => ['type' => 'text', 'label' => 'First Name', 'enabled' => true, 'required' => true],
								'billing_last_name'  => ['type' => 'text', 'label' => 'Last Name', 'enabled' => true, 'required' => true],
							];
						}

						// We must have an email field regardless of custom fields, but we'll add it first if it's not in the custom fields list.
						// Actually, WooCommerce always requires email.
						?>
						<div class="shuriken-form-row">
							<label for="shuriken_reg_email"><?php esc_html_e( 'Email address', 'shuriken-elements' ); ?> <span class="required">*</span></label>
							<input type="email" name="email" id="shuriken_reg_email" required />
						</div>

						<?php foreach ( $signup_fields as $key => $field ) : 
							if ( ! isset( $field['enabled'] ) || ! $field['enabled'] ) continue;
							$type = isset( $field['type'] ) ? $field['type'] : 'text';
							$label = isset( $field['label'] ) ? $field['label'] : $key;
							$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
							$required = isset( $field['required'] ) && $field['required'] ? true : false;
						?>
						<div class="shuriken-form-row">
							<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?> <?php if ( $required ) echo '<span class="required">*</span>'; ?></label>
							<input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" <?php echo $required ? 'required' : ''; ?> />
						</div>
						<?php endforeach; ?>
						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<div class="shuriken-form-row">
								<label for="shuriken_reg_username"><?php esc_html_e( 'Username', 'shuriken-elements' ); ?> <span class="required">*</span></label>
								<input type="text" name="username" id="shuriken_reg_username" required />
							</div>
						<?php endif; ?>
						<div class="shuriken-form-row">
							<label for="shuriken_reg_password"><?php esc_html_e( 'Password', 'shuriken-elements' ); ?> <span class="required">*</span></label>
							<input type="password" name="password" id="shuriken_reg_password" required />
						</div>
						<div class="shuriken-form-row">
							<label for="shuriken_reg_password_confirm"><?php esc_html_e( 'Confirm Password', 'shuriken-elements' ); ?> <span class="required">*</span></label>
							<input type="password" name="password_confirm" id="shuriken_reg_password_confirm" required />
						</div>
						<div class="shuriken-form-row">
							<?php wp_nonce_field( 'shuriken-register', 'shuriken-register-nonce' ); ?>
							<button type="submit" class="shuriken-button"><?php esc_html_e( 'Register', 'shuriken-elements' ); ?></button>
						</div>
						<div class="shuriken-form-message"></div>
						<div class="shuriken-form-toggle" style="margin-top: 15px; text-align: center; font-size: 14px;">
							<?php esc_html_e( 'Already have an account?', 'shuriken-elements' ); ?> <a href="#" class="shuriken-toggle-login" style="font-weight: 600; color: #0073aa; text-decoration: none;"><?php esc_html_e( 'Log in', 'shuriken-elements' ); ?></a>
						</div>
					</form>
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
			wp_set_current_user( $user_signon->ID );
			$new_nonce = wp_create_nonce( 'shuriken_mbm_nonce' );
			wp_send_json_success( [
				'message'   => esc_html__( 'Login successful! Loading...', 'shuriken-elements' ),
				'new_nonce' => $new_nonce
			] );
		}
	}


	/**
	 * AJAX: Logout handler
	 */
	public function ajax_logout() {
		wp_logout();
		wp_send_json_success( esc_html__( 'Logged out successfully.', 'shuriken-elements' ) );
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

			// Ensure billing_email is set to mirror the account email
			update_user_meta( $customer_id, 'billing_email', $email );

			// Save custom signup fields
			$signup_fields = get_option( 'shuriken_signup_fields', [] );
			foreach ( $signup_fields as $key => $field ) {
				if ( isset( $field['enabled'] ) && $field['enabled'] && isset( $_POST[ $key ] ) ) {
					$value = sanitize_text_field( $_POST[ $key ] );
					update_user_meta( $customer_id, $key, $value );

					// Mirror names and phone to WooCommerce fields
					if ( in_array( $key, ['billing_first_name', 'first_name'], true ) ) {
						update_user_meta( $customer_id, 'first_name', $value );
						update_user_meta( $customer_id, 'billing_first_name', $value );
						update_user_meta( $customer_id, 'shipping_first_name', $value );
					} elseif ( in_array( $key, ['billing_last_name', 'last_name'], true ) ) {
						update_user_meta( $customer_id, 'last_name', $value );
						update_user_meta( $customer_id, 'billing_last_name', $value );
						update_user_meta( $customer_id, 'shipping_last_name', $value );
					} elseif ( in_array( $key, ['billing_phone', 'phone'], true ) ) {
						update_user_meta( $customer_id, 'billing_phone', $value );
					}
				}
			}

			// Automatically log in the new user
			wp_set_auth_cookie( $customer_id, true );
			wp_set_current_user( $customer_id );
			$new_nonce = wp_create_nonce( 'shuriken_mbm_nonce' );
			
			wp_send_json_success( [
				'message'   => esc_html__( 'Registration successful! Loading...', 'shuriken-elements' ),
				'new_nonce' => $new_nonce
			] );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}

	/**
	 * AJAX: Get WooCommerce Endpoint Content
	 */
	public function ajax_get_wc_endpoint_content() {
		if ( ! is_user_logged_in() || ! class_exists( 'WooCommerce' ) ) {
			wp_send_json_error( esc_html__( 'Unauthorized or WooCommerce missing.', 'shuriken-elements' ) );
		}

		$endpoint = isset( $_POST['endpoint'] ) ? sanitize_text_field( $_POST['endpoint'] ) : '';
		$value    = isset( $_POST['value'] ) ? sanitize_text_field( $_POST['value'] ) : '';

		if ( empty( $endpoint ) ) {
			wp_send_json_error( esc_html__( 'Invalid endpoint.', 'shuriken-elements' ) );
		}

		ob_start();
		
		// Wrap in standard WC My Account container so scripts work if needed
		echo '<div class="woocommerce"><div class="woocommerce-MyAccount-content">';
		if ( ! empty( $value ) ) {
			do_action( 'woocommerce_account_' . $endpoint . '_endpoint', $value );
		} else {
			do_action( 'woocommerce_account_' . $endpoint . '_endpoint' );
		}
		echo '</div></div>';

		$html = ob_get_clean();
		wp_send_json_success( $html );
	}
}
