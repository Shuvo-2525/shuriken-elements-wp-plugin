<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Function to render table rows
function shuriken_render_field_row( $field_name, $field_data ) {
    $type = isset($field_data['type']) ? $field_data['type'] : 'text';
    $label = isset($field_data['label']) ? $field_data['label'] : '';
    $placeholder = isset($field_data['placeholder']) ? $field_data['placeholder'] : '';
    $required = isset($field_data['required']) ? $field_data['required'] : false;
    $enabled = isset($field_data['enabled']) ? $field_data['enabled'] : true;
    $custom = isset($field_data['custom']) ? $field_data['custom'] : false;
    $priority = isset($field_data['priority']) ? $field_data['priority'] : 10;
    
    // New Advanced fields
    $default_val = isset($field_data['default']) ? $field_data['default'] : '';
    $validate = isset($field_data['validate']) && is_array($field_data['validate']) ? implode(',', $field_data['validate']) : '';
    $show_in_email = isset($field_data['show_in_email']) ? $field_data['show_in_email'] : true;
    $show_in_order = isset($field_data['show_in_order']) ? $field_data['show_in_order'] : true;

    // Convert class array to string
    $class = '';
    if (isset($field_data['class'])) {
        if (is_array($field_data['class'])) {
            $class = implode(' ', $field_data['class']);
        } else {
            $class = $field_data['class'];
        }
    }

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
        data-priority="<?php echo esc_attr($priority); ?>"
        data-placeholder="<?php echo esc_attr($placeholder); ?>"
        data-default="<?php echo esc_attr($default_val); ?>"
        data-validate="<?php echo esc_attr($validate); ?>"
        data-email="<?php echo $show_in_email ? '1' : '0'; ?>"
        data-order="<?php echo $show_in_order ? '1' : '0'; ?>"
        data-class="<?php echo esc_attr($class); ?>">
        
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

function shuriken_render_tab_content( $section_id, $fields_data ) {
    ?>
    <div id="<?php echo esc_attr($section_id); ?>" class="shuriken-tab-content <?php echo $section_id === 'tab-billing' ? 'active' : ''; ?>">
        
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
            <table class="shuriken-fields-table">
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
                    $section_key = str_replace('tab-', '', $section_id);
                    foreach($fields_data as $name => $field) {
                        shuriken_render_field_row($name, $field);
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
?>

<div class="wrap shuriken-admin-wrap">
    <div class="shuriken-header">
        <h1><?php esc_html_e('Checkout Field Editor', 'shuriken-elements'); ?></h1>
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
            <div class="shuriken-tabs">
                <button type="button" class="shuriken-tab-link active" data-target="tab-billing"><?php esc_html_e('Billing Fields', 'shuriken-elements'); ?></button>
                <button type="button" class="shuriken-tab-link" data-target="tab-shipping"><?php esc_html_e('Shipping Fields', 'shuriken-elements'); ?></button>
                <button type="button" class="shuriken-tab-link" data-target="tab-additional"><?php esc_html_e('Additional Fields', 'shuriken-elements'); ?></button>
                <button type="button" class="shuriken-tab-link" data-target="tab-coupon"><?php esc_html_e('Coupon Fields', 'shuriken-elements'); ?></button>
            </div>

            <?php shuriken_render_tab_content('tab-billing', $this->get_fields('billing')); ?>
            <?php shuriken_render_tab_content('tab-shipping', $this->get_fields('shipping')); ?>
            <?php shuriken_render_tab_content('tab-additional', $this->get_fields('additional')); ?>
            <?php shuriken_render_tab_content('tab-coupon', $this->get_fields('coupon')); ?>
        </div>

        <div class="shuriken-editor-right-panel shuriken-preview-panel">
            <div class="shuriken-preview-header">
                <h3><?php esc_html_e('Live Preview', 'shuriken-elements'); ?></h3>
                <span class="shuriken-preview-badge"><?php esc_html_e('Popup Style', 'shuriken-elements'); ?></span>
            </div>
            <div class="shuriken-preview-body">
                <!-- Mocking Popup Checkout Structure for styling inheritance -->
                <div class="shuriken-popup-checkout-container" style="position:relative; transform:none; top:auto; left:auto; width:100%; max-height:none; opacity:1; display:flex; box-shadow:none;">
                    <div class="shuriken-popup-checkout-body">
                        <div class="woocommerce">
                            <form name="checkout" method="post" class="checkout woocommerce-checkout" action="#">
                                <!-- We'll populate this with JS -->
                                <div id="customer_details" class="col2-set">
                                    <div class="col-1">
                                        <div class="woocommerce-billing-fields">
                                            <h3><?php esc_html_e('Billing details', 'woocommerce'); ?></h3>
                                            <div class="woocommerce-billing-fields__field-wrapper" id="preview-billing-fields"></div>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="woocommerce-shipping-fields">
                                            <h3 id="ship-to-different-address">
                                                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox" style="display:flex; align-items:center; gap:8px;">
                                                    <input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" checked type="checkbox" name="ship_to_different_address" value="1"> 
                                                    <span><?php esc_html_e('Ship to a different address?', 'woocommerce'); ?></span>
                                                </label>
                                            </h3>
                                            <div class="shipping_address">
                                                <div class="woocommerce-shipping-fields__field-wrapper" id="preview-shipping-fields"></div>
                                            </div>
                                        </div>
                                        <div class="woocommerce-additional-fields" style="margin-top:20px;">
                                            <h3><?php esc_html_e('Additional information', 'woocommerce'); ?></h3>
                                            <div class="woocommerce-additional-fields__field-wrapper" id="preview-additional-fields"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mock Coupon Section -->
                                <div class="checkout_coupon woocommerce-form-coupon" style="margin-top:30px; padding-top:20px; border-top:1px solid #eee;">
                                    <p><?php esc_html_e('If you have a coupon code, please apply it below.', 'woocommerce'); ?></p>
                                    <div id="preview-coupon-fields" style="display:flex; gap:10px; align-items:flex-end;"></div>
                                </div>
                                
                            </form>
                        </div>
                    </div>
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
                    <input type="text" id="f_name" placeholder="e.g. billing_custom_1">
                    <small><?php esc_html_e('Must be lowercase, alphanumeric, underscores.', 'shuriken-elements'); ?></small>
                </div>
                <div class="shuriken-form-row">
                    <label><?php esc_html_e('Type', 'shuriken-elements'); ?></label>
                    <select id="f_type">
                        <option value="text"><?php esc_html_e('Text', 'shuriken-elements'); ?></option>
                        <option value="textarea"><?php esc_html_e('Textarea', 'shuriken-elements'); ?></option>
                        <option value="email"><?php esc_html_e('Email', 'shuriken-elements'); ?></option>
                        <option value="tel"><?php esc_html_e('Phone', 'shuriken-elements'); ?></option>
                        <option value="select"><?php esc_html_e('Select', 'shuriken-elements'); ?></option>
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

            <div class="shuriken-grid-2">
                <div class="shuriken-form-row">
                    <label><?php esc_html_e('Default Value', 'shuriken-elements'); ?></label>
                    <input type="text" id="f_default">
                </div>
                <div class="shuriken-form-row">
                    <label><?php esc_html_e('CSS Class (Width)', 'shuriken-elements'); ?></label>
                    <select id="f_class">
                        <option value="form-row-wide"><?php esc_html_e('Full Width', 'shuriken-elements'); ?></option>
                        <option value="form-row-first"><?php esc_html_e('Half Width - Left', 'shuriken-elements'); ?></option>
                        <option value="form-row-last"><?php esc_html_e('Half Width - Right', 'shuriken-elements'); ?></option>
                    </select>
                </div>
            </div>

            <div class="shuriken-form-row">
                <label><?php esc_html_e('Validation Rules', 'shuriken-elements'); ?></label>
                <div class="shuriken-checkbox-group">
                    <label><input type="checkbox" class="f_validate" value="email"> Email</label>
                    <label><input type="checkbox" class="f_validate" value="phone"> Phone</label>
                    <label><input type="checkbox" class="f_validate" value="postcode"> Postcode</label>
                    <label><input type="checkbox" class="f_validate" value="state"> State</label>
                    <label><input type="checkbox" class="f_validate" value="number"> Number</label>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;">

            <label class="shuriken-toggle-switch">
                <input type="checkbox" id="f_required">
                <span class="shuriken-toggle-slider"></span>
                <?php esc_html_e('Required Field', 'shuriken-elements'); ?>
            </label>

            <label class="shuriken-toggle-switch">
                <input type="checkbox" id="f_email">
                <span class="shuriken-toggle-slider"></span>
                <?php esc_html_e('Show in Order Emails', 'shuriken-elements'); ?>
            </label>

            <label class="shuriken-toggle-switch">
                <input type="checkbox" id="f_order">
                <span class="shuriken-toggle-slider"></span>
                <?php esc_html_e('Show in Order Details Pages', 'shuriken-elements'); ?>
            </label>

        </form>
    </div>
    <div class="shuriken-modal-footer">
        <button type="button" class="button shuriken-cancel-field"><?php esc_html_e('Cancel', 'shuriken-elements'); ?></button>
        <button type="button" id="shuriken-save-field" class="button button-primary"><?php esc_html_e('Apply Changes', 'shuriken-elements'); ?></button>
    </div>
</div>
