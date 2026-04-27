(function($) {
    'use strict';

    class ShurikenPopupCheckout {
        constructor() {
            this.$overlay = $('.shuriken-popup-checkout-overlay');
            this.$container = $('.shuriken-popup-checkout-container');
            this.$closeBtn = $('.shuriken-popup-close');
            this.$body = $('body');
            
            // Check if widget is present on page
            if (this.$container.length === 0) {
                return;
            }

            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Close button
            this.$closeBtn.on('click', (e) => {
                e.preventDefault();
                this.closePopup();
            });

            // Click outside to close
            this.$overlay.on('click', (e) => {
                if ($(e.target).hasClass('shuriken-popup-checkout-overlay')) {
                    this.closePopup();
                }
            });

            // Escape key
            $(document).on('keyup', (e) => {
                if (e.key === "Escape" && this.$container.hasClass('active')) {
                    this.closePopup();
                }
            });

            // Intercept checkout click in Mobile Bottom Menu
            $(document).on('click', '.shuriken-mbm-sidebar .checkout, .shuriken-mbm-drawer .checkout, .shuriken-mbm-ui-body .checkout', (e) => {
                if (this.$container.length > 0) {
                    e.preventDefault();
                    $('.shuriken-mbm-sidebar, .shuriken-mbm-drawer').removeClass('active is-active');
                    $('.shuriken-mbm-overlay').removeClass('active is-active');

                    if ( typeof shuriken_popup_obj !== 'undefined' && shuriken_popup_obj.force_login == "1" && !shuriken_popup_obj.is_user_logged_in ) {
                        window.shuriken_auth_from_checkout = true;
                        // Trigger My Account Profile Popup if available
                        var $profileTrigger = $('.shuriken-mbm-trigger-profile');
                        if ($profileTrigger.length) {
                            $profileTrigger.first().trigger('click');
                        } else {
                            // Fallback to loading Login Form inside checkout popup
                            this.loadAuthForm('login');
                        }
                    } else {
                        this.openPopup();
                    }
                }
            });

            // Flow Management: Intercept Place Order (also keep it here just in case they reach checkout directly)
            $(document).on('click', '#place_order', (e) => {
                if ( typeof shuriken_popup_obj !== 'undefined' && shuriken_popup_obj.force_login == "1" && !shuriken_popup_obj.is_user_logged_in ) {
                    e.preventDefault();
                    window.shuriken_auth_from_checkout = true;
                    var $profileTrigger = $('.shuriken-mbm-trigger-profile');
                    if ($profileTrigger.length) {
                        $('.shuriken-popup-close').trigger('click'); // close checkout
                        $profileTrigger.first().trigger('click');
                    } else {
                        this.loadAuthForm('login');
                    }
                }
            });

            // AJAX Lost Password Intercept
            $(document).on('click', 'a[href*="lost-password"]', (e) => {
                e.preventDefault();
                this.loadAuthForm('lost_password');
            });

            // Hashchange for Order Received
            $(window).on('hashchange', () => {
                this.checkOrderReceivedHash();
            });

            $(document.body).on('shuriken_auth_success_from_checkout', () => {
                this.needsCheckoutReload = true;
                this.openPopup();
            });

            // Handle Checkout Errors Scroll in Popup
            $(document.body).on('checkout_error', () => {
                if (this.$container.hasClass('active')) {
                    // Scroll the popup container to the top so the user can see the error
                    this.$container.animate({ scrollTop: 0 }, 400);
                    let $body = this.$container.find('.shuriken-popup-checkout-body');
                    if ($body.length) {
                        $body.animate({ scrollTop: 0 }, 400);
                    }
                }
            });

            // Check hash on load
            this.checkOrderReceivedHash();
        }

        checkOrderReceivedHash() {
            if (window.location.hash && window.location.hash.startsWith('#shuriken-order-received|')) {
                let parts = window.location.hash.split('|');
                if (parts.length === 3) {
                    let order_id = parts[1];
                    let order_key = parts[2];
                    this.loadOrderReceived(order_id, order_key);
                    // Remove hash from URL to prevent reload issues
                    history.replaceState(null, null, ' ');
                }
            }
        }

        loadOrderReceived(order_id, order_key) {
            this.$body.css('overflow', 'hidden');
            this.$overlay.addClass('active');
            this.$container.addClass('active loading');
            
            $.post(shuriken_popup_obj.ajax_url, {
                action: 'shuriken_get_order_received',
                order_id: order_id,
                order_key: order_key
            }, (response) => {
                this.$container.removeClass('loading');
                if (response.success && response.data.html) {
                    this.$container.find('.shuriken-popup-checkout-body').html(response.data.html);
                    // Hide the popup header title or change it
                    this.$container.find('.shuriken-popup-checkout-title').text('Order Received');
                } else {
                    this.$container.find('.shuriken-popup-checkout-body').html('<p>Error loading order details.</p>');
                }
            }).fail(() => {
                this.$container.removeClass('loading');
                this.$container.find('.shuriken-popup-checkout-body').html('<p>Error loading order details.</p>');
            });
        }

        loadAuthForm(type) {
            this.$body.css('overflow', 'hidden');
            this.$overlay.addClass('active');
            this.$container.addClass('active loading');
            
            let action = type === 'lost_password' ? 'shuriken_get_lost_password' : 'shuriken_get_account_content'; 
            
            $.post(shuriken_popup_obj.ajax_url, {
                action: action
            }, (response) => {
                this.$container.removeClass('loading');
                if (response.success && response.data) {
                    // response.data could be string (shuriken_get_account_content) or object with html (lost password)
                    let html = typeof response.data === 'string' ? response.data : response.data.html;
                    this.$container.find('.shuriken-popup-checkout-body').html(html);
                    let title = type === 'lost_password' ? 'Lost Password' : 'Login / Register';
                    this.$container.find('.shuriken-popup-checkout-title').text(title);
                } else {
                    this.$container.find('.shuriken-popup-checkout-body').html('<p>Error loading form.</p>');
                }
            });
        }

        openPopup() {
            // Check if checkout form actually exists or needs reload due to auth state change
            if (this.$container.find('form.checkout').length === 0 || this.needsCheckoutReload) {
                // Cart must have been empty or user just logged in. Reload the form.
                this.$container.find('.shuriken-popup-checkout-body').html('<div style="padding: 40px; text-align: center; font-family: sans-serif;">Loading secure checkout...</div>');
                setTimeout(() => {
                    this.$overlay.addClass('active');
                    this.$container.addClass('active');
                }, 10);
                
                $.get(window.location.href, (response) => {
                    this.needsCheckoutReload = false;
                    
                    // Extract fresh nonces to prevent 403 Forbidden
                    var checkoutMatch = response.match(/var\s+wc_checkout_params\s*=\s*({[^;]+});/);
                    if (checkoutMatch && checkoutMatch[1]) {
                        try {
                            var newParams = JSON.parse(checkoutMatch[1]);
                            if (typeof window.wc_checkout_params !== 'undefined') {
                                $.extend(window.wc_checkout_params, newParams);
                            } else {
                                window.wc_checkout_params = newParams;
                            }
                        } catch (e) {}
                    }

                    var $html = $(response);
                    var $newBody = $html.find('.shuriken-popup-checkout-body');
                    if ($newBody.length && $newBody.find('form.checkout').length > 0) {
                        this.$container.find('.shuriken-popup-checkout-body').html($newBody.html());
                        $(document.body).trigger('init_checkout');
                        $(document.body).trigger('wc_fragments_refreshed');
                        $(document.body).trigger('update_checkout');
                    } else {
                        window.location.reload();
                    }
                }).fail(() => {
                    window.location.reload();
                });
                return;
            }

            this.$body.css('overflow', 'hidden'); // Prevent background scrolling

            // Small delay for CSS transition
            setTimeout(() => {
                this.$overlay.addClass('active');
                this.$container.addClass('active');
            }, 10);

            // Trigger WooCommerce update checkout
            this.$container.addClass('loading');
            $(document.body).trigger('update_checkout');
            
            // Remove loading state after a short delay or when checkout updates
            $(document.body).on('updated_checkout', () => {
                this.$container.removeClass('loading');
            });
            
            // Fallback to remove loading
            setTimeout(() => {
                this.$container.removeClass('loading');
            }, 2000);
        }

        closePopup() {
            this.$overlay.removeClass('active');
            this.$container.removeClass('active');
            this.$body.css('overflow', '');
        }
    }

    // Initialize on document ready and Elementor frontend init
    $(document).ready(function() {
        new ShurikenPopupCheckout();
    });

})(jQuery);
