<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Function to render table rows
function shuriken_render_signup_field_row( $field_name, $field_data ) {
    $type = isset($field_data['type']) ? $field_data['type'] : 'text';
    $label = isset($field_data['label']) ? $field_data['label'] : '';
    $placeholder = isset($field_data['placeholder']) ? $field_data['placeholder'] : '';
    $required = isset($field_data['required']) ? $field_data['required'] : false;
    $enabled = isset($field_data['enabled']) ? $field_data['enabled'] : true;
    $custom = isset($field_data['custom']) ? $field_data['custom'] : false;
    
    $req_html = $required ? '<span class="shuriken-badge req-yes">Yes</span>' : '<span class="shuriken-badge req-no">No</span>';
    $status_class = $enabled ? 'enabled' : '';
    $type_html = '<span class="shuriken-badge type-badge">' . esc_html($type) . '</span>';
    if($custom) {
        $type_html .= '<span class="shuriken-badge custom-badge">Custom</span>';
    }
    
    ?>
    <tr data-name="<?php echo esc_attr($field_name); ?>" 
        data-type="<?php echo esc_attr($type); ?>" 
        data-required="<?php echo $required ? '1' : '0'; ?>" 
        data-enabled="<?php echo $enabled ? '1' : '0'; ?>" 
        data-custom="<?php echo $custom ? '1' : '0'; ?>" 
        data-placeholder="<?php echo esc_attr($placeholder); ?>">
        
        <td width="20"><span class="shuriken-drag-handle"></span></td>
        <td width="30" class="shuriken-checkbox-wrap"><input type="checkbox" class="shuriken-row-checkbox"></td>
        <td width="40"><span class="shuriken-status-toggle <?php echo esc_attr($status_class); ?>"></span></td>
        <td class="col-name"><strong><?php echo esc_html($field_name); ?></strong></td>
        <td class="col-type"><?php echo $type_html; ?></td>
        <td class="col-label"><?php echo esc_html($label); ?></td>
        <td class="col-required"><?php echo $req_html; ?></td>
        <td width="150">
            <button type="button" class="button shuriken-edit-field"><?php esc_html_e('Edit', 'shuriken-elements'); ?></button>
        </td>
    </tr>
    <?php
}

$default_fields = [
    'billing_first_name' => ['type' => 'text', 'label' => 'First Name', 'enabled' => true, 'required' => true],
    'billing_last_name' => ['type' => 'text', 'label' => 'Last Name', 'enabled' => true, 'required' => true],
];

$saved_fields = get_option( 'shuriken_signup_fields', [] );
$fields = empty($saved_fields) ? $default_fields : $saved_fields;
?>

<div class="wrap shuriken-admin-wrap">
    <div class="shuriken-header">
        <h1><?php esc_html_e('Signup Field Editor', 'shuriken-elements'); ?></h1>
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
            <div id="tab-signup" class="shuriken-tab-content active">
                <div class="shuriken-action-bar">
                    <div class="shuriken-bulk-actions">
                        <span><?php esc_html_e('Bulk Actions:', 'shuriken-elements'); ?></span>
                        <button type="button" class="button shuriken-bulk-enable"><?php esc_html_e('Enable', 'shuriken-elements'); ?></button>
                        <button type="button" class="button shuriken-bulk-disable"><?php esc_html_e('Disable', 'shuriken-elements'); ?></button>
                        <button type="button" class="button button-danger shuriken-bulk-remove"><?php esc_html_e('Remove', 'shuriken-elements'); ?></button>
                    </div>
                    <div>
                        <button type="button" class="button button-primary shuriken-add-field">+ <?php esc_html_e('Add New Field', 'shuriken-elements'); ?></button>
                    </div>
                </div>

                <div class="shuriken-fields-table-wrapper">
                    <table class="shuriken-fields-table" id="signup-fields-table">
                        <thead>
                            <tr>
                                <th width="20"></th>
                                <th width="30" class="shuriken-checkbox-wrap"><input type="checkbox" class="shuriken-select-all"></th>
                                <th width="40"><?php esc_html_e('Status', 'shuriken-elements'); ?></th>
                                <th><?php esc_html_e('Name', 'shuriken-elements'); ?></th>
                                <th><?php esc_html_e('Type', 'shuriken-elements'); ?></th>
                                <th><?php esc_html_e('Label', 'shuriken-elements'); ?></th>
                                <th><?php esc_html_e('Required', 'shuriken-elements'); ?></th>
                                <th width="150"><?php esc_html_e('Actions', 'shuriken-elements'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach($fields as $name => $field) {
                                shuriken_render_signup_field_row($name, $field);
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="shuriken-editor-right-panel shuriken-preview-panel">
            <div class="shuriken-preview-header">
                <h3><?php esc_html_e('Live Preview', 'shuriken-elements'); ?></h3>
            </div>
            <div class="shuriken-preview-body" style="background:#f9f9f9; padding:30px;">
                <div style="background:#fff; padding:30px; border-radius:8px; border:1px solid #eee;">
                    <form id="preview-signup-form">
                        <div id="preview-signup-fields-container"></div>
                        <p class="form-row">
                            <label><?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
                            <input type="email" class="input-text" style="width:100%; margin-top:5px;" disabled value="user@example.com">
                        </p>
                        <p class="form-row">
                            <button type="button" class="button" disabled><?php esc_html_e('Register', 'woocommerce'); ?></button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Field Edit Modal -->
<div class="shuriken-modal-overlay"></div>
<div id="shuriken-field-modal" class="shuriken-modal">
    <div class="shuriken-modal-header">
        <h2><?php esc_html_e('Edit Field', 'shuriken-elements'); ?></h2>
        <button type="button" class="shuriken-modal-close">&times;</button>
    </div>
    <div class="shuriken-modal-body">
        <form id="shuriken-field-form">
            <div class="shuriken-grid-2">
                <div class="shuriken-form-row">
                    <label><?php esc_html_e('Name', 'shuriken-elements'); ?></label>
                    <input type="text" id="f_name" placeholder="e.g. signup_custom_1">
                </div>
                <div class="shuriken-form-row">
                    <label><?php esc_html_e('Type', 'shuriken-elements'); ?></label>
                    <select id="f_type">
                        <option value="text"><?php esc_html_e('Text', 'shuriken-elements'); ?></option>
                        <option value="tel"><?php esc_html_e('Phone', 'shuriken-elements'); ?></option>
                    </select>
                </div>
            </div>

            <div class="shuriken-grid-2">
                <div class="shuriken-form-row">
                    <label><?php esc_html_e('Label', 'shuriken-elements'); ?></label>
                    <input type="text" id="f_label">
                </div>
                <div class="shuriken-form-row">
                    <label><?php esc_html_e('Placeholder', 'shuriken-elements'); ?></label>
                    <input type="text" id="f_placeholder">
                </div>
            </div>

            <label class="shuriken-toggle-switch" style="margin-top:20px;">
                <input type="checkbox" id="f_required">
                <span class="shuriken-toggle-slider"></span>
                <?php esc_html_e('Required Field', 'shuriken-elements'); ?>
            </label>

        </form>
    </div>
    <div class="shuriken-modal-footer">
        <button type="button" class="button shuriken-cancel-field"><?php esc_html_e('Cancel', 'shuriken-elements'); ?></button>
        <button type="button" id="shuriken-save-field" class="button button-primary"><?php esc_html_e('Apply Changes', 'shuriken-elements'); ?></button>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let $modal = $('#shuriken-field-modal');
    let $overlay = $('.shuriken-modal-overlay');
    let currentRow = null;

    // Sortable
    $('#signup-fields-table tbody').sortable({
        handle: '.shuriken-drag-handle',
        update: function() {
            updatePreview();
        }
    });

    // Toggle Status
    $(document).on('click', '.shuriken-status-toggle', function() {
        $(this).toggleClass('enabled');
        $(this).closest('tr').attr('data-enabled', $(this).hasClass('enabled') ? '1' : '0');
        updatePreview();
    });

    // Open Add Modal
    $('.shuriken-add-field').on('click', function() {
        currentRow = null;
        $('#shuriken-field-form')[0].reset();
        $('#f_name').prop('readonly', false);
        $modal.addClass('active');
        $overlay.addClass('active');
    });

    // Open Edit Modal
    $(document).on('click', '.shuriken-edit-field', function() {
        currentRow = $(this).closest('tr');
        $('#f_name').val(currentRow.attr('data-name')).prop('readonly', !currentRow.attr('data-custom') || currentRow.attr('data-custom') === '0');
        $('#f_type').val(currentRow.attr('data-type'));
        $('#f_label').val(currentRow.find('.col-label').text());
        $('#f_placeholder').val(currentRow.attr('data-placeholder') || '');
        $('#f_required').prop('checked', currentRow.attr('data-required') === '1');

        $modal.addClass('active');
        $overlay.addClass('active');
    });

    // Close Modal
    $(document).on('click', '.shuriken-modal-close, .shuriken-cancel-field, .shuriken-modal-overlay', function(e) {
        e.preventDefault();
        $modal.removeClass('active');
        $overlay.removeClass('active');
    });

    // Save Modal
    $('#shuriken-save-field').on('click', function() {
        let name = $('#f_name').val().trim().toLowerCase().replace(/[^a-z0-9_]/g, '_');
        $('#f_name').val(name); // update visual in case it was sanitized
        
        if(!name) { alert('Name is required'); return; }

        let isDuplicate = false;
        $('#signup-fields-table tbody tr').each(function() {
            if ($(this).attr('data-name') === name && (!currentRow || !$(this).is(currentRow))) {
                isDuplicate = true;
            }
        });
        
        if (isDuplicate) {
            alert('A field with this name already exists.');
            return;
        }

        let type = $('#f_type').val();
        let label = $('#f_label').val();
        let placeholder = $('#f_placeholder').val();
        let req = $('#f_required').is(':checked') ? '1' : '0';
        let reqText = req === '1' ? '<span class="shuriken-badge req-yes">Yes</span>' : '<span class="shuriken-badge req-no">No</span>';

        if(currentRow) {
            currentRow.attr('data-name', name);
            currentRow.attr('data-type', type);
            currentRow.attr('data-required', req);
            currentRow.attr('data-placeholder', placeholder);
            currentRow.find('.col-name strong').text(name);
            currentRow.find('.col-type').html('<span class="shuriken-badge type-badge">'+type+'</span>' + (currentRow.attr('data-custom')==='1' ? '<span class="shuriken-badge custom-badge">Custom</span>':''));
            currentRow.find('.col-label').text(label);
            currentRow.find('.col-required').html(reqText);
        } else {
            let tr = `<tr data-name="${name}" data-type="${type}" data-required="${req}" data-enabled="1" data-custom="1" data-placeholder="${placeholder}">
                <td width="20"><span class="shuriken-drag-handle"></span></td>
                <td width="30" class="shuriken-checkbox-wrap"><input type="checkbox" class="shuriken-row-checkbox"></td>
                <td width="40"><span class="shuriken-status-toggle enabled"></span></td>
                <td class="col-name"><strong>${name}</strong></td>
                <td class="col-type"><span class="shuriken-badge type-badge">${type}</span><span class="shuriken-badge custom-badge">Custom</span></td>
                <td class="col-label">${label}</td>
                <td class="col-required">${reqText}</td>
                <td width="150"><button type="button" class="button shuriken-edit-field">Edit</button></td>
            </tr>`;
            $('#signup-fields-table tbody').append(tr);
        }

        $modal.removeClass('active');
        $overlay.removeClass('active');
        updatePreview();
    });

    function updatePreview() {
        let $container = $('#preview-signup-fields-container');
        $container.empty();

        $('#signup-fields-table tbody tr').each(function() {
            if($(this).attr('data-enabled') === '1') {
                let label = $(this).find('.col-label').text();
                let req = $(this).attr('data-required') === '1' ? ' <span class="required">*</span>' : '';
                let type = $(this).attr('data-type');
                let placeholder = $(this).attr('data-placeholder') || '';
                
                $container.append(`
                    <p class="form-row form-row-wide">
                        <label>${label}${req}</label>
                        <input type="${type}" class="input-text" placeholder="${placeholder}" style="width:100%; margin-top:5px;">
                    </p>
                `);
            }
        });
    }

    updatePreview();

    // Select All
    $('.shuriken-select-all').on('click', function() {
        $('.shuriken-row-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Bulk Actions
    $('.shuriken-bulk-enable, .shuriken-bulk-disable, .shuriken-bulk-remove').on('click', function() {
        let action = $(this).hasClass('shuriken-bulk-enable') ? 'enable' : ($(this).hasClass('shuriken-bulk-disable') ? 'disable' : 'remove');
        
        $('.shuriken-row-checkbox:checked').each(function() {
            let row = $(this).closest('tr');
            if(action === 'enable') {
                row.attr('data-enabled', '1');
                row.find('.shuriken-status-toggle').addClass('enabled');
            } else if(action === 'disable') {
                row.attr('data-enabled', '0');
                row.find('.shuriken-status-toggle').removeClass('enabled');
            } else if(action === 'remove' && row.attr('data-custom') === '1') {
                row.remove();
            }
        });
        updatePreview();
        $('.shuriken-select-all').prop('checked', false);
    });

    // Save
    $('#shuriken-save-changes').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $spinner = $btn.siblings('.shuriken-spinner');
        
        $btn.prop('disabled', true);
        $spinner.addClass('is-active');

        var fields = {};
        $('#signup-fields-table tbody tr').each(function() {
            var name = $(this).attr('data-name');
            fields[name] = {
                type: $(this).attr('data-type'),
                label: $(this).find('.col-label').text(),
                placeholder: $(this).attr('data-placeholder'),
                required: $(this).attr('data-required') === '1',
                enabled: $(this).attr('data-enabled') === '1',
                custom: $(this).attr('data-custom') === '1'
            };
        });

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'shuriken_save_signup_fields',
                security: '<?php echo wp_create_nonce("shuriken_signup_nonce"); ?>',
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
        if(confirm('Are you sure you want to reset to default fields?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'shuriken_reset_signup_fields',
                    security: '<?php echo wp_create_nonce("shuriken_signup_nonce"); ?>'
                },
                success: function() {
                    window.location.reload();
                }
            });
        }
    });
});
</script>
