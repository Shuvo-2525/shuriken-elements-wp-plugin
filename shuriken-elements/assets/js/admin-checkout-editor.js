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
        });

        $('.shuriken-bulk-disable').on('click', function() {
            var $tab = $(this).closest('.shuriken-tab-content');
            $tab.find('.shuriken-row-checkbox:checked').each(function() {
                var $row = $(this).closest('tr');
                $row.attr('data-enabled', '0');
                $row.find('.shuriken-status-toggle').removeClass('enabled');
            });
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

            $('#f_name').val(name).prop('readonly', !isCustom);
            $('#f_type').val(type).prop('disabled', !isCustom);
            $('#f_label').val(label);
            $('#f_placeholder').val(placeholder);
            $('#f_required').prop('checked', required);
            $('#f_class').val(cssClass);
            $('#f_default').val(defaultVal);
            $('#f_email').prop('checked', showEmail);
            $('#f_order').prop('checked', showOrder);

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
            
            var validateArray = [];
            $('.f_validate:checked').each(function() {
                validateArray.push($(this).val());
            });
            var validateStr = validateArray.join(',');
            
            if (currentEditRow) {
                // Update existing row
                if(currentEditRow.attr('data-custom') === '1') {
                    currentEditRow.attr('data-name', name);
                    currentEditRow.attr('data-type', type);
                    currentEditRow.find('.col-name').html('<strong>'+name+'</strong>');
                    currentEditRow.find('.col-type').html('<span class="shuriken-badge type-badge">' + type + '</span><span class="shuriken-badge custom-badge">Custom</span>');
                }
                currentEditRow.attr('data-placeholder', placeholder);
                currentEditRow.attr('data-required', required);
                currentEditRow.attr('data-class', cssClass);
                currentEditRow.attr('data-default', defaultVal);
                currentEditRow.attr('data-validate', validateStr);
                currentEditRow.attr('data-email', showEmail);
                currentEditRow.attr('data-order', showOrder);
                currentEditRow.find('.col-label').text(label);
                currentEditRow.find('.col-required').html(reqHtml);
            } else {
                // Add new row to active tab
                var priority = 999; 
                var newRow = `
                    <tr data-name="${name}" data-type="${type}" data-required="${required}" data-enabled="1" data-custom="1" data-priority="${priority}" data-placeholder="${placeholder}" data-class="${cssClass}" data-default="${defaultVal}" data-validate="${validateStr}" data-email="${showEmail}" data-order="${showOrder}">
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
        });

        // Save Changes (AJAX)
        $('#shuriken-save-changes').on('click', function(e) {
            e.preventDefault();
            
            var data = {
                billing: [],
                shipping: [],
                additional: []
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
                        show_in_order: $row.attr('data-order') === '1'
                    });
                });
            }

            gatherFields('tab-billing', data.billing);
            gatherFields('tab-shipping', data.shipping);
            gatherFields('tab-additional', data.additional);

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

    });

})(jQuery);
