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
            // The class for the mobile bottom menu sidebar/drawer is typically .shuriken-mbm-sidebar or .shuriken-mbm-drawer
            // The checkout button inside it has classes .button.checkout
            $(document).on('click', '.shuriken-mbm-sidebar .checkout, .shuriken-mbm-drawer .checkout, .shuriken-mbm-ui-body .checkout', (e) => {
                // If popup checkout widget is available on the page, prevent redirect and open popup
                if (this.$container.length > 0) {
                    e.preventDefault();
                    
                    // Close the Mobile Bottom Menu UI if open
                    $('.shuriken-mbm-sidebar, .shuriken-mbm-drawer').removeClass('active');
                    $('.shuriken-mbm-overlay').removeClass('active');
                    
                    // Open Popup
                    this.openPopup();
                }
            });
        }

        openPopup() {
            // Check if checkout form actually exists (WooCommerce hides it if cart was empty on initial load)
            if (this.$container.find('form.checkout').length === 0) {
                // Cart must have been empty. Reload the page to properly initialize WooCommerce checkout scripts and form.
                this.$container.find('.shuriken-popup-checkout-body').html('<div style="padding: 40px; text-align: center; font-family: sans-serif;">Loading secure checkout...</div>');
                setTimeout(() => {
                    this.$overlay.addClass('active');
                    this.$container.addClass('active');
                }, 10);
                
                $.get(window.location.href, (response) => {
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
