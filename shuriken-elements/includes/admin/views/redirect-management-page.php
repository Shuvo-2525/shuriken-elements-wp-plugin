<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap shuriken-admin-wrap">
    <div class="shuriken-header">
        <h1><?php esc_html_e('Redirect Management', 'shuriken-elements'); ?></h1>
        <div>
            <button type="button" id="shuriken-refresh-redirects" class="button">
                <?php esc_html_e('Refresh Stats', 'shuriken-elements'); ?>
            </button>
        </div>
    </div>

    <div class="shuriken-editor-layout" style="grid-template-columns: 1fr;">
        <div class="shuriken-editor-left-panel">
            
            <!-- Add New Redirect Section -->
            <div class="shuriken-tab-content active" style="margin-bottom: 24px;">
                <div class="shuriken-action-bar" style="background: #f9fafb;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #1f2937;"><?php esc_html_e('Add New Redirect', 'shuriken-elements'); ?></h3>
                </div>
                <div class="shuriken-fields-table-wrapper" style="padding: 24px; border-top: none;">
                    <form id="shuriken-add-redirect-form" style="display: flex; gap: 16px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php esc_html_e('Custom Slug', 'shuriken-elements'); ?></label>
                            <input type="text" id="shuriken-new-slug" placeholder="e.g. promo-2026" style="width: 100%;" required>
                        </div>
                        <div style="flex: 2;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600;"><?php esc_html_e('Target URL', 'shuriken-elements'); ?></label>
                            <input type="url" id="shuriken-new-target" placeholder="https://..." style="width: 100%;" required>
                        </div>
                        <div>
                            <button type="submit" class="button button-primary" style="height: 32px;"><?php esc_html_e('Add Redirect', 'shuriken-elements'); ?></button>
                        </div>
                    </form>
                    <div id="shuriken-add-redirect-message" style="margin-top: 16px; display: none;"></div>
                </div>
            </div>

            <!-- Existing Redirects Section -->
            <div class="shuriken-tab-content active">
                <div class="shuriken-action-bar" style="background: #f9fafb;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #1f2937;"><?php esc_html_e('All Redirects', 'shuriken-elements'); ?></h3>
                </div>
                <div class="shuriken-fields-table-wrapper" style="border-top: none;">
                    <table class="shuriken-fields-table" id="shuriken-redirects-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Slug', 'shuriken-elements'); ?></th>
                                <th><?php esc_html_e('Target URL', 'shuriken-elements'); ?></th>
                                <th width="100"><?php esc_html_e('Clicks', 'shuriken-elements'); ?></th>
                                <th width="150"><?php esc_html_e('Actions', 'shuriken-elements'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px;"><?php esc_html_e('Loading...', 'shuriken-elements'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ajaxUrl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
    const nonce = '<?php echo wp_create_nonce( 'shuriken_redirect_nonce' ); ?>';
    const tableBody = document.querySelector('#shuriken-redirects-table tbody');
    const msgBox = document.getElementById('shuriken-add-redirect-message');

    function showMessage(msg, isError = false) {
        msgBox.innerHTML = msg;
        msgBox.style.color = isError ? '#dc2626' : '#16a34a';
        msgBox.style.display = 'block';
        setTimeout(() => msgBox.style.display = 'none', 3000);
    }

    function loadRedirects() {
        fetch(ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'shuriken_get_redirects',
                nonce: nonce
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderTable(data.data.redirects);
            } else {
                tableBody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:red;">Error loading data.</td></tr>`;
            }
        });
    }

    function renderTable(redirects) {
        if (redirects.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding: 20px; color: #6b7280;">No redirects found.</td></tr>`;
            return;
        }

        const siteUrl = '<?php echo home_url('/'); ?>';
        
        tableBody.innerHTML = '';
        redirects.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <strong>${r.slug}</strong>
                    <br><small><a href="${siteUrl}${r.slug}" target="_blank" style="color: #3b82f6;">${siteUrl}${r.slug}</a></small>
                </td>
                <td><a href="${r.target}" target="_blank" style="color: #6b7280; text-decoration: none;">${r.target}</a></td>
                <td><strong style="font-size: 16px;">${r.clicks}</strong></td>
                <td>
                    <button type="button" class="button shuriken-delete-redirect" data-id="${r.id}" style="color: #dc2626; border-color: #dc2626;"><?php esc_html_e('Delete', 'shuriken-elements'); ?></button>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        // Attach delete events
        document.querySelectorAll('.shuriken-delete-redirect').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('<?php esc_html_e('Are you sure you want to delete this redirect?', 'shuriken-elements'); ?>')) {
                    deleteRedirect(this.getAttribute('data-id'));
                }
            });
        });
    }

    function deleteRedirect(id) {
        fetch(ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'shuriken_delete_redirect',
                nonce: nonce,
                id: id
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadRedirects();
            } else {
                alert(data.data.message || 'Error deleting redirect.');
            }
        });
    }

    document.getElementById('shuriken-add-redirect-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const slugInput = document.getElementById('shuriken-new-slug');
        const targetInput = document.getElementById('shuriken-new-target');

        fetch(ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'shuriken_add_redirect',
                nonce: nonce,
                slug: slugInput.value,
                target: targetInput.value
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage(data.data.message);
                slugInput.value = '';
                targetInput.value = '';
                loadRedirects();
            } else {
                showMessage(data.data.message || 'Error adding redirect.', true);
            }
        });
    });

    document.getElementById('shuriken-refresh-redirects').addEventListener('click', loadRedirects);

    // Initial load
    loadRedirects();
});
</script>
