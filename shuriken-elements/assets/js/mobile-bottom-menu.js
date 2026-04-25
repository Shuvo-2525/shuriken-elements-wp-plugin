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

        $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
            updateCartButtonTexts();
        });

        // Initialize texts on load
        updateCartButtonTexts();
    };

    // Make sure you run this code under Elementor.
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/shuriken-mobile-bottom-menu.default', WidgetMobileBottomMenuHandler);
        elementorFrontend.hooks.addAction('frontend/element_ready/shuriken-floating-cart.default', WidgetMobileBottomMenuHandler);
    });

})(jQuery);
