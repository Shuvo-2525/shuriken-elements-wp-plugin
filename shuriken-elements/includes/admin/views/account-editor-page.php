<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$default_endpoints = [
    'dashboard'       => [ 'label' => 'Dashboard', 'enabled' => true, 'icon' => 'dashicons-dashboard' ],
    'orders'          => [ 'label' => 'Orders', 'enabled' => true, 'icon' => 'dashicons-cart' ],
    'downloads'       => [ 'label' => 'Downloads', 'enabled' => true, 'icon' => 'dashicons-download' ],
    'edit-address'    => [ 'label' => 'Addresses', 'enabled' => true, 'icon' => 'dashicons-location-alt' ],
    'edit-account'    => [ 'label' => 'Account details', 'enabled' => true, 'icon' => 'dashicons-admin-users' ],
    'customer-logout' => [ 'label' => 'Logout', 'enabled' => true, 'icon' => 'dashicons-migrate' ],
];

$saved_endpoints = get_option( 'shuriken_account_management_fields', [] );
$endpoints = empty($saved_endpoints) ? $default_endpoints : $saved_endpoints;

?>
<div class="wrap shuriken-admin-wrap">
    <div class="shuriken-header">
        <h1><?php esc_html_e('Account Management Editor', 'shuriken-elements'); ?></h1>
        <div>
            <button type="button" id="shuriken-reset-settings" class="button button-danger"><?php esc_html_e('Reset Defaults', 'shuriken-elements'); ?></button>
            <button type="button" id="shuriken-save-changes" class="button button-primary" style="margin-left: 10px;">
                <?php esc_html_e('Save Changes', 'shuriken-elements'); ?>
            </button>
            <span class="spinner shuriken-spinner"></span>
        </div>
    </div>

    <div class="shuriken-editor-layout">
        <div class="shuriken-editor-left-panel">
            <div class="shuriken-action-bar" style="margin-bottom: 20px;">
                <p><?php esc_html_e('Manage the endpoints displayed in the My Account popup/drawer.', 'shuriken-elements'); ?></p>
            </div>

            <div class="shuriken-fields-table-wrapper">
                <table class="shuriken-fields-table" id="account-endpoints-table">
                    <thead>
                        <tr>
                            <th width="20"></th>
                            <th width="40"><?php esc_html_e('Status', 'shuriken-elements'); ?></th>
                            <th><?php esc_html_e('Endpoint', 'shuriken-elements'); ?></th>
                            <th><?php esc_html_e('Label', 'shuriken-elements'); ?></th>
                            <th><?php esc_html_e('Icon Class', 'shuriken-elements'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $endpoints as $key => $data ) : 
                            $enabled = isset($data['enabled']) ? $data['enabled'] : true;
                            $status_class = $enabled ? 'enabled' : '';
                        ?>
                        <tr data-endpoint="<?php echo esc_attr($key); ?>" data-enabled="<?php echo $enabled ? '1' : '0'; ?>">
                            <td width="20"><span class="shuriken-drag-handle"></span></td>
                            <td width="40"><span class="shuriken-status-toggle <?php echo esc_attr($status_class); ?>"></span></td>
                            <td class="col-name"><strong><?php echo esc_html($key); ?></strong></td>
                            <td class="col-label">
                                <input type="text" class="endpoint-label-input" value="<?php echo esc_attr($data['label']); ?>" style="width: 100%;">
                            </td>
                            <td class="col-icon">
                                <input type="text" class="endpoint-icon-input" value="<?php echo esc_attr(isset($data['icon']) ? $data['icon'] : ''); ?>" style="width: 100%;">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="shuriken-editor-right-panel shuriken-preview-panel">
            <div class="shuriken-preview-header">
                <h3><?php esc_html_e('Live Preview', 'shuriken-elements'); ?></h3>
                <span class="shuriken-preview-badge"><?php esc_html_e('Popup Style', 'shuriken-elements'); ?></span>
            </div>
            <div class="shuriken-preview-body" style="background:#f0f2f5; padding: 40px; display:flex; justify-content:center;">
                
                <div style="width: 300px; background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden;">
                    <div style="padding: 20px; background: #1a1a1a; color: #fff; text-align: center;">
                        <div style="width:60px; height:60px; background:#444; border-radius:50%; margin:0 auto 10px;"></div>
                        <h4 style="margin:0; font-size:16px;">John Doe</h4>
                        <span style="font-size:12px; color:#aaa;">john@example.com</span>
                    </div>
                    <ul id="preview-account-menu" style="list-style:none; margin:0; padding:10px 0;">
                        <!-- JS will populate this -->
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Sortable
    $('#account-endpoints-table tbody').sortable({
        handle: '.shuriken-drag-handle',
        update: function() {
            updatePreview();
        }
    });

    // Toggle Status
    $(document).on('click', '.shuriken-status-toggle', function() {
        $(this).toggleClass('enabled');
        let $row = $(this).closest('tr');
        $row.attr('data-enabled', $(this).hasClass('enabled') ? '1' : '0');
        updatePreview();
    });

    // Live update on input change
    $(document).on('input', '.endpoint-label-input, .endpoint-icon-input', function() {
        updatePreview();
    });

    function updatePreview() {
        let $preview = $('#preview-account-menu');
        $preview.empty();

        $('#account-endpoints-table tbody tr').each(function() {
            if ($(this).attr('data-enabled') === '1') {
                let label = $(this).find('.endpoint-label-input').val();
                let icon = $(this).find('.endpoint-icon-input').val();
                
                $preview.append(`
                    <li style="padding: 10px 20px; border-bottom: 1px solid #f0f0f0; display:flex; align-items:center; gap:10px; color:#333; cursor:pointer;">
                        <span class="dashicons ${icon}" style="color:#888;"></span>
                        <span style="font-weight:500;">${label}</span>
                    </li>
                `);
            }
        });
    }

    // Init preview
    updatePreview();

    // Save
    $('#shuriken-save-changes').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $spinner = $btn.siblings('.shuriken-spinner');
        
        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        var fields = {};
        $('#account-endpoints-table tbody tr').each(function() {
            var endpoint = $(this).attr('data-endpoint');
            fields[endpoint] = {
                label: $(this).find('.endpoint-label-input').val(),
                icon: $(this).find('.endpoint-icon-input').val(),
                enabled: $(this).attr('data-enabled') === '1'
            };
        });

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'shuriken_save_account_fields',
                security: '<?php echo wp_create_nonce("shuriken_account_nonce"); ?>',
                fields: JSON.stringify(fields)
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

    // Reset
    $('#shuriken-reset-settings').on('click', function(e) {
        e.preventDefault();
        if(confirm('Are you sure you want to reset to default endpoints?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'shuriken_reset_account_fields',
                    security: '<?php echo wp_create_nonce("shuriken_account_nonce"); ?>'
                },
                success: function() {
                    window.location.reload();
                }
            });
        }
    });
});
</script>
