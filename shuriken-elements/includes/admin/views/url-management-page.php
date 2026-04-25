<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure settings exist
$auto_block = get_option( 'shuriken_url_blocks_auto', 'no' );
$manual_blocks = get_option( 'shuriken_url_blocks_manual', [] );
$action_404 = get_option( 'shuriken_url_blocks_404_action', 'default' );
$custom_url_404 = get_option( 'shuriken_url_blocks_404_custom_url', '' );

$pages = get_pages();
$posts = get_posts(['numberposts' => -1]); // Fetch all posts
?>
<div class="wrap shuriken-admin-wrap">
    
    <?php settings_errors('shuriken_elements_url_blocking_settings'); ?>

    <div class="shuriken-header">
        <h1><?php esc_html_e('URL Management', 'shuriken-elements'); ?></h1>
        <div>
            <!-- We will trigger the form submit when this button is clicked -->
            <button type="button" id="shuriken-save-url-settings-top" class="button button-primary shuriken-save-btn">
                <?php esc_html_e('Save Changes', 'shuriken-elements'); ?>
            </button>
        </div>
    </div>

    <form id="shuriken-url-management-form" method="post" action="options.php">
        <?php
        settings_fields( 'shuriken_elements_url_blocking_settings' );
        ?>

        <div class="shuriken-editor-layout" style="grid-template-columns: 1fr;">
            
            <div class="shuriken-editor-left-panel">
                
                <!-- Automatic Blocking Section -->
                <div class="shuriken-tab-content active" style="margin-bottom: 24px;">
                    <div class="shuriken-action-bar" style="background: #f9fafb;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #1f2937;"><?php esc_html_e('Automatic Blocking', 'shuriken-elements'); ?></h3>
                    </div>
                    
                    <div class="shuriken-fields-table-wrapper" style="padding: 24px; border-top: none;">
                        <p style="margin-top: 0; margin-bottom: 20px; color: #6b7280; font-size: 14px;">
                            <?php esc_html_e( 'If you use the Shuriken Ajax Cart or Popup Checkout, direct access to the default WooCommerce Cart and Checkout pages is confusing for users. Enable this to automatically block them.', 'shuriken-elements' ); ?>
                        </p>
                        
                        <!-- Improved Toggle UI -->
                        <div style="display: flex; align-items: center; justify-content: space-between; border: 1px solid #e5e7eb; padding: 16px; border-radius: 8px; background: #fff;">
                            <div>
                                <strong style="display: block; margin-bottom: 4px; color: #111827;"><?php esc_html_e('Block Default Cart/Checkout', 'shuriken-elements'); ?></strong>
                                <span style="color: #6b7280; font-size: 13px;"><?php esc_html_e('Automatically redirect /cart and /checkout to Homepage', 'shuriken-elements'); ?></span>
                            </div>
                            <label class="shuriken-toggle-switch" style="margin: 0; padding: 0;">
                                <!-- Hidden input to ensure 'no' is sent if unchecked -->
                                <input type="hidden" name="shuriken_url_blocks_auto" value="no">
                                <input type="checkbox" name="shuriken_url_blocks_auto" value="yes" <?php checked( $auto_block, 'yes' ); ?>>
                                <span class="shuriken-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Advanced Manual Blocking Section -->
                <div class="shuriken-tab-content active" style="margin-bottom: 24px;">
                    <div class="shuriken-action-bar" style="background: #f9fafb; display: block; padding: 16px 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #1f2937;"><?php esc_html_e('Manual Page/Post Blocking', 'shuriken-elements'); ?></h3>
                            
                            <!-- Bulk Action Bar -->
                            <div class="shuriken-bulk-actions">
                                <span><?php esc_html_e('Bulk Action:', 'shuriken-elements'); ?></span>
                                <select id="shuriken-bulk-action-select" style="min-width: 150px; height: 32px; padding: 0 10px; font-size: 13px;">
                                    <option value=""><?php esc_html_e('-- Select Action --', 'shuriken-elements'); ?></option>
                                    <option value="none"><?php esc_html_e('Unblock (Not Blocked)', 'shuriken-elements'); ?></option>
                                    <option value="home"><?php esc_html_e('Redirect to Homepage', 'shuriken-elements'); ?></option>
                                    <option value="404"><?php esc_html_e('Display 404 Error', 'shuriken-elements'); ?></option>
                                    <option value="custom"><?php esc_html_e('Redirect to Custom URL', 'shuriken-elements'); ?></option>
                                </select>
                                <button type="button" id="shuriken-apply-bulk" class="button"><?php esc_html_e('Apply Bulk Action', 'shuriken-elements'); ?></button>
                            </div>
                        </div>

                        <!-- Internal Tabs -->
                        <div class="shuriken-tabs" style="margin-bottom: 0;">
                            <button type="button" class="shuriken-tab-link manual-tab-link active" data-target="manual-tab-pages"><?php esc_html_e('Pages', 'shuriken-elements'); ?></button>
                            <button type="button" class="shuriken-tab-link manual-tab-link" data-target="manual-tab-posts"><?php esc_html_e('Posts', 'shuriken-elements'); ?></button>
                        </div>
                    </div>
                    
                    <div class="shuriken-fields-table-wrapper" style="border-top: none; border-radius: 0 0 8px 8px;">
                        
                        <!-- Pages Table -->
                        <div id="manual-tab-pages" class="manual-tab-content" style="display: block;">
                            <table class="shuriken-fields-table">
                                <thead>
                                    <tr>
                                        <th width="30" class="shuriken-checkbox-wrap"><input type="checkbox" class="shuriken-select-all-pages"></th>
                                        <th><?php esc_html_e('Page Title', 'shuriken-elements'); ?></th>
                                        <th width="250"><?php esc_html_e('Redirect Action', 'shuriken-elements'); ?></th>
                                        <th><?php esc_html_e('Custom URL', 'shuriken-elements'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $wc_cart_id = class_exists( 'WooCommerce' ) ? wc_get_page_id( 'cart' ) : 0;
                                    $wc_checkout_id = class_exists( 'WooCommerce' ) ? wc_get_page_id( 'checkout' ) : 0;

                                    foreach ( $pages as $page ) : 
                                        $is_auto_blocked = ( $auto_block === 'yes' && ( $page->ID == $wc_cart_id || $page->ID == $wc_checkout_id ) );
                                        $action = isset( $manual_blocks[$page->ID] ) ? $manual_blocks[$page->ID]['action'] : 'none';
                                        $url = isset( $manual_blocks[$page->ID] ) ? $manual_blocks[$page->ID]['url'] : '';
                                    ?>
                                    <tr style="<?php echo $is_auto_blocked ? 'background: #f3f4f6; opacity: 0.8;' : ''; ?>">
                                        <td class="shuriken-checkbox-wrap">
                                            <input type="checkbox" class="shuriken-row-checkbox shuriken-page-checkbox" <?php echo $is_auto_blocked ? 'disabled' : ''; ?>>
                                        </td>
                                        <td>
                                            <strong><?php echo esc_html( $page->post_title ); ?></strong>
                                            <?php if ( $is_auto_blocked ) : ?>
                                                <br><small style="color: #6b7280;"><?php esc_html_e('Managed by Automatic Blocking', 'shuriken-elements'); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ( $is_auto_blocked ) : ?>
                                                <select disabled style="width: 100%; background: #e5e7eb; color: #6b7280;">
                                                    <option><?php esc_html_e('Automatically Blocked (Home)', 'shuriken-elements'); ?></option>
                                                </select>
                                                <input type="hidden" name="shuriken_url_blocks_manual[<?php echo esc_attr( $page->ID ); ?>][action]" value="<?php echo esc_attr( $action ); ?>">
                                                <input type="hidden" name="shuriken_url_blocks_manual[<?php echo esc_attr( $page->ID ); ?>][url]" value="<?php echo esc_attr( $url ); ?>">
                                            <?php else : ?>
                                                <select name="shuriken_url_blocks_manual[<?php echo esc_attr( $page->ID ); ?>][action]" class="shuriken-action-select" style="width: 100%;">
                                                    <option value="none" <?php selected( $action, 'none' ); ?>><?php esc_html_e('Not Blocked', 'shuriken-elements'); ?></option>
                                                    <option value="home" <?php selected( $action, 'home' ); ?>><?php esc_html_e('Redirect to Homepage', 'shuriken-elements'); ?></option>
                                                    <option value="404" <?php selected( $action, '404' ); ?>><?php esc_html_e('Display 404 Error', 'shuriken-elements'); ?></option>
                                                    <option value="custom" <?php selected( $action, 'custom' ); ?>><?php esc_html_e('Redirect to Custom URL', 'shuriken-elements'); ?></option>
                                                </select>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ( ! $is_auto_blocked ) : ?>
                                                <input type="text" name="shuriken_url_blocks_manual[<?php echo esc_attr( $page->ID ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" class="shuriken-custom-url-input" style="width: 100%; <?php echo $action === 'custom' ? '' : 'display:none;'; ?>" placeholder="https://...">
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Posts Table -->
                        <div id="manual-tab-posts" class="manual-tab-content" style="display: none;">
                            <table class="shuriken-fields-table">
                                <thead>
                                    <tr>
                                        <th width="30" class="shuriken-checkbox-wrap"><input type="checkbox" class="shuriken-select-all-posts"></th>
                                        <th><?php esc_html_e('Post Title', 'shuriken-elements'); ?></th>
                                        <th width="200"><?php esc_html_e('Redirect Action', 'shuriken-elements'); ?></th>
                                        <th><?php esc_html_e('Custom URL', 'shuriken-elements'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $posts as $post ) : 
                                        $action = isset( $manual_blocks[$post->ID] ) ? $manual_blocks[$post->ID]['action'] : 'none';
                                        $url = isset( $manual_blocks[$post->ID] ) ? $manual_blocks[$post->ID]['url'] : '';
                                    ?>
                                    <tr>
                                        <td class="shuriken-checkbox-wrap"><input type="checkbox" class="shuriken-row-checkbox shuriken-post-checkbox"></td>
                                        <td><strong><?php echo esc_html( $post->post_title ); ?></strong></td>
                                        <td>
                                            <select name="shuriken_url_blocks_manual[<?php echo esc_attr( $post->ID ); ?>][action]" class="shuriken-action-select" style="width: 100%;">
                                                <option value="none" <?php selected( $action, 'none' ); ?>><?php esc_html_e('Not Blocked', 'shuriken-elements'); ?></option>
                                                <option value="home" <?php selected( $action, 'home' ); ?>><?php esc_html_e('Redirect to Homepage', 'shuriken-elements'); ?></option>
                                                <option value="404" <?php selected( $action, '404' ); ?>><?php esc_html_e('Display 404 Error', 'shuriken-elements'); ?></option>
                                                <option value="custom" <?php selected( $action, 'custom' ); ?>><?php esc_html_e('Redirect to Custom URL', 'shuriken-elements'); ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="shuriken_url_blocks_manual[<?php echo esc_attr( $post->ID ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" class="shuriken-custom-url-input" style="width: 100%; <?php echo $action === 'custom' ? '' : 'display:none;'; ?>" placeholder="https://...">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- 404 Management Section -->
                <div class="shuriken-tab-content active">
                    <div class="shuriken-action-bar" style="background: #f9fafb;">
                        <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #1f2937;"><?php esc_html_e('404 / Misspelled URL Management', 'shuriken-elements'); ?></h3>
                    </div>
                    
                    <div class="shuriken-fields-table-wrapper" style="padding: 24px; border-top: none;">
                        <div class="shuriken-grid-2">
                            
                            <div class="shuriken-form-row">
                                <label><?php esc_html_e('404 Error Action', 'shuriken-elements'); ?></label>
                                <select name="shuriken_url_blocks_404_action" id="shuriken_404_action">
                                    <option value="default" <?php selected( $action_404, 'default' ); ?>><?php esc_html_e( 'Show Default Theme 404 Page', 'shuriken-elements' ); ?></option>
                                    <option value="home" <?php selected( $action_404, 'home' ); ?>><?php esc_html_e( 'Redirect to Homepage', 'shuriken-elements' ); ?></option>
                                    <option value="custom" <?php selected( $action_404, 'custom' ); ?>><?php esc_html_e( 'Redirect to Custom URL', 'shuriken-elements' ); ?></option>
                                </select>
                            </div>

                            <div class="shuriken-form-row" id="shuriken_404_custom_url_row" style="<?php echo $action_404 === 'custom' ? 'display:block;' : 'display:none;'; ?>">
                                <label><?php esc_html_e('Custom Redirect URL', 'shuriken-elements'); ?></label>
                                <input type="text" name="shuriken_url_blocks_404_custom_url" value="<?php echo esc_attr( $custom_url_404 ); ?>" placeholder="https://..." />
                                <small><?php esc_html_e('Enter the full URL where all 404 traffic should be redirected.', 'shuriken-elements'); ?></small>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <!-- Sticky Save Bar -->
    <div id="shuriken-sticky-save-bar" style="display: none; position: fixed; bottom: 0; left: <?php echo is_rtl() ? '0' : '160px'; ?>; right: <?php echo is_rtl() ? '160px' : '0'; ?>; background: #fff; border-top: 1px solid #e5e7eb; padding: 15px 24px; box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05); z-index: 999; justify-content: space-between; align-items: center; transition: all 0.3s ease;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="dashicons dashicons-warning" style="color: #f59e0b;"></span>
            <span style="font-weight: 500; color: #374151; font-size: 14px;"><?php esc_html_e('You have unsaved changes.', 'shuriken-elements'); ?></span>
        </div>
        <div>
            <button type="button" class="button shuriken-save-btn button-primary" style="padding: 0 24px; height: 36px; font-size: 14px; font-weight: 500; border-radius: 6px;">
                <?php esc_html_e('Save All Changes', 'shuriken-elements'); ?>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let formChanged = false;
            const stickyBar = document.getElementById('shuriken-sticky-save-bar');
            
            // Adjust left position based on WP admin menu state
            function updateStickyBarPosition() {
                const adminMenu = document.getElementById('adminmenuwrap');
                if (adminMenu) {
                    const menuWidth = adminMenu.offsetWidth;
                    if (document.body.classList.contains('rtl')) {
                        stickyBar.style.right = menuWidth + 'px';
                    } else {
                        stickyBar.style.left = menuWidth + 'px';
                    }
                }
            }
            
            window.addEventListener('resize', updateStickyBarPosition);
            updateStickyBarPosition();

            function markAsChanged() {
                formChanged = true;
                stickyBar.style.display = 'flex';
            }

            // Detect any changes in the form
            const formInputs = document.querySelectorAll('#shuriken-url-management-form input, #shuriken-url-management-form select');
            formInputs.forEach(input => {
                input.addEventListener('change', markAsChanged);
                input.addEventListener('input', markAsChanged);
            });

            // Main Form Submission
            document.querySelectorAll('.shuriken-save-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = document.getElementById('shuriken-url-management-form');
                    
                    document.querySelectorAll('.shuriken-save-btn').forEach(b => {
                        b.innerText = '<?php esc_attr_e("Saving...", "shuriken-elements"); ?>';
                        b.disabled = true;
                    });

                    // Submit normally to options.php instead of using fetch to avoid 302 redirect issues or security plugin blocks
                    form.submit();
                });
            });

            // 404 Custom URL field visibility toggle
            const action404Select = document.getElementById('shuriken_404_action');
            const customUrlRow = document.getElementById('shuriken_404_custom_url_row');
            
            if(action404Select) {
                action404Select.addEventListener('change', function() {
                    customUrlRow.style.display = (this.value === 'custom') ? 'block' : 'none';
                });
            }

            // Per-row Custom URL field visibility toggle
            document.querySelectorAll('.shuriken-action-select').forEach(function(select) {
                select.addEventListener('change', function() {
                    const row = this.closest('tr');
                    const urlInput = row.querySelector('.shuriken-custom-url-input');
                    if (urlInput) {
                        urlInput.style.display = (this.value === 'custom') ? 'block' : 'none';
                    }
                });
            });

            // Tabs Switching (Pages/Posts)
            document.querySelectorAll('.manual-tab-link').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.manual-tab-link').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.manual-tab-content').forEach(c => c.style.display = 'none');
                    
                    this.classList.add('active');
                    const targetId = this.getAttribute('data-target');
                    document.getElementById(targetId).style.display = 'block';
                });
            });

            // Select All Pages
            const selectAllPages = document.querySelector('.shuriken-select-all-pages');
            if (selectAllPages) {
                selectAllPages.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('#manual-tab-pages .shuriken-row-checkbox');
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });
            }

            // Select All Posts
            const selectAllPosts = document.querySelector('.shuriken-select-all-posts');
            if (selectAllPosts) {
                selectAllPosts.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('#manual-tab-posts .shuriken-row-checkbox');
                    checkboxes.forEach(cb => cb.checked = this.checked);
                });
            }

            // Bulk Action Apply
            document.getElementById('shuriken-apply-bulk').addEventListener('click', function() {
                const bulkAction = document.getElementById('shuriken-bulk-action-select').value;
                if (!bulkAction) {
                    alert('<?php esc_attr_e("Please select an action first.", "shuriken-elements"); ?>');
                    return;
                }

                // Get only checked rows in the CURRENTLY VISIBLE tab
                const activeTab = document.querySelector('.manual-tab-content[style*="display: block"]');
                if (!activeTab) return;

                const checkedBoxes = activeTab.querySelectorAll('.shuriken-row-checkbox:checked');
                
                if (checkedBoxes.length === 0) {
                    alert('<?php esc_attr_e("Please select at least one item.", "shuriken-elements"); ?>');
                    return;
                }

                checkedBoxes.forEach(function(checkbox) {
                    const row = checkbox.closest('tr');
                    const select = row.querySelector('.shuriken-action-select');
                    const urlInput = row.querySelector('.shuriken-custom-url-input');
                    
                    if (select) {
                        select.value = bulkAction;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        
                        if (urlInput) {
                            urlInput.style.display = (bulkAction === 'custom') ? 'block' : 'none';
                        }
                        
                        // Highlight row to show it was changed
                        row.style.transition = 'background-color 0.3s ease';
                        row.style.backgroundColor = '#e0f2fe'; // light blue
                        setTimeout(() => {
                            row.style.backgroundColor = '';
                        }, 1500);
                    }
                });

                markAsChanged();

                // Uncheck bulk master box
                const selectAll = activeTab.querySelector('th input[type="checkbox"]');
                if (selectAll) selectAll.checked = false;
                checkedBoxes.forEach(cb => cb.checked = false);
            });
        });
    </script>
</div>
