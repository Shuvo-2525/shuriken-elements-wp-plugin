# Shuriken Elements

**Version:** 1.1.0
**Author:** Mohammad Rafiq Shuvo
**Tested up to Elementor:** 3.20.0
**Requires PHP:** 7.4+
**Requires Elementor:** 3.0.0+

Shuriken Elements is a powerful Elementor Addon designed to elevate your WordPress and WooCommerce site with robust, dynamic, and premium widgets. It seamlessly integrates advanced e-commerce functionalities directly into your Elementor workflow.

## 🚀 Features & Functionality

### 1. Mobile Bottom Menu Widget
A highly customizable, app-like sticky mobile navigation bar that stays fixed at the bottom of the screen, enhancing mobile user experience and conversions.

**Key Capabilities:**
*   **Dynamic Menu Items:** Add unlimited menu items using a repeater field. Set custom titles, icons, and links.
*   **Visibility Logic:** Show or hide specific menu items based on user login status (All, Logged In, Logged Out).
*   **Visual Ordering:** Control the exact position of each item, including system items like Cart and Search.
*   **Advanced Cart Integration:**
    *   Toggle Cart icon visibility.
    *   Choose Cart Click Action: Redirect to Cart Page, Open On-Page Sidebar Cart, or Open Bottom Drawer Cart.
    *   Customize "View Cart" and "Checkout" button text.
    *   Dynamic Cart Badge showing item count with full style controls (colors, typography).
*   **Smart Search Integration:**
    *   Toggle Search icon visibility.
    *   Choose Search Action: Real-time AJAX Search or Standard Search Form redirect.
    *   Set Search Source: Query all Post & Pages or exclusively WooCommerce Products.
    *   Custom placeholder text.
*   **Responsive Visibility:** Choose to show/hide the menu on Desktop, Tablet, and Mobile devices independently.
*   **Deep Styling Options:**
    *   Container: Background (classic/gradient), padding, borders, border-radius, and box-shadow (elevation).
    *   Items: Normal and Active/Hover colors for icons and text.
    *   Icons: Position (Left, Top, Right), scalable size, and spacing adjustments.
    *   Cart UI (Sidebar/Drawer): Fully customize the background, header, text colors, item dividers, and checkout button styling.

### 2. Popup Checkout Widget
A modern, frictionless checkout experience. This widget outputs a hidden checkout modal that is triggered instantly without page reloads, typically via the Mobile Bottom Menu.

**Key Capabilities:**
*   **Seamless WooCommerce Integration:** Directly renders the full WooCommerce checkout flow inside a customizable Elementor popup.
*   **Customizable Header:** Edit the popup title and close button text/icons.
*   **Overlay & Container Styling:**
    *   Full control over the backdrop overlay color.
    *   Container Max Width adjustments for responsive rendering.
    *   Background styles (classic or gradients).
    *   Custom borders, border-radius, and drop shadows for a premium look.
    *   Header and Close Button hover effects and typography settings.
*   **Smart Warnings:** Alerts you within the Elementor editor if WooCommerce is inactive.

### 3. WooCommerce Checkout Field Editor (Admin Dashboard)
A professional-grade checkout field manager built directly into your WordPress admin panel (`Shuriken Elements > Checkout Fields`).

**Key Capabilities:**
*   **Full Field Management:** Edit Billing, Shipping, and Additional order fields.
*   **Drag & Drop Ordering:** Easily change the position of fields using an intuitive drag-and-drop interface.
*   **Advanced Field Properties:**
    *   Change Field Labels and Placeholders.
    *   Modify Field Types (text, email, tel, textarea, etc.).
    *   Toggle 'Required' and 'Enabled' status for any field.
    *   Set custom CSS classes for layout control (e.g., `form-row-wide`, `form-row-first`).
    *   Configure validation rules.
*   **Display Toggles:** Choose whether custom fields show up in Order Emails and the Order details screen.
*   **Safe Defaults:** One-click reset to restore default WooCommerce checkout fields.
*   **AJAX Powered:** Instant saving and resetting without page reloads.

---

## 📋 Changelog

### Version 1.2.0
*   **New Feature:** Added **Special Redirects Interface** with a modern UI for configuring and managing advanced redirect rules.
*   **New Feature:** Implemented **Page Access Control** with a new tabbed UI for managing per-page/post blocking rules.
*   **New Feature:** Added "Blocked by Shuriken" **Status Badge** in the WordPress admin Pages/Posts list for quick visibility of blocking configurations.
*   **Improvement:** Enhanced **Mobile Bottom Menu** with a flexible ordering system, allowing unified sequence customization for standard items, search, and cart triggers.
*   **Improvement:** Upgraded **Mobile Cart** with robust AJAX-based quantity increment/decrement controls and granular device visibility toggles.
*   **Improvement:** Added full Elementor-based customization options for cart buttons and text within the Mobile Bottom Menu.
*   **Fix:** Resolved a **Ghost Checkout Field** UI bug that caused layout obstructions upon page load.
*   **Fix:** Fixed synchronization issues in the **URL Blocking System** to ensure manual blocking rules are correctly applied to the frontend.

### Version 1.1.0
*   **New Feature:** Added **Live Preview** to the Checkout Field Editor for real-time visual feedback.
*   **New Feature:** Added **Coupon Section management** to the Checkout Field Editor.
*   **New Feature:** Integrated **Drag-and-Drop** support within the Checkout Live Preview panel.
*   **Improvement:** Implemented **AJAX-based Coupon submission** in Popup Checkout to prevent unwanted page reloads.
*   **Improvement:** Enhanced **Asset Enqueueing** logic for Popup Checkout, ensuring compatibility across all pages.
*   **Improvement:** Added **Automatic Body Padding** adjustment for the Mobile Bottom Menu to prevent content overlap.
*   **Fix:** Resolved **Sidebar Cart Drawer** scrolling and layout issues for better accessibility.
*   **Fix:** Fixed "Empty Checkout Popup" issue on first load by ensuring fragments are correctly triggered.
*   **Fix:** Replaced unreliable delete links with a robust **AJAX-based item deletion** handler in the cart drawer.
*   **Fix:** Optimized **Coupon positioning** logic within the checkout popup flow.

### Version 1.0.0
*   **Initial Release:** Core plugin architecture established.
*   **Added:** Elementor and PHP version validation checks.
*   **Added:** Mobile Bottom Menu Widget with Cart & AJAX Search integration.
*   **Added:** Popup Checkout Widget for frictionless purchasing.
*   **Added:** Advanced Checkout Field Editor in the WP Admin Dashboard.
*   **Added:** Frontend integration engine (`class-shuriken-wc-checkout-fields.php`) to apply custom checkout fields to WooCommerce.
