(function($) {
    'use strict';

    var WidgetMobileBottomMenuHandler = function($scope, $) {
        var $sidebarTrigger = $scope.find('.shuriken-mbm-trigger-sidebar');
        var $drawerTrigger  = $scope.find('.shuriken-mbm-trigger-drawer');
        var $sidebar        = $scope.find('.shuriken-mbm-sidebar');
        var $drawer         = $scope.find('.shuriken-mbm-drawer');
        var $overlay        = $scope.find('.shuriken-mbm-overlay');
        var $closeBtns      = $scope.find('.shuriken-mbm-close-ui');

        function openUI($element) {
            $overlay.addClass('is-active');
            $element.addClass('is-active');
            $('body').css('overflow', 'hidden'); // prevent bg scroll
        }

        function closeUI() {
            $overlay.removeClass('is-active');
            if ($sidebar.length) $sidebar.removeClass('is-active');
            if ($drawer.length) $drawer.removeClass('is-active');
            
            var $profileContainer = $scope.find('.shuriken-mbm-profile-drawer, .shuriken-mbm-profile-popup-container');
            if ($profileContainer.length) $profileContainer.removeClass('is-active');
            var $profileOverlay = $scope.find('.shuriken-mbm-profile-overlay');
            if ($profileOverlay.length) {
                $profileOverlay.removeClass('is-active');
                window.shuriken_auth_from_checkout = false;
            }

            $('body').css('overflow', '');
            
            // Re-bind fragments refresh to update if cart changed aggressively while open
            if( typeof wc_add_to_cart_fragments_params !== 'undefined' ) {
                $(document.body).trigger('wc_fragment_refresh');
            }
        }

        // Add padding to body equivalent to menu height so it doesn't overlap footer content
        function adjustBodyPadding() {
            var $wrapper = $scope.find('.shuriken-mobile-bottom-menu-wrapper');
            if ($wrapper.length && $wrapper.is(':visible')) {
                var menuHeight = $wrapper.outerHeight();
                $('body').css('padding-bottom', menuHeight + 'px');
            } else {
                $('body').css('padding-bottom', '');
            }
        }
        $(window).on('resize', adjustBodyPadding);
        setTimeout(adjustBodyPadding, 100);

        if ( $sidebarTrigger.length && $sidebar.length ) {
            $sidebarTrigger.on('click', function(e) {
                e.preventDefault();
                openUI($sidebar);
            });
        }

        if ( $drawerTrigger.length && $drawer.length ) {
            $drawerTrigger.on('click', function(e) {
                e.preventDefault();
                openUI($drawer);
            });
        }

        // Search Implementation
        var $searchTriggers = $scope.find('.shuriken-mbm-trigger-search');
        var $searchOverlay = $scope.find('.shuriken-mbm-search-overlay');
        var $closeSearch = $scope.find('.shuriken-mbm-close-search');
        var $searchInput = $scope.find('.shuriken-mbm-search-input');
        var $searchResults = $scope.find('.shuriken-mbm-search-results');
        
        var searchTimeout = null;
        var ajaxUrl = (typeof shuriken_obj !== 'undefined') ? shuriken_obj.ajax_url : '/wp-admin/admin-ajax.php';
        var nonce   = (typeof shuriken_obj !== 'undefined') ? shuriken_obj.nonce : '';

        if ( $searchTriggers.length && $searchOverlay.length ) {
            $searchTriggers.on('click', function(e) {
                e.preventDefault();
                var mode = $(this).data('search-type');
                
                $searchOverlay.fadeIn(300);
                setTimeout(function(){ $searchInput.trigger('focus'); }, 100);
                $('body').css('overflow', 'hidden');

                if ( mode === 'search_redirect' ) {
                    $searchResults.hide();
                    $searchInput.off('keyup.ajaxSearch');
                } else if ( mode === 'search_ajax' ) {
                    $searchResults.show();
                    $searchInput.on('keyup.ajaxSearch', handleAjaxSearch);
                }
            });
        }

        if ( $closeSearch.length ) {
            $closeSearch.on('click', function() {
                $searchOverlay.fadeOut(300);
                $('body').css('overflow', '');
                $searchInput.val('');
                $searchResults.html('');
            });
        }

        function handleAjaxSearch() {
            var query = $(this).val();
            var queryType = $searchOverlay.data('query-type');
            
            clearTimeout(searchTimeout);

            if ( query.length < 2 ) {
                $searchResults.html('');
                return;
            }

            searchTimeout = setTimeout(function() {
                $searchResults.html('<div class="shuriken-mbm-search-loading">Searching...</div>');
                
                $.post(ajaxUrl, {
                    action: 'shuriken_ajax_search',
                    query: query,
                    query_type: queryType,
                    security: nonce
                }, function(response) {
                    if ( response.success ) {
                        var html = '';
                        if ( response.data.length > 0 ) {
                            $.each(response.data, function(index, item) {
                                html += '<div class="shuriken-search-result-item">';
                                html += '<a href="' + item.url + '">';
                                if ( item.thumbnail ) {
                                    html += '<img src="' + item.thumbnail + '" alt="' + item.title + '" />';
                                } else {
                                    html += '<div class="shuriken-search-no-thumb"></div>';
                                }
                                html += '<div class="shuriken-search-item-info">';
                                html += '<h4>' + item.title + '</h4>';
                                if ( item.price_html ) {
                                    html += '<div class="price">' + item.price_html + '</div>';
                                }
                                html += '</div>';
                                html += '</a>';
                                html += '</div>';
                            });
                        } else {
                            html = '<div class="shuriken-mbm-search-no-results">No results found.</div>';
                        }
                        $searchResults.html(html);
                    }
                });
            }, 500); // 500ms debounce
        }

        $closeBtns.on('click', closeUI);
        $overlay.on('click', closeUI);

        // Optional: Update badge animation purely visual on added_to_cart
        $(document.body).on('added_to_cart', function() {
            var $badge = $scope.find('.shuriken-mbm-cart-badge');
            $badge.css('transform', 'scale(1.3)');
            setTimeout(function() {
                $badge.css('transform', 'scale(1)');
            }, 300);
        });

        // AJAX Quantity Update
        $(document.body).on('click', '.shuriken-mbm-qty-btn', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $container = $btn.closest('.shuriken-mbm-qty-container');
            var cartItemKey = $container.data('cart-item-key');
            var currentQty = parseInt($container.find('.shuriken-mbm-qty-val').text());
            var newQty = $btn.hasClass('plus') ? currentQty + 1 : currentQty - 1;

            if (newQty < 0) return;

            console.log('Shuriken Elements: Updating quantity...', { key: cartItemKey, newQty: newQty });

            $container.css('opacity', '0.5').css('pointer-events', 'none');

            // Use localized ajax_url or fallback
            var ajaxUrl = (typeof shuriken_obj !== 'undefined') ? shuriken_obj.ajax_url : ((typeof wc_add_to_cart_params !== 'undefined') ? wc_add_to_cart_params.ajax_url : '/wp-admin/admin-ajax.php');
            var nonce   = (typeof shuriken_obj !== 'undefined') ? shuriken_obj.nonce : '';

            $.post(ajaxUrl, {
                action: 'shuriken_update_cart_item_qty',
                cart_item_key: cartItemKey,
                new_qty: newQty,
                security: nonce
            }, function(response) {
                if (response && response.fragments) {
                    console.log('Shuriken Elements: Quantity updated successfully.');
                    
                    var fragments = response.fragments;
                    
                    // Replace fragments manually for instant UI update
                    $.each(fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });
                    
                    // Update LocalStorage Hash just like WC core does
                    if ( typeof wc_cart_fragments_params !== 'undefined' ) {
                        var cart_hash_key = wc_cart_fragments_params.cart_hash_key;
                        sessionStorage.setItem( wc_cart_fragments_params.fragment_name, JSON.stringify( fragments ) );
                        if ( response.cart_hash ) {
                            sessionStorage.setItem( cart_hash_key, response.cart_hash );
                        }
                    }

                    $(document.body).trigger('wc_fragments_refreshed');

                } else {
                    console.error('Shuriken Elements: Cart update failed.', response);
                    $container.css('opacity', '1').css('pointer-events', 'auto');
                }
            }).fail(function() {
                $container.css('opacity', '1').css('pointer-events', 'auto');
            });
        });

        // AJAX Remove Item (Robust Fix)
        $(document.body).on('click', '.shuriken-mbm-ui-body .woocommerce-mini-cart-item .remove', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation(); // Prevent WC core from doing its buggy hide

            var $btn = $(this);
            var cartItemKey = $btn.data('cart_item_key');
            
            // Sometimes it's inside a data attribute or we can extract it from href
            if (!cartItemKey) {
                var href = $btn.attr('href');
                var match = href ? href.match(/remove_item=([^&]+)/) : null;
                if (match) {
                    cartItemKey = match[1];
                }
            }

            if (!cartItemKey) return;

            var $container = $btn.closest('.woocommerce-mini-cart-item');
            $container.css('opacity', '0.5').css('pointer-events', 'none');

            var ajaxUrl = (typeof shuriken_obj !== 'undefined') ? shuriken_obj.ajax_url : ((typeof wc_add_to_cart_params !== 'undefined') ? wc_add_to_cart_params.ajax_url : '/wp-admin/admin-ajax.php');
            var nonce   = (typeof shuriken_obj !== 'undefined') ? shuriken_obj.nonce : '';

            $.post(ajaxUrl, {
                action: 'shuriken_update_cart_item_qty',
                cart_item_key: cartItemKey,
                new_qty: 0, // 0 removes the item
                security: nonce
            }, function(response) {
                if (response && response.fragments) {
                    var fragments = response.fragments;
                    $.each(fragments, function(key, value) {
                        $(key).replaceWith(value);
                    });
                    if ( typeof wc_cart_fragments_params !== 'undefined' ) {
                        var cart_hash_key = wc_cart_fragments_params.cart_hash_key;
                        sessionStorage.setItem( wc_cart_fragments_params.fragment_name, JSON.stringify( fragments ) );
                        if ( response.cart_hash ) {
                            sessionStorage.setItem( cart_hash_key, response.cart_hash );
                        }
                    }
                    $(document.body).trigger('wc_fragments_refreshed');
                } else {
                    $container.css('opacity', '1').css('pointer-events', 'auto');
                }
            }).fail(function() {
                $container.css('opacity', '1').css('pointer-events', 'auto');
            });
        });

        // Dynamic Text Replacement for Cart Buttons
        function updateCartButtonTexts() {
            var $sidebar = $scope.find('.shuriken-mbm-sidebar');
            var $drawer  = $scope.find('.shuriken-mbm-drawer');
            var $target  = $sidebar.length ? $sidebar : $drawer;

            if (!$target.length) return;

            var viewCartText = $target.data('view-cart-text');
            var checkoutText = $target.data('checkout-text');

            if (viewCartText) {
                $target.find('.woocommerce-mini-cart__buttons .button:not(.checkout)').text(viewCartText);
            }
            if (checkoutText) {
                $target.find('.woocommerce-mini-cart__buttons .button.checkout').text(checkoutText);
            }
        }

        // Profile Implementation
        var $profileTrigger = $scope.find('.shuriken-mbm-trigger-profile');
        var $profileContainer = $scope.find('.shuriken-mbm-profile-drawer, .shuriken-mbm-profile-popup-container');
        var $profileOverlay = $scope.find('.shuriken-mbm-profile-overlay');
        var $profileContent = $scope.find('.shuriken-mbm-profile-content');

        if ( $profileTrigger.length && $profileContainer.length ) {
            $profileTrigger.on('click', function(e) {
                e.preventDefault();
                openUI($profileContainer);
                $profileOverlay.addClass('is-active');
                
                // Load content if not already loaded or if we want to refresh
                loadProfileContent();
            });
        }

        $profileOverlay.on('click', function() {
            closeUI();
        });

        function loadProfileContent() {
            $profileContent.html('<div class="shuriken-mbm-loading"><div class="shuriken-mbm-loader"></div></div>');

            $.post(ajaxUrl, {
                action: 'shuriken_get_account_content'
            }, function(response) {
                if ( response.success ) {
                    $profileContent.html(response.data);
                    initProfileForms();
                } else {
                    $profileContent.html('<div class="shuriken-mbm-error">Failed to load content.</div>');
                }
            });
        }

        function initProfileForms() {
            // Toggle to Register
            $profileContent.find('.shuriken-toggle-register').on('click', function(e) {
                e.preventDefault();
                $profileContent.find('#shuriken-login-form').hide().removeClass('active');
                $profileContent.find('#shuriken-register-form').fadeIn(300).addClass('active');
            });

            // Toggle to Login
            $profileContent.find('.shuriken-toggle-login').on('click', function(e) {
                e.preventDefault();
                $profileContent.find('#shuriken-register-form').hide().removeClass('active');
                $profileContent.find('#shuriken-login-form').fadeIn(300).addClass('active');
            });

            // WooCommerce Endpoint AJAX
            $profileContent.on('click', 'a', function(e) {
                var url = $(this).attr('href');
                if (!url || url === '#' || url.indexOf('javascript:') === 0) return;

                var urlObj;
                try {
                    urlObj = new URL(url, window.location.origin);
                } catch(err) { return; }
                
                if (urlObj.hostname !== window.location.hostname) return;

                var $li = $(this).closest('li');
                if ($li.hasClass('woocommerce-MyAccount-navigation-link--dashboard')) {
                    return; 
                }

                // Handle Logout
                if ($li.hasClass('woocommerce-MyAccount-navigation-link--customer-logout') || urlObj.pathname.indexOf('customer-logout') !== -1) {
                    e.preventDefault();
                    $profileContent.html('<div class="shuriken-mbm-loading"><div class="shuriken-mbm-loader"></div></div>');
                    $.post(ajaxUrl, {
                        action: 'shuriken_ajax_logout'
                    }, function() {
                        closeUI(); // Close the popup without reloading the page
                        loadProfileContent(); // reload content silently in the background
                    }).fail(function() {
                        window.location.href = url; // Fallback
                    });
                    return;
                }

                var pathSegments = urlObj.pathname.replace(/^\/|\/$/g, '').split('/');
                var knownEndpoints = ['orders', 'downloads', 'edit-address', 'payment-methods', 'edit-account', 'view-order'];
                var endpoint = '';
                var value = '';

                for (var i = 0; i < pathSegments.length; i++) {
                    if (knownEndpoints.indexOf(pathSegments[i]) !== -1) {
                        endpoint = pathSegments[i];
                        if (i + 1 < pathSegments.length) {
                            value = pathSegments[i+1];
                        }
                        break;
                    }
                }

                if (!endpoint) {
                    return; // Let standard links behave normally
                }

                e.preventDefault();

                $profileContent.html('<div class="shuriken-mbm-loading"><div class="shuriken-mbm-loader"></div></div>');

                $.post(ajaxUrl, {
                    action: 'shuriken_get_wc_endpoint',
                    endpoint: endpoint,
                    value: value
                }, function(response) {
                    if ( response.success ) {
                        var backBtn = '<button class="shuriken-mbm-back-btn" style="background: none; border: none; color: var(--shuriken-mbm-item-active-color, #0073aa); font-weight: 600; cursor: pointer; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-size: 15px;"><i class="fas fa-arrow-left"></i> Back to Menu</button>';
                        $profileContent.html(backBtn + response.data);
                        
                        $profileContent.find('.shuriken-mbm-back-btn').on('click', function() {
                            loadProfileContent();
                        });
                        
                        // Re-initialize forms if there are nested links
                        initProfileForms();
                    } else {
                        window.location.href = url;
                    }
                }).fail(function() {
                    window.location.href = url;
                });
            });

            // Login AJAX
            $profileContent.find('#shuriken-login-form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $msg = $form.find('.shuriken-form-message');
                var $btn = $form.find('button[type="submit"]');

                $msg.html('').removeClass('error success');
                $btn.prop('disabled', true).addClass('loading');

                $.post(ajaxUrl, {
                    action: 'shuriken_ajax_login',
                    username: $form.find('input[name="username"]').val(),
                    password: $form.find('input[name="password"]').val(),
                    rememberme: $form.find('input[name="rememberme"]').is(':checked'),
                    security: $form.find('#shuriken-login-nonce').val()
                }, function(response) {
                    if ( response.success ) {
                        var msgText = response.data.message ? response.data.message : response.data;
                        if (response.data.new_nonce) {
                            nonce = response.data.new_nonce;
                        }
                        if (typeof shuriken_popup_obj !== 'undefined') {
                            shuriken_popup_obj.is_user_logged_in = true;
                        }
                        $msg.addClass('success').html(msgText);
                        
                        if (window.shuriken_auth_from_checkout) {
                            window.shuriken_auth_from_checkout = false;
                            setTimeout(function() {
                                $('.shuriken-mbm-profile-overlay, .shuriken-mbm-profile-drawer, .shuriken-mbm-profile-popup-container').removeClass('is-active');
                                $('body').css('overflow', '');
                                $(document.body).trigger('shuriken_auth_success_from_checkout');
                            }, 1000);
                        } else {
                            // Reload content to show dashboard
                            setTimeout(loadProfileContent, 1000);
                        }
                    } else {
                        $msg.addClass('error').html(response.data);
                        $btn.prop('disabled', false).removeClass('loading');
                    }
                });
            });

            // Register AJAX
            $profileContent.find('#shuriken-register-form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $msg = $form.find('.shuriken-form-message');
                var $btn = $form.find('button[type="submit"]');
                
                var password = $form.find('input[name="password"]').val();
                var confirmPassword = $form.find('input[name="password_confirm"]').val();

                $msg.html('').removeClass('error success');
                
                if (password !== confirmPassword) {
                    $msg.addClass('error').html((typeof shuriken_obj !== 'undefined' && shuriken_obj.i18n_password_mismatch) ? shuriken_obj.i18n_password_mismatch : 'Passwords do not match.');
                    return;
                }

                $btn.prop('disabled', true).addClass('loading');

                var formData = $form.serializeArray();
                formData.push({ name: 'action', value: 'shuriken_ajax_register' });
                formData.push({ name: 'security', value: $form.find('#shuriken-register-nonce').val() });

                $.post(ajaxUrl, $.param(formData), function(response) {
                    if ( response.success ) {
                        var msgText = response.data.message ? response.data.message : response.data;
                        if (response.data.new_nonce) {
                            nonce = response.data.new_nonce;
                        }
                        if (typeof shuriken_popup_obj !== 'undefined') {
                            shuriken_popup_obj.is_user_logged_in = true;
                        }
                        $msg.addClass('success').html(msgText);
                        
                        if (window.shuriken_auth_from_checkout) {
                            window.shuriken_auth_from_checkout = false;
                            setTimeout(function() {
                                $('.shuriken-mbm-profile-overlay, .shuriken-mbm-profile-drawer, .shuriken-mbm-profile-popup-container').removeClass('is-active');
                                $('body').css('overflow', '');
                                $(document.body).trigger('shuriken_auth_success_from_checkout');
                            }, 1000);
                        } else {
                            // Reload content to show dashboard
                            setTimeout(loadProfileContent, 1000);
                        }
                    } else {
                        $msg.addClass('error').html(response.data);
                        $btn.prop('disabled', false).removeClass('loading');
                    }
                });
            });
        }

        $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
            updateCartButtonTexts();
        });

        // Initialize texts on load
        updateCartButtonTexts();

        // Track Order Implementation
        var $trackOrderTriggers = $scope.find('.shuriken-mbm-trigger-track-order');
        var $trackOrderContainer = $scope.find('.shuriken-mbm-track-order-drawer, .shuriken-mbm-track-order-popup-container');
        var $trackOrderOverlay = $scope.find('.shuriken-mbm-track-order-overlay');
        var $trackOrderForm = $scope.find('#shuriken-track-order-form');
        var $trackOrderResults = $scope.find('.shuriken-track-order-results');
        var $trackOrderMsg = $scope.find('.shuriken-track-order-message');

        if ( $trackOrderTriggers.length && $trackOrderContainer.length ) {
            $trackOrderTriggers.on('click', function(e) {
                e.preventDefault();
                openUI($trackOrderContainer);
                $trackOrderOverlay.addClass('is-active');
            });
        }

        $trackOrderOverlay.on('click', function() {
            closeUI();
            if ($trackOrderContainer.length) $trackOrderContainer.removeClass('is-active');
            $trackOrderOverlay.removeClass('is-active');
        });
        
        $trackOrderContainer.find('.shuriken-mbm-close-ui').on('click', function() {
             closeUI();
             $trackOrderContainer.removeClass('is-active');
             $trackOrderOverlay.removeClass('is-active');
        });

        if ( $trackOrderForm.length ) {
            $trackOrderForm.on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $form.find('button[type="submit"]');
                var orderId = $form.find('input[name="order_id"]').val();
                var orderEmail = $form.find('input[name="order_email"]').val();

                $trackOrderMsg.html('').removeClass('error success');
                $trackOrderResults.html('<div class="shuriken-mbm-loading" style="padding: 20px;"><div class="shuriken-mbm-loader"></div></div>');
                $btn.prop('disabled', true).addClass('loading');

                $.post(ajaxUrl, {
                    action: 'shuriken_track_order',
                    order_id: orderId,
                    order_email: orderEmail
                }, function(response) {
                    $btn.prop('disabled', false).removeClass('loading');
                    
                    if ( response.success ) {
                        $trackOrderResults.html(response.data.html).hide().fadeIn(300);
                        $form.slideUp();
                        
                        // Add back button
                        var $backBtn = $('<button class="shuriken-mbm-back-btn" style="background: none; border: none; color: var(--shuriken-mbm-item-active-color, #0073aa); font-weight: 600; cursor: pointer; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-size: 15px;"><i class="fas fa-arrow-left"></i> Track Another Order</button>');
                        $trackOrderResults.prepend($backBtn);
                        
                        $backBtn.on('click', function() {
                            $trackOrderResults.html('');
                            $form.find('input[name="order_id"]').val('');
                            $form.slideDown();
                        });
                    } else {
                        $trackOrderResults.html('');
                        $trackOrderMsg.addClass('error').html(response.data);
                    }
                }).fail(function() {
                    $btn.prop('disabled', false).removeClass('loading');
                    $trackOrderResults.html('');
                    $trackOrderMsg.addClass('error').html('An error occurred while tracking your order. Please try again.');
                });
            });
        }

        // Intercept WooCommerce Checkout Redirect for Shuriken Popup
        $.ajaxSetup({
            dataFilter: function(data, type) {
                if (type === 'json' && data) {
                    try {
                        var json = JSON.parse(data);
                        // If it's a success, it's a redirect, and the checkout popup is visible
                        if (json.result === 'success' && json.redirect && json.redirect.indexOf('order-received') !== -1) {
                            var $checkoutPopup = $('.shuriken-popup-checkout-container');
                            if ($checkoutPopup.length && $checkoutPopup.is(':visible') && (shuriken_obj.disable_order_redirect == "1" || shuriken_obj.disable_order_redirect === true)) {
                                json.redirect = '#shuriken-order-received=' + encodeURIComponent(json.redirect);
                                return JSON.stringify(json);
                            }
                        }
                    } catch (e) {}
                }
                return data;
            }
        });

        // Handle the custom hash redirect
        $(window).on('hashchange', function() {
            var hash = window.location.hash;
            if (hash.indexOf('#shuriken-order-received=') === 0) {
                var realUrl = decodeURIComponent(hash.replace('#shuriken-order-received=', ''));
                var $checkoutPopup = $('.shuriken-popup-checkout-container');
                var $checkoutBody = $checkoutPopup.find('.shuriken-popup-checkout-body');
                
                if ($checkoutBody.length && (shuriken_obj.disable_order_redirect == "1" || shuriken_obj.disable_order_redirect === true)) {
                    $checkoutBody.html('<div style="padding: 40px; text-align: center;"><div class="shuriken-mbm-loader" style="margin: 0 auto;"></div><p style="margin-top: 15px;">Processing your order...</p></div>');
                    // Fetch the order received page and extract the content
                    $.get(realUrl, function(html) {
                        var $temp = $('<div>').html(html);
                        var $content = $temp.find('.woocommerce-order'); // WC's default wrapper for order received
                        if ($content.length) {
                            $checkoutBody.html($content);
                        } else {
                            $checkoutBody.html(html); // Fallback
                        }
                    }).fail(function() {
                        window.location.href = realUrl;
                    });
                } else {
                    window.location.href = realUrl;
                }
            }
        });

    };

    // Make sure you run this code under Elementor.
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/shuriken-mobile-bottom-menu.default', WidgetMobileBottomMenuHandler);
        elementorFrontend.hooks.addAction('frontend/element_ready/shuriken-floating-cart.default', WidgetMobileBottomMenuHandler);
    });

})(jQuery);
