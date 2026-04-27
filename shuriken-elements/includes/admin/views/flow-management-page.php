<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_settings = [
    'force_login_checkout' => false,
    'allow_guest_checkout' => get_option( 'woocommerce_enable_guest_checkout', 'yes' ) === 'yes' ? true : false,
];

$saved_settings = get_option( 'shuriken_flow_management_settings', [] );
$settings = array_merge($default_settings, $saved_settings);

?>
<div class="wrap shuriken-admin-wrap">
    <div class="shuriken-header">
        <h1><?php esc_html_e('Flow Management', 'shuriken-elements'); ?></h1>
        <div>
            <button type="button" id="shuriken-save-changes" class="button button-primary">
                <?php esc_html_e('Save Changes', 'shuriken-elements'); ?>
            </button>
            <span class="spinner shuriken-spinner"></span>
        </div>
    </div>

    <div class="shuriken-editor-layout">
        <div class="shuriken-editor-left-panel" style="width: 100%;">
            <div class="shuriken-action-bar" style="margin-bottom: 20px;">
                <p><?php esc_html_e('Manage checkout and user flows.', 'shuriken-elements'); ?></p>
            </div>

            <div style="background:#fff; padding:30px; border-radius:8px; border:1px solid #eee;">
                
                <div style="margin-bottom: 30px;">
                    <h3><?php esc_html_e('Checkout Flow', 'shuriken-elements'); ?></h3>
                    <p style="color:#666; margin-bottom:15px;"><?php esc_html_e('Configure how users experience the checkout process.', 'shuriken-elements'); ?></p>

                    <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                        <label class="shuriken-toggle-switch">
                            <input type="checkbox" id="setting-guest-checkout" <?php checked($settings['allow_guest_checkout'], true); ?>>
                            <span class="shuriken-toggle-slider"></span>
                            <span style="font-weight:600; font-size:14px; margin-left:10px;"><?php esc_html_e('Allow Guest Checkout', 'shuriken-elements'); ?></span>
                        </label>
                        <p style="margin: 5px 0 0 50px; font-size: 13px; color: #777;">
                            <?php esc_html_e('If enabled, users can place orders without logging in or creating an account. (Syncs with WooCommerce guest checkout setting).', 'shuriken-elements'); ?>
                        </p>
                    </div>

                    <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                        <label class="shuriken-toggle-switch">
                            <input type="checkbox" id="setting-force-login" <?php checked(isset($settings['force_login_checkout']) && $settings['force_login_checkout'], true); ?>>
                            <span class="shuriken-toggle-slider"></span>
                            <span style="font-weight:600; font-size:14px; margin-left:10px;"><?php esc_html_e('Force Login Popup on "Place Order"', 'shuriken-elements'); ?></span>
                        </label>
                        <p style="margin: 5px 0 0 50px; font-size: 13px; color: #777;">
                            <?php esc_html_e('If a guest user clicks "Place Order", a login popup will appear. After successful login, the order will be placed automatically.', 'shuriken-elements'); ?>
                        </p>
                    </div>

                    <div style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 6px;">
                        <label class="shuriken-toggle-switch">
                            <input type="checkbox" id="setting-disable-redirect" <?php checked(isset($settings['disable_order_redirect']) && $settings['disable_order_redirect'], true); ?>>
                            <span class="shuriken-toggle-slider"></span>
                            <span style="font-weight:600; font-size:14px; margin-left:10px;"><?php esc_html_e('Show Order Confirmation Inline', 'shuriken-elements'); ?></span>
                        </label>
                        <p style="margin: 5px 0 0 50px; font-size: 13px; color: #777;">
                            <?php esc_html_e('If enabled, the order confirmation (Thank You page) will be shown inside the checkout popup instead of redirecting to the /order-received page.', 'shuriken-elements'); ?>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Mutual Exclusivity
    $('#setting-guest-checkout').on('change', function() {
        if ($(this).is(':checked')) {
            $('#setting-force-login').prop('checked', false);
        }
    });

    $('#setting-force-login').on('change', function() {
        if ($(this).is(':checked')) {
            $('#setting-guest-checkout').prop('checked', false);
        }
    });

    $('#shuriken-save-changes').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $spinner = $btn.siblings('.shuriken-spinner');
        
        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        var settings = {
            allow_guest_checkout: $('#setting-guest-checkout').is(':checked'),
            force_login_checkout: $('#setting-force-login').is(':checked'),
            disable_order_redirect: $('#setting-disable-redirect').is(':checked')
        };

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'shuriken_save_flow_settings',
                security: '<?php echo wp_create_nonce("shuriken_flow_nonce"); ?>',
                settings: JSON.stringify(settings)
            },
            success: function(response) {
                $btn.prop('disabled', false);
                $spinner.removeClass('is-active');
                if(response.success) {
                    alert('Settings saved successfully!');
                } else {
                    alert('Error saving settings.');
                }
            }
        });
    });
});
</script>
