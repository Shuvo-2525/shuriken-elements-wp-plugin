(function($) {
    'use strict';

    $(document).ready(function() {

        // Tab Switching
        $('.shuriken-tab-link').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('data-target');
            
            $('.shuriken-tab-link').removeClass('active');
            $(this).addClass('active');
            
            $('.shuriken-tab-content').removeClass('active');
            $('#' + target).addClass('active');
        });

        // Make table rows sortable
        $('.shuriken-fields-table tbody').sortable({
            handle: '.shuriken-drag-handle',
            cursor: 'grabbing',
            update: function(event, ui) {
                var section = $(this).closest('.shuriken-tab-content').attr('id');
                recalculatePriorities(section);
                if (typeof renderLivePreview === 'function') renderLivePreview();
            }
        });

        function recalculatePriorities(sectionId) {
            var priority = 10;
            $('#' + sectionId + ' .shuriken-fields-table tbody tr').each(function() {
                $(this).attr('data-priority', priority);
                priority += 10;
            });
        }

        // Toggle Enabled Status (Single Row)
        $(document).on('click', '.shuriken-status-toggle', function() {
            $(this).toggleClass('enabled');
            var isEnabled = $(this).hasClass('enabled');
            $(this).closest('tr').attr('data-enabled', isEnabled ? '1' : '0');
            if (typeof renderLivePreview === 'function') renderLivePreview();
        });

        // Select All Checkboxes
        $('.shuriken-select-all').on('change', function() {
            var isChecked = $(this).is(':checked');
            $(this).closest('.shuriken-tab-content').find('.shuriken-row-checkbox').prop('checked', isChecked);
        });

        // Bulk Actions
        $('.shuriken-bulk-enable').on('click', function() {
            var $tab = $(this).closest('.shuriken-tab-content');
            $tab.find('.shuriken-row-checkbox:checked').each(function() {
                var $row = $(this).closest('tr');
                $row.attr('data-enabled', '1');
                $row.find('.shuriken-status-toggle').addClass('enabled');
            });
            if (typeof renderLivePreview === 'function') renderLivePreview();
        });

        $('.shuriken-bulk-disable').on('click', function() {
            var $tab = $(this).closest('.shuriken-tab-content');
            $tab.find('.shuriken-row-checkbox:checked').each(function() {
                var $row = $(this).closest('tr');
                $row.attr('data-enabled', '0');
                $row.find('.shuriken-status-toggle').removeClass('enabled');
            });
            if (typeof renderLivePreview === 'function') renderLivePreview();
        });

        $('.shuriken-bulk-remove').on('click', function() {
            if(confirm('Are you sure you want to remove selected custom fields? (Default fields cannot be removed)')) {
                var $tab = $(this).closest('.shuriken-tab-content');
                $tab.find('.shuriken-row-checkbox:checked').each(function() {
                    var $row = $(this).closest('tr');
                    if($row.attr('data-custom') === '1') {
                        $row.remove();
                    }
                });
                if (typeof renderLivePreview === 'function') renderLivePreview();
            }
        });

        // Edit Modal
        var $modal = $('#shuriken-field-modal');
        var $overlay = $('.shuriken-modal-overlay');
        var currentEditRow = null;

        $(document).on('click', '.shuriken-edit-field', function(e) {
            e.preventDefault();
            var $row = $(this).closest('tr');
            currentEditRow = $row;
            
            var name = $row.attr('data-name');
            var type = $row.attr('data-type');
            var label = $row.find('.col-label').text().trim();
            var placeholder = $row.attr('data-placeholder') || '';
            var required = $row.attr('data-required') === '1';
            var cssClass = $row.attr('data-class');
            var isCustom = $row.attr('data-custom') === '1';
            
            // New V2 fields
            var defaultVal = $row.attr('data-default') || '';
            var validateStr = $row.attr('data-validate') || '';
            var showEmail = $row.attr('data-email') !== '0'; // Default to true if not set
            var showOrder = $row.attr('data-order') !== '0';
            var position = $row.attr('data-position') || 'woocommerce_before_checkout_form';

            $('#f_name').val(name).prop('readonly', false);
            $('#f_type').val(type).prop('disabled', false);
            $('#f_label').val(label);
            $('#f_placeholder').val(placeholder);
            $('#f_required').prop('checked', required);
            $('#f_class').val(cssClass);
            $('#f_default').val(defaultVal);
            $('#f_email').prop('checked', showEmail);
            $('#f_order').prop('checked', showOrder);
            $('#f_position').val(position);

            if (name === 'coupon_code') {
                $('#row_f_position').show();
            } else {
                $('#row_f_position').hide();
            }

            // Set validation checkboxes
            $('.f_validate').prop('checked', false);
            if(validateStr) {
                var valArray = validateStr.split(',');
                $('.f_validate').each(function() {
                    if(valArray.includes($(this).val())) {
                        $(this).prop('checked', true);
                    }
                });
            }

            $('.shuriken-modal-header h2').text('Edit Field: ' + name);
            $overlay.fadeIn('fast');
            $modal.fadeIn('fast');
        });

        // Add New Field
        $('.shuriken-add-field').on('click', function(e) {
            e.preventDefault();
            currentEditRow = null;
            $('#shuriken-field-form')[0].reset();
            
            // Set defaults for toggles
            $('#f_email').prop('checked', true);
            $('#f_order').prop('checked', true);
            
            // Generate basic name based on tab
            var activeTab = $('.shuriken-tab-content.active').attr('id');
            var prefix = 'billing_';
            if(activeTab === 'tab-shipping') prefix = 'shipping_';
            if(activeTab === 'tab-additional') prefix = 'order_';
            
            $('#f_name').val(prefix + 'custom_').prop('readonly', false);
            $('#f_type').prop('disabled', false);
            
            $('#row_f_position').hide();
            
            $('.shuriken-modal-header h2').text('Add New Field');
            $overlay.fadeIn('fast');
            $modal.fadeIn('fast');
        });

        // Close Modal
        $('.shuriken-modal-close, .shuriken-modal-overlay, .shuriken-cancel-field').on('click', function(e) {
            e.preventDefault();
            $overlay.fadeOut('fast');
            $modal.fadeOut('fast');
        });

        // Save Field (in Modal)
        $('#shuriken-save-field').on('click', function(e) {
            e.preventDefault();
            
            var name = $('#f_name').val().trim().replace(/[^a-z0-9_]/gi, '').toLowerCase();
            if(!name) { alert('Field name is required and must be alphanumeric.'); return; }
            
            var type = $('#f_type').val();
            var label = $('#f_label').val().trim();
            var placeholder = $('#f_placeholder').val().trim();
            var required = $('#f_required').is(':checked') ? '1' : '0';
            var cssClass = $('#f_class').val();
            var reqHtml = required === '1' ? '<span class="shuriken-badge req-yes">Yes</span>' : '<span class="shuriken-badge req-no">No</span>';
            
            // V2 Fields
            var defaultVal = $('#f_default').val().trim();
            var showEmail = $('#f_email').is(':checked') ? '1' : '0';
            var showOrder = $('#f_order').is(':checked') ? '1' : '0';
            var position = $('#f_position').val();
            
            var validateArray = [];
            $('.f_validate:checked').each(function() {
                validateArray.push($(this).val());
            });
            var validateStr = validateArray.join(',');
            
            if (currentEditRow) {
                // Update existing row
                currentEditRow.attr('data-name', name);
                currentEditRow.attr('data-type', type);
                currentEditRow.find('.col-name').html('<strong>'+name+'</strong>');
                
                var isCustomRow = currentEditRow.attr('data-custom') === '1';
                var customBadge = isCustomRow ? '<span class="shuriken-badge custom-badge">Custom</span>' : '';
                currentEditRow.find('.col-type').html('<span class="shuriken-badge type-badge">' + type + '</span>' + customBadge);

                currentEditRow.attr('data-placeholder', placeholder);
                currentEditRow.attr('data-required', required);
                currentEditRow.attr('data-class', cssClass);
                currentEditRow.attr('data-default', defaultVal);
                currentEditRow.attr('data-validate', validateStr);
                currentEditRow.attr('data-email', showEmail);
                currentEditRow.attr('data-order', showOrder);
                if (name === 'coupon_code') {
                    currentEditRow.attr('data-position', position);
                }
                currentEditRow.find('.col-label').text(label);
                currentEditRow.find('.col-required').html(reqHtml);
            } else {
                // Add new row to active tab
                var priority = 999; 
                var newRow = `
                    <tr data-name="${name}" data-type="${type}" data-required="${required}" data-enabled="1" data-custom="1" data-priority="${priority}" data-placeholder="${placeholder}" data-class="${cssClass}" data-default="${defaultVal}" data-validate="${validateStr}" data-email="${showEmail}" data-order="${showOrder}" data-position="${position}">
                        <td width="20"><span class="shuriken-drag-handle"></span></td>
                        <td width="30" class="shuriken-checkbox-wrap"><input type="checkbox" class="shuriken-row-checkbox"></td>
                        <td width="40"><span class="shuriken-status-toggle enabled"></span></td>
                        <td class="col-name"><strong>${name}</strong></td>
                        <td class="col-type"><span class="shuriken-badge type-badge">${type}</span><span class="shuriken-badge custom-badge">Custom</span></td>
                        <td class="col-label">${label}</td>
                        <td class="col-required">${reqHtml}</td>
                        <td width="150">
                            <button type="button" class="button shuriken-edit-field">Edit</button>
                        </td>
                    </tr>
                `;
                $('.shuriken-tab-content.active .shuriken-fields-table tbody').append(newRow);
                recalculatePriorities($('.shuriken-tab-content.active').attr('id'));
            }
            
            $overlay.fadeOut('fast');
            $modal.fadeOut('fast');
            if (typeof renderLivePreview === 'function') renderLivePreview();
        });

        // Save Changes (AJAX)
        $('#shuriken-save-changes').on('click', function(e) {
            e.preventDefault();
            
            var data = {
                billing: [],
                shipping: [],
                additional: [],
                coupon: []
            };

            // Loop through each tab and gather data
            function gatherFields(sectionId, arrayRef) {
                $('#' + sectionId + ' .shuriken-fields-table tbody tr').each(function() {
                    var $row = $(this);
                    
                    var validateStr = $row.attr('data-validate');
                    var validateArr = validateStr ? validateStr.split(',') : [];
                    
                    arrayRef.push({
                        name: $row.attr('data-name'),
                        type: $row.attr('data-type'),
                        label: $row.find('.col-label').text().trim(),
                        placeholder: $row.attr('data-placeholder'),
                        required: $row.attr('data-required') === '1',
                        enabled: $row.attr('data-enabled') === '1',
                        custom: $row.attr('data-custom') === '1',
                        priority: parseInt($row.attr('data-priority')),
                        class: $row.attr('data-class'),
                        default: $row.attr('data-default'),
                        validate: validateArr,
                        show_in_email: $row.attr('data-email') === '1',
                        show_in_order: $row.attr('data-order') === '1',
                        position: $row.attr('data-position')
                    });
                });
            }

            gatherFields('tab-billing', data.billing);
            gatherFields('tab-shipping', data.shipping);
            gatherFields('tab-additional', data.additional);
            gatherFields('tab-coupon', data.coupon);

            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');
            $('.shuriken-spinner').addClass('is-active');

            $.ajax({
                url: shuriken_checkout_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'shuriken_save_checkout_fields',
                    security: shuriken_checkout_obj.nonce,
                    fields: JSON.stringify(data)
                },
                success: function(response) {
                    if (response.success) {
                        alert('Settings saved successfully!');
                    } else {
                        alert('Error saving settings: ' + response.data);
                    }
                },
                error: function() {
                    alert('An error occurred.');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save Changes');
                    $('.shuriken-spinner').removeClass('is-active');
                }
            });
        });

        // Reset Settings
        $('#shuriken-reset-settings').on('click', function(e) {
            e.preventDefault();
            if(confirm('Are you sure you want to reset all checkout fields to WooCommerce defaults? Custom fields will be lost.')) {
                
                var $btn = $(this);
                $btn.prop('disabled', true).text('Resetting...');

                $.ajax({
                    url: shuriken_checkout_obj.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'shuriken_reset_checkout_fields',
                        security: shuriken_checkout_obj.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            alert('Error resetting settings.');
                            $btn.prop('disabled', false).text('Reset Defaults');
                        }
                    }
                });
            }
        });

        // Live Preview Functionality
        function renderLivePreview() {
            var sections = {
                'tab-billing': '#preview-billing-fields',
                'tab-shipping': '#preview-shipping-fields',
                'tab-additional': '#preview-additional-fields',
                'tab-coupon': '#preview-coupon-fields'
            };

            $.each(sections, function(tabId, containerSelector) {
                var $container = $(containerSelector);
                if (!$container.length) return;
                $container.empty();

                // Get enabled rows
                var $rows = $('#' + tabId + ' .shuriken-fields-table tbody tr[data-enabled="1"]');
                
                $rows.each(function() {
                    var $row = $(this);
                    var name = $row.attr('data-name');
                    var type = $row.attr('data-type');
                    var label = $row.find('.col-label').text().trim();
                    var placeholder = $row.attr('data-placeholder') || '';
                    var required = $row.attr('data-required') === '1';
                    var cssClass = $row.attr('data-class') || 'form-row-wide';

                    var reqHtml = required ? '&nbsp;<abbr class="required" title="required">*</abbr>' : '';
                    
                    var inputHtml = '';
                    if (type === 'textarea') {
                        inputHtml = '<textarea name="'+name+'" class="input-text" placeholder="'+placeholder+'"></textarea>';
                    } else if (type === 'select') {
                        inputHtml = '<select name="'+name+'" class="select"><option value="">'+(placeholder || 'Select an option...')+'</option></select>';
                    } else {
                        var inputType = type;
                        if (['text', 'email', 'tel'].indexOf(type) === -1) inputType = 'text';
                        inputHtml = '<input type="'+inputType+'" class="input-text" name="'+name+'" placeholder="'+placeholder+'">';
                    }

                    var html = `
                        <p class="form-row ${cssClass}" id="${name}_field" data-source-name="${name}" data-source-tab="${tabId}">
                            <label class="">${label}${reqHtml}</label>
                            <span class="woocommerce-input-wrapper">
                                ${inputHtml}
                            </span>
                        </p>
                    `;
                    $container.append(html);
                });

                // Make preview sortable
                if ($container.hasClass('ui-sortable')) {
                    $container.sortable('destroy');
                }
                
                $container.sortable({
                    items: '> .form-row',
                    cursor: 'grabbing',
                    update: function(event, ui) {
                        var $sortedItem = ui.item;
                        var sourceName = $sortedItem.attr('data-source-name');
                        var tabId = $sortedItem.attr('data-source-tab');
                        var newIndex = $sortedItem.index();
                        
                        var $tableBody = $('#' + tabId + ' .shuriken-fields-table tbody');
                        var $targetTr = $tableBody.find('tr[data-name="' + sourceName + '"]');
                        var currentEnabledIndex = $tableBody.find('tr[data-enabled="1"]').index($targetTr);
                        
                        if (newIndex !== currentEnabledIndex) {
                            $targetTr.detach();
                            var $newSiblings = $tableBody.find('tr[data-enabled="1"]');
                            if (newIndex === 0) {
                                if ($newSiblings.length > 0) {
                                    $newSiblings.first().before($targetTr);
                                } else {
                                    $tableBody.append($targetTr);
                                }
                            } else {
                                $newSiblings.eq(newIndex - 1).after($targetTr);
                            }
                        }

                        recalculatePriorities(tabId);
                    }
                });
            });

            // Move coupon section visually based on position setting
            var $couponRow = $('#tab-coupon .shuriken-fields-table tbody tr[data-name="coupon_code"]');
            var $couponPreview = $('.woocommerce-form-coupon');
            if ($couponRow.length && $couponRow.attr('data-enabled') === '1') {
                $couponPreview.show();
                var pos = $couponRow.attr('data-position');
                var $checkoutForm = $('form.checkout');
                
                if (pos === 'woocommerce_before_checkout_form') {
                    $checkoutForm.before($couponPreview);
                } else if (pos === 'woocommerce_after_checkout_form') {
                    $checkoutForm.after($couponPreview);
                } else if (pos === 'inline_before_customer_details') {
                    $('#customer_details').before($couponPreview);
                } else if (pos === 'inline_before_order_review') {
                    $checkoutForm.append($couponPreview); // Simple append to bottom for now since order review isn't mocked yet, or append to after customer details
                } else if (pos === 'inline_before_payment_methods') {
                    $checkoutForm.append($couponPreview);
                } else {
                    $checkoutForm.before($couponPreview);
                }
            } else {
                $couponPreview.hide();
            }
        }

        // Initialize Live Preview
        setTimeout(renderLivePreview, 100);

        // Click on preview field to edit
        $(document).on('click', '.shuriken-preview-body .form-row', function() {
            var sourceName = $(this).attr('data-source-name');
            var tabId = $(this).attr('data-source-tab');
            
            // Switch tab
            $('.shuriken-tab-link[data-target="' + tabId + '"]').click();
            
            // Find row and click edit
            var $row = $('#' + tabId + ' .shuriken-fields-table tbody tr[data-name="' + sourceName + '"]');
            if ($row.length) {
                // Scroll to row
                $row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Flash effect
                $row.css('background-color', '#fffbeb');
                setTimeout(function() { $row.css('background-color', ''); }, 1000);
                
                // Click edit
                $row.find('.shuriken-edit-field').click();
            }
        });

    });

})(jQuery);
