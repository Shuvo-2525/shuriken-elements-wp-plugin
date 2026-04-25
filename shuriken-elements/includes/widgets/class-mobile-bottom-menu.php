<?php
namespace ShurikenElements\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Mobile Bottom Menu Widget.
 *
 * Elementor widget that inserts a sticky mobile bottom menu.
 *
 * @since 1.0.0
 */
class Mobile_Bottom_Menu extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'shuriken-mobile-bottom-menu';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Mobile Bottom Menu', 'shuriken-elements' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-menu-bar';
	}

	/**
	 * Get widget categories.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'shuriken-blocks' ];
	}

	/**
	 * Get widget styles dependencies.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget styles dependencies.
	 */
	public function get_style_depends() {
		return [ 'shuriken-mobile-bottom-menu' ];
	}

	/**
	 * Get widget scripts dependencies.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [ 'shuriken-mobile-bottom-menu' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		/* ----------------------------------------------------------------------
		 * Content Tab - Menu Items
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_menu_items',
			[
				'label' => esc_html__( 'Menu Items', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'item_title',
			[
				'label' => esc_html__( 'Title', 'shuriken-elements' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Menu Item', 'shuriken-elements' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'item_icon',
			[
				'label' => esc_html__( 'Icon', 'shuriken-elements' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-home',
					'library' => 'fa-solid',
				],
			]
		);

		$repeater->add_control(
			'item_link',
			[
				'label' => esc_html__( 'Link', 'shuriken-elements' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'shuriken-elements' ),
				'default' => [
					'url' => '#',
				],
			]
		);

		$repeater->add_control(
			'item_visibility',
			[
				'label' => esc_html__( 'Visibility Logic', 'shuriken-elements' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all' => esc_html__( 'Show to All', 'shuriken-elements' ),
					'logged_in' => esc_html__( 'Logged In Users Only', 'shuriken-elements' ),
					'logged_out' => esc_html__( 'Logged Out Users Only', 'shuriken-elements' ),
				],
			]
		);

		$repeater->add_control(
			'item_order',
			[
				'label' => esc_html__( 'Item Order', 'shuriken-elements' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'description' => esc_html__( 'Visual position (0 = first, 1 = second, etc.)', 'shuriken-elements' ),
			]
		);

        // Submenu integration - currently assuming flat links to WordPress Pages or standard URLS for simplicity 
        // to avoid nested repeaters limitation, as noted in the plan. Later can integrate with WP Nav Menus.

		$this->add_control(
			'menu_items',
			[
				'label' => esc_html__( 'Menu Items', 'shuriken-elements' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'item_title' => esc_html__( 'Home', 'shuriken-elements' ),
						'item_icon' => [ 'value' => 'fas fa-home', 'library' => 'fa-solid' ],
					],
					[
						'item_title' => esc_html__( 'Search', 'shuriken-elements' ),
						'item_icon' => [ 'value' => 'fas fa-search', 'library' => 'fa-solid' ],
					],
					[
						'item_title' => esc_html__( 'Profile', 'shuriken-elements' ),
						'item_icon' => [ 'value' => 'fas fa-user', 'library' => 'fa-solid' ],
					],
				],
				'title_field' => '{{{ item_title }}}',
			]
		);

		$this->end_controls_section();

		/* ----------------------------------------------------------------------
		 * Content Tab - E-Commerce & Features
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_ecommerce',
			[
				'label' => esc_html__( 'E-Commerce & Extras', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_cart',
			[
				'label' => esc_html__( 'Show Cart Icon', 'shuriken-elements' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shuriken-elements' ),
				'label_off' => esc_html__( 'Hide', 'shuriken-elements' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
        
        $this->add_control(
			'cart_order',
			[
				'label' => esc_html__( 'Cart Item Order', 'shuriken-elements' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 99,
				'description' => esc_html__( 'Visual position (Higher number = further right)', 'shuriken-elements' ),
                'condition' => [
                    'show_cart' => 'yes'
                ]
			]
		);
        
        $this->add_control(
			'cart_icon',
			[
				'label' => esc_html__( 'Cart Icon', 'shuriken-elements' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-shopping-cart',
					'library' => 'fa-solid',
				],
                'condition' => [
                    'show_cart' => 'yes'
                ]
			]
		);

        $this->add_control(
			'cart_action',
			[
				'label' => esc_html__( 'Cart Click Action', 'shuriken-elements' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'redirect',
				'options' => [
					'redirect' => esc_html__( 'Redirect to Cart Page', 'shuriken-elements' ),
					'sidebar'  => esc_html__( 'Open On-Page Sidebar Cart', 'shuriken-elements' ),
					'drawer'   => esc_html__( 'Open Bottom Drawer Cart', 'shuriken-elements' ),
				],
                'condition' => [
                    'show_cart' => 'yes'
                ]
			]
		);

        $this->add_control(
			'show_view_cart_btn',
			[
				'label' => esc_html__( 'Show "View Cart" Button', 'shuriken-elements' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shuriken-elements' ),
				'label_off' => esc_html__( 'Hide', 'shuriken-elements' ),
				'return_value' => 'yes',
				'default' => 'no',
                'condition' => [
                    'show_cart' => 'yes',
                    'cart_action!' => 'redirect',
                ]
			]
		);

        $this->add_control(
			'view_cart_text',
			[
				'label' => esc_html__( '"View Cart" Text', 'shuriken-elements' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'View Cart', 'shuriken-elements' ),
                'condition' => [
                    'show_cart' => 'yes',
                    'cart_action!' => 'redirect',
                    'show_view_cart_btn' => 'yes'
                ]
			]
		);

        $this->add_control(
			'checkout_text',
			[
				'label' => esc_html__( '"Checkout" Text', 'shuriken-elements' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Checkout', 'shuriken-elements' ),
                'condition' => [
                    'show_cart' => 'yes',
                    'cart_action!' => 'redirect',
                ]
			]
		);

		$this->end_controls_section();

		/* ----------------------------------------------------------------------
		 * Content Tab - Search Configuration
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_search_config',
			[
				'label' => esc_html__( 'Search Configuration', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_search',
			[
				'label' => esc_html__( 'Show Search Icon', 'shuriken-elements' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shuriken-elements' ),
				'label_off' => esc_html__( 'Hide', 'shuriken-elements' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

        $this->add_control(
			'search_order',
			[
				'label' => esc_html__( 'Search Item Order', 'shuriken-elements' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 50,
				'description' => esc_html__( 'Visual position (Higher number = further right)', 'shuriken-elements' ),
                'condition' => [
                    'show_search' => 'yes'
                ]
			]
		);

        $this->add_control(
			'search_label',
			[
				'label' => esc_html__( 'Search Label Title', 'shuriken-elements' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Search', 'shuriken-elements' ),
                'condition' => [
                    'show_search' => 'yes'
                ]
			]
		);

        $this->add_control(
			'search_item_icon',
			[
				'label' => esc_html__( 'Search Icon', 'shuriken-elements' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-search',
					'library' => 'fa-solid',
				],
                'condition' => [
                    'show_search' => 'yes'
                ]
			]
		);

        $this->add_control(
			'search_action',
			[
				'label' => esc_html__( 'Search Action', 'shuriken-elements' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'search_ajax',
				'options' => [
					'search_ajax' => esc_html__( 'Real-time AJAX Search', 'shuriken-elements' ),
					'search_redirect' => esc_html__( 'Standard Search Form', 'shuriken-elements' ),
				],
                'condition' => [
                    'show_search' => 'yes'
                ]
			]
		);

		$this->add_control(
			'search_query_type',
			[
				'label' => esc_html__( 'Search Source', 'shuriken-elements' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all' => esc_html__( 'All Posts & Pages', 'shuriken-elements' ),
					'product' => esc_html__( 'WooCommerce Products', 'shuriken-elements' ),
				],
                'condition' => [
                    'show_search' => 'yes'
                ]
			]
		);

		$this->add_control(
			'search_placeholder',
			[
				'label' => esc_html__( 'Search Placeholder Text', 'shuriken-elements' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Search...', 'shuriken-elements' ),
                'condition' => [
                    'show_search' => 'yes'
                ]
			]
		);

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->add_control(
				'woo_missing_notice',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw' => esc_html__( 'Warning: WooCommerce is not active. The "WooCommerce Products" search will fallback or not work correctly until WooCommerce is installed.', 'shuriken-elements' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'condition' => [
                        'show_search' => 'yes',
						'search_query_type' => 'product',
					],
				]
			);
		}

		$this->end_controls_section();

		/* ----------------------------------------------------------------------
		 * Content Tab - Profile Configuration
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_profile_config',
			[
				'label' => esc_html__( 'Profile Configuration', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_profile',
			[
				'label' => esc_html__( 'Show Profile Icon', 'shuriken-elements' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shuriken-elements' ),
				'label_off' => esc_html__( 'Hide', 'shuriken-elements' ),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);

        $this->add_control(
			'profile_order',
			[
				'label' => esc_html__( 'Profile Item Order', 'shuriken-elements' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 75,
				'description' => esc_html__( 'Visual position (Higher number = further right)', 'shuriken-elements' ),
                'condition' => [
                    'show_profile' => 'yes'
                ]
			]
		);

        $this->add_control(
			'profile_label',
			[
				'label' => esc_html__( 'Profile Label Title', 'shuriken-elements' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Account', 'shuriken-elements' ),
                'condition' => [
                    'show_profile' => 'yes'
                ]
			]
		);

        $this->add_control(
			'profile_item_icon',
			[
				'label' => esc_html__( 'Profile Icon', 'shuriken-elements' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-user',
					'library' => 'fa-solid',
				],
                'condition' => [
                    'show_profile' => 'yes'
                ]
			]
		);

		$this->end_controls_section();

        /* ----------------------------------------------------------------------
		 * Content Tab - Visibility
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_visibility',
			[
				'label' => esc_html__( 'Visibility', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
			'show_desktop',
			[
				'label' => esc_html__( 'Show on Desktop', 'shuriken-elements' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shuriken-elements' ),
				'label_off' => esc_html__( 'Hide', 'shuriken-elements' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

        $this->add_control(
			'show_tablet',
			[
				'label' => esc_html__( 'Show on Tablet', 'shuriken-elements' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shuriken-elements' ),
				'label_off' => esc_html__( 'Hide', 'shuriken-elements' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

        $this->add_control(
			'show_mobile',
			[
				'label' => esc_html__( 'Show on Mobile', 'shuriken-elements' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Show', 'shuriken-elements' ),
				'label_off' => esc_html__( 'Hide', 'shuriken-elements' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->end_controls_section();

		/* ----------------------------------------------------------------------
		 * Style Tab - Container
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_container',
			[
				'label' => esc_html__( 'Container', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_background',
				'label' => esc_html__( 'Background', 'shuriken-elements' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .shuriken-mobile-bottom-menu-wrapper',
			]
		);

        $this->add_responsive_control(
			'container_padding',
			[
				'label' => esc_html__( 'Padding', 'shuriken-elements' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .shuriken-mobile-bottom-menu-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'label' => esc_html__( 'Border', 'shuriken-elements' ),
				'selector' => '{{WRAPPER}} .shuriken-mobile-bottom-menu-wrapper',
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label' => esc_html__( 'Border/Curve Radius', 'shuriken-elements' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .shuriken-mobile-bottom-menu-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_box_shadow',
				'label' => esc_html__( 'Box Shadow (Elevation)', 'shuriken-elements' ),
				'selector' => '{{WRAPPER}} .shuriken-mobile-bottom-menu-wrapper',
			]
		);

		$this->end_controls_section();

		/* ----------------------------------------------------------------------
		 * Style Tab - Menu Items
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_items',
			[
				'label' => esc_html__( 'Items Styling', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->start_controls_tabs( 'tabs_items_style' );

		// Normal State
		$this->start_controls_tab(
			'tab_items_normal',
			[
				'label' => esc_html__( 'Normal', 'shuriken-elements' ),
			]
		);

		$this->add_control(
			'item_color',
			[
				'label' => esc_html__( 'Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .shuriken-mbm-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();

		// Hover/Active State
		$this->start_controls_tab(
			'tab_items_hover',
			[
				'label' => esc_html__( 'Active/Hover', 'shuriken-elements' ),
			]
		);

		$this->add_control(
			'item_active_color',
			[
				'label' => esc_html__( 'Active Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .shuriken-mbm-link:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .shuriken-mbm-link:active' => 'color: {{VALUE}};',
					'{{WRAPPER}} .shuriken-mbm-item.active .shuriken-mbm-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_control(
			'icon_position',
			[
				'label' => esc_html__( 'Icon Position', 'shuriken-elements' ),
				'type' => Controls_Manager::CHOOSE,
                'separator' => 'before',
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'shuriken-elements' ),
						'icon' => 'eicon-h-align-left',
					],
					'top' => [
						'title' => esc_html__( 'Top', 'shuriken-elements' ),
						'icon' => 'eicon-v-align-top',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'shuriken-elements' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'top',
				'toggle' => false,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'shuriken-elements' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .shuriken-mbm-icon, {{WRAPPER}} .shuriken-mbm-icon svg' => '--shuriken-mbm-icon-size: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_spacing',
			[
				'label' => esc_html__( 'Icon Spacing', 'shuriken-elements' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .shuriken-mbm-link' => '--shuriken-mbm-icon-spacing: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'label' => esc_html__( 'Typography', 'shuriken-elements' ),
				'selector' => '{{WRAPPER}} .shuriken-mbm-label',
			]
		);

		$this->end_controls_section();
        
        /* ----------------------------------------------------------------------
		 * Style Tab - Cart Badge
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_cart_badge',
			[
				'label' => esc_html__( 'Cart Badge Styling', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
        
        $this->add_control(
			'badge_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .shuriken-mbm-cart-badge' => 'background-color: {{VALUE}};',
				],
			]
		);
        
        $this->add_control(
			'badge_text_color',
			[
				'label' => esc_html__( 'Text Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .shuriken-mbm-cart-badge' => 'color: {{VALUE}};',
				],
			]
		);
        
        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'badge_typography',
				'selector' => '{{WRAPPER}} .shuriken-mbm-cart-badge',
			]
		);
        
        $this->end_controls_section();

		/* ----------------------------------------------------------------------
		 * Style Tab - Cart Sidebar/Drawer UI
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_cart_ui',
			[
				'label' => esc_html__( 'Cart Drawer / Sidebar UI', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_cart' => 'yes',
                    'cart_action!' => 'redirect'
                ]
			]
		);
        
        $this->add_control(
			'cart_ui_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-sidebar, .shuriken-mbm-drawer' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'cart_ui_header_bg',
			[
				'label' => esc_html__( 'Header Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-sidebar .shuriken-mbm-ui-header, .shuriken-mbm-drawer .shuriken-mbm-ui-header' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'cart_ui_header_color',
			[
				'label' => esc_html__( 'Header Text Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-sidebar .shuriken-mbm-ui-header h3, .shuriken-mbm-drawer .shuriken-mbm-ui-header h3, .shuriken-mbm-sidebar .shuriken-mbm-close-ui, .shuriken-mbm-drawer .shuriken-mbm-close-ui' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'cart_ui_item_border',
			[
				'label' => esc_html__( 'Item Divider Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-ui-body' => '--shuriken-mbm-cart-item-border: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'cart_ui_btn_bg',
			[
				'label' => esc_html__( 'Checkout Button Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-ui-body' => '--shuriken-mbm-cart-btn-bg: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'cart_ui_btn_color',
			[
				'label' => esc_html__( 'Checkout Button Text Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-ui-body' => '--shuriken-mbm-cart-btn-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'heading_view_cart_styling',
			[
				'label' => esc_html__( 'View Cart Button', 'shuriken-elements' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
                'condition' => [
                    'show_view_cart_btn' => 'yes'
                ]
			]
		);

        $this->add_control(
			'view_cart_bg',
			[
				'label' => esc_html__( 'Background Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-sidebar, .shuriken-mbm-drawer' => '--shuriken-mbm-vc-bg: {{VALUE}};',
				],
                'condition' => [
                    'show_view_cart_btn' => 'yes'
                ]
			]
		);

        $this->add_control(
			'view_cart_color',
			[
				'label' => esc_html__( 'Text Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-sidebar, .shuriken-mbm-drawer' => '--shuriken-mbm-vc-color: {{VALUE}};',
				],
                'condition' => [
                    'show_view_cart_btn' => 'yes'
                ]
			]
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'view_cart_typography',
				'selector' => '.shuriken-mbm-sidebar .woocommerce-mini-cart__buttons .button:not(.checkout), .shuriken-mbm-drawer .woocommerce-mini-cart__buttons .button:not(.checkout)',
                'condition' => [
                    'show_view_cart_btn' => 'yes'
                ]
			]
		);

        $this->add_control(
			'heading_checkout_styling_adv',
			[
				'label' => esc_html__( 'Checkout Button', 'shuriken-elements' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'checkout_typography',
				'selector' => '.shuriken-mbm-sidebar .woocommerce-mini-cart__buttons .button.checkout, .shuriken-mbm-drawer .woocommerce-mini-cart__buttons .button.checkout',
			]
		);

        $this->add_control(
			'heading_qty_styling',
			[
				'label' => esc_html__( 'Quantity Adjuster - Global', 'shuriken-elements' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_responsive_control(
			'qty_btn_size',
			[
				'label' => esc_html__( 'Buttons Size', 'shuriken-elements' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'selectors' => [
					'.shuriken-mbm-qty-btn' => '--shuriken-mbm-qty-btn-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

        $this->add_responsive_control(
			'qty_btn_radius',
			[
				'label' => esc_html__( 'Buttons Radius', 'shuriken-elements' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'.shuriken-mbm-qty-btn' => '--shuriken-mbm-qty-btn-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
			'heading_qty_plus',
			[
				'label' => esc_html__( 'Plus (+) Button', 'shuriken-elements' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_control(
			'qty_plus_bg',
			[
				'label' => esc_html__( 'Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-qty-btn.plus' => '--shuriken-mbm-qty-plus-bg: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'qty_plus_color',
			[
				'label' => esc_html__( 'Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-qty-btn.plus' => '--shuriken-mbm-qty-plus-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'qty_plus_border',
			[
				'label' => esc_html__( 'Border Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-qty-btn.plus' => '--shuriken-mbm-qty-plus-border: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'heading_qty_minus',
			[
				'label' => esc_html__( 'Minus (-) Button', 'shuriken-elements' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_control(
			'qty_minus_bg',
			[
				'label' => esc_html__( 'Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-qty-btn.minus' => '--shuriken-mbm-qty-minus-bg: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'qty_minus_color',
			[
				'label' => esc_html__( 'Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-qty-btn.minus' => '--shuriken-mbm-qty-minus-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'qty_minus_border',
			[
				'label' => esc_html__( 'Border Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-qty-btn.minus' => '--shuriken-mbm-qty-minus-border: {{VALUE}};',
				],
			]
		);

        $this->end_controls_section();

		/* ----------------------------------------------------------------------
		 * Style Tab - Search UI
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_search_ui',
			[
				'label' => esc_html__( 'Search Overlay UI', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
			'search_ui_bg_color',
			[
				'label' => esc_html__( 'Overlay Background Overlay', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-search-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'search_input_bg',
			[
				'label' => esc_html__( 'Input Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-search-form input[type="search"]' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'search_input_color',
			[
				'label' => esc_html__( 'Input Text Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-search-form input[type="search"]' => 'color: {{VALUE}} !important;',
				],
			]
		);

        $this->add_control(
			'search_button_bg',
			[
				'label' => esc_html__( 'Search Button Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-search-submit' => 'background-color: {{VALUE}}; box-shadow: 0 4px 10px {{VALUE}}4D;',
				],
			]
		);

        $this->add_control(
			'search_button_color',
			[
				'label' => esc_html__( 'Search Button Icon Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-search-submit i' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'search_field_border',
				'label' => esc_html__( 'Input Border', 'shuriken-elements' ),
				'selector' => '{{WRAPPER}} .shuriken-mbm-search-field-group',
			]
		);

        $this->add_control(
			'search_field_border_radius',
			[
				'label' => esc_html__( 'Input Border Radius', 'shuriken-elements' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'.shuriken-mbm-search-field-group' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
			]
		);

        $this->add_control(
			'heading_close_btn_style',
			[
				'label' => esc_html__( 'Close Button Styling', 'shuriken-elements' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_control(
			'search_close_alignment',
			[
				'label' => esc_html__( 'Close Button Alignment', 'shuriken-elements' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'shuriken-elements' ),
						'icon' => 'eicon-text-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'shuriken-elements' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'right',
			]
		);

        $this->add_control(
			'search_close_size',
			[
				'label' => esc_html__( 'Close Button Size', 'shuriken-elements' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 80,
					],
				],
				'selectors' => [
					'.shuriken-mbm-close-search' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: calc({{SIZE}}{{UNIT}} / 2);',
				],
			]
		);

        $this->add_control(
			'search_close_bg',
			[
				'label' => esc_html__( 'Close Button Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-close-search' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'search_close_color',
			[
				'label' => esc_html__( 'Close Button Icon Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-close-search' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'search_results_bg',
			[
				'label' => esc_html__( 'Results Container Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-search-results' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'search_results_color',
			[
				'label' => esc_html__( 'Results Text Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-search-results .shuriken-search-result-item a' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'search_results_hover_bg',
			[
				'label' => esc_html__( 'Results Item Hover Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-mbm-search-results .shuriken-search-result-item:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

	}

	/**
	 * Render widget output on the frontend.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( empty( $settings['menu_items'] ) ) {
			return;
		}
        
        $icon_pos_class = 'icon-pos-' . $settings['icon_position'];

        // Visibility Classes
        $visibility_classes = [];
        if ( $settings['show_desktop'] !== 'yes' ) $visibility_classes[] = 'shuriken-mbm-hide-desktop';
        if ( $settings['show_tablet'] !== 'yes' )  $visibility_classes[] = 'shuriken-mbm-hide-tablet';
        if ( $settings['show_mobile'] !== 'yes' )  $visibility_classes[] = 'shuriken-mbm-hide-mobile';

		?>
		<div class="shuriken-mobile-bottom-menu-wrapper <?php echo esc_attr( implode( ' ', $visibility_classes ) ); ?>">
			<ul class="shuriken-mbm-grid">
				<?php
				foreach ( $settings['menu_items'] as $index => $item ) {
                    
                    // Visibility Logic
                    $visibility = $item['item_visibility'];
                    if ( $visibility === 'logged_in' && ! is_user_logged_in() ) {
                        continue;
                    }
                    if ( $visibility === 'logged_out' && is_user_logged_in() ) {
                        continue;
                    }

					$repeater_setting_key = $this->get_repeater_setting_key( 'item_title', 'menu_items', $index );
					$this->add_render_attribute( $repeater_setting_key, 'class', 'shuriken-mbm-link' );
                    $this->add_render_attribute( $repeater_setting_key, 'class', $icon_pos_class );
					
					if ( ! empty( $item['item_link']['url'] ) ) {
						$this->add_link_attributes( $repeater_setting_key, $item['item_link'] );
					}
					
					$item_order = isset( $item['item_order'] ) ? $item['item_order'] : 0;
					?>
					<li class="shuriken-mbm-item" style="order: <?php echo esc_attr( $item_order ); ?>;">
						<a <?php echo $this->get_render_attribute_string( $repeater_setting_key ); ?>>
							<?php if ( ! empty( $item['item_icon']['value'] ) ) : ?>
								<div class="shuriken-mbm-icon">
									<?php Icons_Manager::render_icon( $item['item_icon'], [ 'aria-hidden' => 'true' ] ); ?>
								</div>
							<?php endif; ?>
							<span class="shuriken-mbm-label"><?php echo esc_html( $item['item_title'] ); ?></span>
						</a>
					</li>
					<?php
				}
                
                // Render Search if enabled
                if ( $settings['show_search'] === 'yes' ) {
                    $search_class = 'shuriken-mbm-link shuriken-mbm-trigger-search ' . esc_attr($icon_pos_class);
                    $search_action = isset($settings['search_action']) ? $settings['search_action'] : 'search_ajax';
                    $search_order = isset( $settings['search_order'] ) ? $settings['search_order'] : 50;
                    ?>
                    <li class="shuriken-mbm-item shuriken-mbm-item-search" style="order: <?php echo esc_attr( $search_order ); ?>;">
						<a href="javascript:void(0);" class="<?php echo esc_attr($search_class); ?>" data-search-type="<?php echo esc_attr($search_action); ?>">
                            <div class="shuriken-mbm-icon">
                                <?php Icons_Manager::render_icon( $settings['search_item_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                            </div>
							<span class="shuriken-mbm-label"><?php echo esc_html($settings['search_label']); ?></span>
						</a>
					</li>
                    <?php
                }
                
                // Render Cart if enabled
                if ( $settings['show_cart'] === 'yes' ) {
                    $cart_count = 0;
                    $cart_url = '#';
                    if ( class_exists( 'WooCommerce' ) ) {
                        $cart_count = WC()->cart->get_cart_contents_count();
                        $cart_url = wc_get_cart_url();
                    }
                    
                    $cart_action_attr = '';
                    $cart_class = 'shuriken-mbm-link ' . esc_attr($icon_pos_class);
                    if ( $settings['cart_action'] === 'sidebar' ) {
                        $cart_url = 'javascript:void(0);';
                        $cart_class .= ' shuriken-mbm-trigger-sidebar';
                    } elseif ( $settings['cart_action'] === 'drawer' ) {
                        $cart_url = 'javascript:void(0);';
                        $cart_class .= ' shuriken-mbm-trigger-drawer';
                    }

                    $cart_order = isset( $settings['cart_order'] ) ? $settings['cart_order'] : 99;
                    ?>
                    <li class="shuriken-mbm-item shuriken-mbm-item-cart" style="order: <?php echo esc_attr( $cart_order ); ?>;">
						<a href="<?php echo esc_url( $cart_url ); ?>" class="<?php echo esc_attr($cart_class); ?>">
                            <div class="shuriken-mbm-icon">
                                <?php Icons_Manager::render_icon( $settings['cart_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                                <span class="shuriken-mbm-cart-badge"><?php echo esc_html($cart_count); ?></span>
                            </div>
							<span class="shuriken-mbm-label"><?php esc_html_e('Cart', 'shuriken-elements'); ?></span>
						</a>
					</li>
                    <?php
                }

                // Render Profile if enabled
                if ( isset($settings['show_profile']) && $settings['show_profile'] === 'yes' ) {
                    $profile_class = 'shuriken-mbm-link shuriken-mbm-trigger-profile ' . esc_attr($icon_pos_class);
                    $profile_order = isset( $settings['profile_order'] ) ? $settings['profile_order'] : 75;
                    ?>
                    <li class="shuriken-mbm-item shuriken-mbm-item-profile" style="order: <?php echo esc_attr( $profile_order ); ?>;">
						<a href="javascript:void(0);" class="<?php echo esc_attr($profile_class); ?>">
                            <div class="shuriken-mbm-icon">
                                <?php Icons_Manager::render_icon( $settings['profile_item_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                            </div>
							<span class="shuriken-mbm-label"><?php echo esc_html($settings['profile_label']); ?></span>
						</a>
					</li>
                    <?php
                }
				?>
			</ul>
		</div>

        <?php 
        // Render Sidebar or Drawer Structure if needed
        if ( $settings['show_cart'] === 'yes' && in_array( $settings['cart_action'], ['sidebar', 'drawer'] ) ) {
            $ui_type = $settings['cart_action'] === 'sidebar' ? 'shuriken-mbm-sidebar' : 'shuriken-mbm-drawer';
            
            $hide_view_cart_class = $settings['show_view_cart_btn'] !== 'yes' ? 'shuriken-mbm-hide-view-cart' : '';

            ?>
            <div class="shuriken-mbm-overlay"></div>
            <div class="<?php echo esc_attr( $ui_type ); ?> <?php echo esc_attr($hide_view_cart_class); ?>" 
                 data-view-cart-text="<?php echo esc_attr($settings['view_cart_text']); ?>"
                 data-checkout-text="<?php echo esc_attr($settings['checkout_text']); ?>">
                <div class="shuriken-mbm-ui-header">
                    <h3><?php esc_html_e( 'Your Cart', 'shuriken-elements' ); ?></h3>
                    <button class="shuriken-mbm-close-ui">&times;</button>
                </div>
                <div class="shuriken-mbm-ui-body">
                    <?php 
                    // This div target will be injected by WC fragments
                    if ( class_exists( 'WooCommerce' ) ) {
                        \ShurikenElements\Class_Shuriken_WooCommerce::instance()->render_cart_content();
                    } 
                    ?>
                </div>
            </div>
            <?php
        }

        // Render Search Overlay if search is enabled
        $has_search = ( isset($settings['show_search']) && $settings['show_search'] === 'yes' );

        if ( $has_search ) {
            $placeholder = ! empty( $settings['search_placeholder'] ) ? $settings['search_placeholder'] : esc_attr__( 'Search...', 'shuriken-elements' );
            $query_type = isset($settings['search_query_type']) ? $settings['search_query_type'] : 'all';
            $close_alignment = isset($settings['search_close_alignment']) ? $settings['search_close_alignment'] : 'right';
            ?>
            <div class="shuriken-mbm-search-overlay" style="display: none;" data-query-type="<?php echo esc_attr($query_type); ?>">
                <div class="shuriken-mbm-search-header" style="text-align: <?php echo esc_attr($close_alignment); ?>;">
                    <button class="shuriken-mbm-close-search">&times;</button>
                </div>
                <div class="shuriken-mbm-search-container">
                    <form role="search" method="get" class="shuriken-mbm-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <div class="shuriken-mbm-search-field-group">
                            <input type="search" class="shuriken-mbm-search-input" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
                            <button type="submit" class="shuriken-mbm-search-submit" title="<?php esc_attr_e( 'Search', 'shuriken-elements' ); ?>">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <?php if ( $query_type === 'product' && class_exists( 'WooCommerce' ) ) : ?>
                            <input type="hidden" name="post_type" value="product" />
                        <?php endif; ?>
                    </form>
                    <div class="shuriken-mbm-search-results"></div>
                </div>
            </div>
            <?php
        }

        // Render Profile Drawer if enabled
        if ( isset($settings['show_profile']) && $settings['show_profile'] === 'yes' ) {
            ?>
            <div class="shuriken-mbm-overlay shuriken-mbm-profile-overlay"></div>
            <div class="shuriken-mbm-drawer shuriken-mbm-profile-drawer">
                <div class="shuriken-mbm-ui-header">
                    <h3><?php echo esc_html($settings['profile_label']); ?></h3>
                    <button class="shuriken-mbm-close-ui">&times;</button>
                </div>
                <div class="shuriken-mbm-ui-body">
                    <div class="shuriken-mbm-profile-content">
                        <!-- AJAX content will be loaded here -->
                        <div class="shuriken-mbm-loading">
                            <div class="shuriken-mbm-loader"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
	}
}
