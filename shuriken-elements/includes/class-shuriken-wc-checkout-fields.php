<?php
namespace ShurikenElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Checkout Fields Engine
 */
class Class_Shuriken_WC_Checkout_Fields {

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		// Hook into WC Checkout fields
		add_filter( 'woocommerce_checkout_fields', [ $this, 'filter_checkout_fields' ], 999 );

		// Save custom fields to order meta
		add_action( 'woocommerce_checkout_update_order_meta', [ $this, 'save_custom_fields_to_order' ], 10, 2 );

		// Display in emails
		add_filter( 'woocommerce_email_order_meta_fields', [ $this, 'display_custom_fields_in_emails' ], 10, 3 );

		// Display on Thank You page and View Order
		add_action( 'woocommerce_order_details_after_order_table', [ $this, 'display_custom_fields_in_order_details' ], 20, 1 );

		// Hide Additional Information header if empty
		add_filter( 'woocommerce_enable_order_notes_field', [ $this, 'filter_order_notes_field' ], 999 );

		// Handle coupon features
		add_action( 'wp', [ $this, 'handle_coupon_features' ] );
	}

	/**
	 * Main filter for checkout fields
	 */
	public function filter_checkout_fields( $fields ) {
		$billing_saved = get_option( 'shuriken_wc_fields_billing' );
		$shipping_saved = get_option( 'shuriken_wc_fields_shipping' );
		$additional_saved = get_option( 'shuriken_wc_fields_additional' );

		if ( is_array( $billing_saved ) && ! empty( $billing_saved ) ) {
			$fields['billing'] = $this->apply_field_settings( isset($fields['billing']) ? $fields['billing'] : [], $billing_saved );
		}

		if ( is_array( $shipping_saved ) && ! empty( $shipping_saved ) ) {
			$fields['shipping'] = $this->apply_field_settings( isset($fields['shipping']) ? $fields['shipping'] : [], $shipping_saved );
		}

		if ( is_array( $additional_saved ) && ! empty( $additional_saved ) ) {
			// Additional fields usually go under 'order'
			if(!isset($fields['order'])) $fields['order'] = [];
			$fields['order'] = $this->apply_field_settings( $fields['order'], $additional_saved );
		}

		return $fields;
	}

	/**
	 * Hide order notes (Additional Information) header if there are no enabled fields
	 */
	public function filter_order_notes_field( $enabled ) {
		$additional_saved = get_option( 'shuriken_wc_fields_additional' );
		if ( is_array( $additional_saved ) && ! empty( $additional_saved ) ) {
			$has_active = false;
			foreach ( $additional_saved as $settings ) {
				if ( ! isset( $settings['enabled'] ) || $settings['enabled'] ) {
					$has_active = true;
					break;
				}
			}
			if ( ! $has_active ) {
				return false;
			}
		}
		return $enabled;
	}

	/**
	 * Handle coupon enabling/disabling and form rendering
	 */
	public function handle_coupon_features() {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		$coupon_saved = get_option( 'shuriken_wc_fields_coupon' );
		
		// Check if coupon is disabled in our settings
		if ( is_array( $coupon_saved ) && isset( $coupon_saved['coupon_code'] ) && isset( $coupon_saved['coupon_code']['enabled'] ) && ! $coupon_saved['coupon_code']['enabled'] ) {
			add_filter( 'woocommerce_coupons_enabled', '__return_false', 999 );
			return;
		}

		// If enabled, remove default WooCommerce toggle and form
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_message', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
		
        $position = isset( $coupon_saved['coupon_code']['position'] ) ? $coupon_saved['coupon_code']['position'] : 'woocommerce_before_checkout_form';

        if ( in_array( $position, ['woocommerce_before_checkout_form', 'woocommerce_after_checkout_form'] ) ) {
            add_action( $position, [ $this, 'custom_coupon_form_output' ], 10 );
        } else {
            // Output outside the form first, then we use JS to move it
            add_action( 'woocommerce_before_checkout_form', [ $this, 'custom_coupon_form_output_inline' ], 10 );
        }
            
        add_action( 'wp_footer', function() use ($position) {
            ?>
            <script>
            jQuery(document).ready(function($) {
                
                function moveShurikenCoupon() {
                    var pos = '<?php echo esc_js($position); ?>';
                    
                    if (pos === 'woocommerce_before_checkout_form' || pos === 'woocommerce_after_checkout_form') {
                        return;
                    }
                    
                    $('.shuriken-inline-coupon-wrapper').each(function() {
                        var $couponBlock = $(this);
                        var $wrapper = $couponBlock.closest('.woocommerce');
                        var $checkoutForm = $wrapper.find('form.checkout');
                        
                        // If form isn't found, maybe it's not rendered yet
                        if (!$checkoutForm.length) return;
                        
                        if (pos === 'inline_before_customer_details') {
                            $checkoutForm.find('#customer_details').before($couponBlock);
                        } else if (pos === 'inline_before_order_review') {
                            // Put it before the heading if it exists, otherwise before the table
                            var $heading = $checkoutForm.find('#order_review_heading');
                            if ($heading.length) {
                                $heading.before($couponBlock);
                            } else {
                                $checkoutForm.find('#order_review').before($couponBlock);
                            }
                        } else if (pos === 'inline_before_payment_methods') {
                            $checkoutForm.find('#payment').before($couponBlock);
                        }
                        
                        $couponBlock.show();
                    });
                }

                // Move on load
                moveShurikenCoupon();
                
                // Move back outside before checkout updates so it isn't destroyed
                $(document.body).on('update_checkout', function() {
                    var $couponBlock = $('.shuriken-inline-coupon-wrapper');
                    var $checkoutForm = $('form.checkout');
                    if ($couponBlock.length && $checkoutForm.length && $checkoutForm.has($couponBlock).length) {
                        $checkoutForm.before($couponBlock);
                        $couponBlock.hide();
                    }
                });
                
                // Move after checkout updates (AJAX)
                $(document.body).on('updated_checkout', function() {
                    moveShurikenCoupon();
                });

                // Custom AJAX submission since it's a div now, not a form
                $(document).on('click', '.shuriken-inline-coupon-wrapper button[name="apply_coupon"]', function(e) {
                    e.preventDefault();
                    var code = $(this).siblings('input[name="coupon_code"]').val();
                    if (!code) return;
                    
                    var $wrapper = $(this).closest('.woocommerce');
                    if (typeof wc_checkout_params === 'undefined') return;

                    $wrapper.addClass('processing').block({message: null, overlayCSS: {background: '#fff', opacity: 0.6}});

                    $.ajax({
                        type: 'POST',
                        url: wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'apply_coupon' ),
                        data: {
                            security: wc_checkout_params.apply_coupon_nonce,
                            coupon_code: code
                        },
                        success: function( response ) {
                            $('.woocommerce-error, .woocommerce-message').remove();
                            if ( response ) {
                                $('form.checkout').before( response );
                                $wrapper.removeClass('processing').unblock();
                                $( document.body ).trigger( 'applied_coupon_in_checkout', [ response ] );
                                $( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );
                            }
                        },
                        error: function() {
                            $wrapper.removeClass('processing').unblock();
                        }
                    });
                });
            });
            </script>
            <?php
        }, 999 );
	}

    public function custom_coupon_form_output_inline() {
		$coupon_saved = get_option( 'shuriken_wc_fields_coupon' );
		$label = isset( $coupon_saved['coupon_code']['label'] ) && ! empty( $coupon_saved['coupon_code']['label'] ) ? $coupon_saved['coupon_code']['label'] : __( 'If you have a coupon code, please apply it below.', 'woocommerce' );
		$placeholder = isset( $coupon_saved['coupon_code']['placeholder'] ) && ! empty( $coupon_saved['coupon_code']['placeholder'] ) ? $coupon_saved['coupon_code']['placeholder'] : __( 'Coupon code', 'woocommerce' );
		
		?>
		<div class="checkout_coupon woocommerce-form-coupon shuriken-inline-coupon-wrapper" style="display:none; margin-bottom: 25px;">
			<p><?php echo esc_html( $label ); ?></p>
			<div style="display:flex; gap:10px;">
				<input type="text" name="coupon_code" class="input-text" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="" style="flex-grow:1;" />
				<button type="button" class="button" name="apply_coupon"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Output custom inline coupon form
	 */
	public function custom_coupon_form_output() {
		$coupon_saved = get_option( 'shuriken_wc_fields_coupon' );
		$label = isset( $coupon_saved['coupon_code']['label'] ) && ! empty( $coupon_saved['coupon_code']['label'] ) ? $coupon_saved['coupon_code']['label'] : __( 'If you have a coupon code, please apply it below.', 'woocommerce' );
		$placeholder = isset( $coupon_saved['coupon_code']['placeholder'] ) && ! empty( $coupon_saved['coupon_code']['placeholder'] ) ? $coupon_saved['coupon_code']['placeholder'] : __( 'Coupon code', 'woocommerce' );
		
		?>
		<div class="checkout_coupon woocommerce-form-coupon shuriken-inline-coupon-wrapper" style="display:block !important; margin-bottom: 25px;">
			<p><?php echo esc_html( $label ); ?></p>
			<div style="display:flex; gap:10px;">
				<input type="text" name="coupon_code" class="input-text" placeholder="<?php echo esc_attr( $placeholder ); ?>" id="coupon_code" value="" style="flex-grow:1;" />
				<button type="button" class="button" name="apply_coupon"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Apply settings to a group of fields
	 */
	private function apply_field_settings( $wc_fields, $saved_fields ) {
		$new_fields = [];

		foreach ( $saved_fields as $name => $settings ) {
			// Skip if disabled
			if ( isset( $settings['enabled'] ) && ! $settings['enabled'] ) {
				continue;
			}

			$field = isset( $wc_fields[$name] ) ? $wc_fields[$name] : [];

			// Apply settings
			$field['type'] = isset($settings['type']) ? $settings['type'] : (isset($field['type']) ? $field['type'] : 'text');
			
			if ( ! empty( $settings['label'] ) ) {
				$field['label'] = $settings['label'];
			}
			
			if ( isset( $settings['placeholder'] ) ) {
				$field['placeholder'] = $settings['placeholder'];
			}
			
			$field['required'] = isset( $settings['required'] ) ? (bool) $settings['required'] : false;
			$field['priority'] = isset( $settings['priority'] ) ? (int) $settings['priority'] : 10;
			
			// Apply Advanced Settings
			if ( ! empty( $settings['default'] ) ) {
				$field['default'] = $settings['default'];
			}
			
			if ( ! empty( $settings['validate'] ) && is_array( $settings['validate'] ) ) {
				$field['validate'] = $settings['validate'];
			}

			if ( isset( $settings['class'] ) && is_array( $settings['class'] ) ) {
				// Remove old width classes, keep others
				$old_classes = isset($field['class']) && is_array($field['class']) ? $field['class'] : [];
				$old_classes = array_diff($old_classes, ['form-row-first', 'form-row-last', 'form-row-wide']);
				$field['class'] = array_merge( $old_classes, $settings['class'] );
			}

            // Flag as custom if it is
            if( isset($settings['custom']) && $settings['custom'] ) {
                $field['shuriken_custom'] = true;
            }

			$new_fields[$name] = $field;
		}

		// What if WC has fields we didn't save? (e.g., newly added by another plugin). 
        // We'll append them to the end.
		foreach ( $wc_fields as $name => $field ) {
			if ( ! isset( $new_fields[$name] ) && ! isset( $saved_fields[$name] ) ) {
				$new_fields[$name] = $field;
			}
		}

        // Sort by priority
        uasort( $new_fields, function($a, $b) {
            $p1 = isset($a['priority']) ? (int) $a['priority'] : 10;
            $p2 = isset($b['priority']) ? (int) $b['priority'] : 10;
            return $p1 - $p2;
        });

		return $new_fields;
	}

	/**
	 * Save custom fields to order
	 */
	public function save_custom_fields_to_order( $order_id, $data ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) return;

		$sections = [
			get_option( 'shuriken_wc_fields_billing' ),
			get_option( 'shuriken_wc_fields_shipping' ),
			get_option( 'shuriken_wc_fields_additional' )
		];

		foreach ( $sections as $saved_fields ) {
			if ( ! is_array( $saved_fields ) ) continue;

			foreach ( $saved_fields as $name => $settings ) {
				// Only process custom fields that are enabled
				if ( isset( $settings['custom'] ) && $settings['custom'] && isset( $settings['enabled'] ) && $settings['enabled'] ) {
					
                    if ( isset( $_POST[$name] ) ) {
						$value = sanitize_text_field( $_POST[$name] );
                        if ( $settings['type'] === 'textarea' ) {
                            $value = sanitize_textarea_field( $_POST[$name] );
                        } elseif ( $settings['type'] === 'email' ) {
                            $value = sanitize_email( $_POST[$name] );
                        }
						
						if ( ! empty( $value ) ) {
							$order->update_meta_data( $name, $value );
						}
					}
				}
			}
		}

		$order->save();
	}

	/**
	 * Helper to get all active custom fields
	 */
	private function get_active_custom_fields() {
		$custom_fields = [];
		$sections = [
			get_option( 'shuriken_wc_fields_billing' ),
			get_option( 'shuriken_wc_fields_shipping' ),
			get_option( 'shuriken_wc_fields_additional' )
		];

		foreach ( $sections as $saved_fields ) {
			if ( ! is_array( $saved_fields ) ) continue;
			foreach ( $saved_fields as $name => $settings ) {
				if ( isset( $settings['custom'] ) && $settings['custom'] && isset( $settings['enabled'] ) && $settings['enabled'] ) {
					$custom_fields[$name] = $settings;
				}
			}
		}
		return $custom_fields;
	}

	/**
	 * Display in emails
	 */
	public function display_custom_fields_in_emails( $fields, $sent_to_admin, $order ) {
		if ( ! $order ) return $fields;

		$custom_fields = $this->get_active_custom_fields();

		foreach ( $custom_fields as $name => $settings ) {
			// Check if we should display in email
			if ( isset( $settings['show_in_email'] ) && ! $settings['show_in_email'] ) {
				continue;
			}

			$value = $order->get_meta( $name, true );
			if ( ! empty( $value ) ) {
				$fields[$name] = [
					'label' => ! empty( $settings['label'] ) ? $settings['label'] : $name,
					'value' => $value,
				];
			}
		}

		return $fields;
	}

	/**
	 * Display in order details (Thank You page & My Account)
	 */
	public function display_custom_fields_in_order_details( $order ) {
		if ( ! $order ) return;

		$custom_fields = $this->get_active_custom_fields();
		$html = '';

		foreach ( $custom_fields as $name => $settings ) {
			// Check if we should display in order details
			if ( isset( $settings['show_in_order'] ) && ! $settings['show_in_order'] ) {
				continue;
			}

			$value = $order->get_meta( $name, true );
			if ( ! empty( $value ) ) {
				$label = ! empty( $settings['label'] ) ? $settings['label'] : $name;
                if( $settings['type'] === 'textarea' ) {
                    $value = nl2br( esc_html( $value ) );
                } else {
                    $value = esc_html( $value );
                }
				
				$html .= '<tr><th>' . esc_html( $label ) . ':</th><td>' . wp_kses_post( $value ) . '</td></tr>';
			}
		}

		if ( ! empty( $html ) ) {
			echo '<h2 class="woocommerce-column__title">' . esc_html__( 'Additional Information', 'shuriken-elements' ) . '</h2>';
			echo '<table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">';
			echo $html;
			echo '</table>';
		}
	}
}
