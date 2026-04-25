<?php
namespace ShurikenElements\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Floating Cart Widget.
 *
 * Elementor widget that inserts a floating cart button and UI.
 *
 * @since 1.0.0
 */
class Floating_Cart extends Widget_Base {

	public function get_name() {
		return 'shuriken-floating-cart';
	}

	public function get_title() {
		return esc_html__( 'Floating Cart', 'shuriken-elements' );
	}

	public function get_icon() {
		return 'eicon-cart-medium';
	}

	public function get_categories() {
		return [ 'shuriken-blocks' ];
	}

	public function get_style_depends() {
		return [ 'shuriken-mobile-bottom-menu', 'shuriken-floating-cart' ];
	}

	public function get_script_depends() {
		return [ 'shuriken-mobile-bottom-menu' ];
	}

	protected function register_controls() {

		/* ----------------------------------------------------------------------
		 * Content Tab - Button Configuration
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_button_config',
			[
				'label' => esc_html__( 'Floating Button', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'button_position',
			[
				'label' => esc_html__( 'Position', 'shuriken-elements' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'bottom-right',
				'options' => [
					'bottom-left' => esc_html__( 'Bottom Left', 'shuriken-elements' ),
					'bottom-right' => esc_html__( 'Bottom Right', 'shuriken-elements' ),
				],
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
			]
		);

		$this->add_control(
			'cart_action',
			[
				'label' => esc_html__( 'Click Action', 'shuriken-elements' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'sidebar',
				'options' => [
					'redirect' => esc_html__( 'Redirect to Cart Page', 'shuriken-elements' ),
					'sidebar'  => esc_html__( 'Open On-Page Sidebar Cart', 'shuriken-elements' ),
					'drawer'   => esc_html__( 'Open Bottom Drawer Cart', 'shuriken-elements' ),
				],
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
                    'cart_action!' => 'redirect',
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
		 * Style Tab - Button
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_button',
			[
				'label' => esc_html__( 'Floating Button Styling', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'shuriken-elements' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .shuriken-fc-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );
		$this->start_controls_tab( 'tab_button_normal', [ 'label' => esc_html__( 'Normal', 'shuriken-elements' ) ] );

		$this->add_control(
			'button_color',
			[
				'label' => esc_html__( 'Icon Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .shuriken-fc-btn' => 'color: {{VALUE}};',
					'{{WRAPPER}} .shuriken-fc-btn svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background',
				'label' => esc_html__( 'Background', 'shuriken-elements' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .shuriken-fc-btn',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab( 'tab_button_hover', [ 'label' => esc_html__( 'Hover', 'shuriken-elements' ) ] );

		$this->add_control(
			'button_hover_color',
			[
				'label' => esc_html__( 'Icon Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .shuriken-fc-btn:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}} .shuriken-fc-btn:hover svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_hover_background',
				'label' => esc_html__( 'Background', 'shuriken-elements' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .shuriken-fc-btn:hover',
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'label' => esc_html__( 'Border', 'shuriken-elements' ),
				'selector' => '{{WRAPPER}} .shuriken-fc-btn',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'shuriken-elements' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .shuriken-fc-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'label' => esc_html__( 'Box Shadow', 'shuriken-elements' ),
				'selector' => '{{WRAPPER}} .shuriken-fc-btn',
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
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .shuriken-fc-btn' => '--shuriken-fc-icon-size: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .shuriken-fc-btn svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
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
					'{{WRAPPER}} .shuriken-fc-badge' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'badge_text_color',
			[
				'label' => esc_html__( 'Text Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .shuriken-fc-badge' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'badge_typography',
				'selector' => '{{WRAPPER}} .shuriken-fc-badge',
			]
		);

		$this->add_responsive_control(
			'badge_position_offset',
			[
				'label' => esc_html__( 'Badge Offset', 'shuriken-elements' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .shuriken-fc-badge' => 'top: {{TOP}}{{UNIT}}; right: {{RIGHT}}{{UNIT}};',
				],
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

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Visibility Classes
		$visibility_classes = [];
		if ( $settings['show_desktop'] !== 'yes' ) $visibility_classes[] = 'shuriken-fc-hide-desktop';
		if ( $settings['show_tablet'] !== 'yes' )  $visibility_classes[] = 'shuriken-fc-hide-tablet';
		if ( $settings['show_mobile'] !== 'yes' )  $visibility_classes[] = 'shuriken-fc-hide-mobile';

        $pos_class = 'shuriken-fc-pos-' . $settings['button_position'];
        
        $cart_count = 0;
        $cart_url = '#';
        if ( class_exists( 'WooCommerce' ) ) {
            $cart_count = WC()->cart->get_cart_contents_count();
            $cart_url = wc_get_cart_url();
        }

        $cart_class = 'shuriken-fc-btn';
        if ( $settings['cart_action'] === 'sidebar' ) {
            $cart_url = 'javascript:void(0);';
            $cart_class .= ' shuriken-mbm-trigger-sidebar';
        } elseif ( $settings['cart_action'] === 'drawer' ) {
            $cart_url = 'javascript:void(0);';
            $cart_class .= ' shuriken-mbm-trigger-drawer';
        }

		?>
		<div class="shuriken-floating-cart-wrapper <?php echo esc_attr( implode( ' ', $visibility_classes ) . ' ' . $pos_class ); ?>">
			<a href="<?php echo esc_url( $cart_url ); ?>" class="<?php echo esc_attr($cart_class); ?>">
                <?php Icons_Manager::render_icon( $settings['cart_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                <span class="shuriken-fc-badge shuriken-mbm-cart-badge"><?php echo esc_html($cart_count); ?></span>
            </a>
		</div>

        <?php 
        // Render Sidebar or Drawer Structure if needed
        if ( in_array( $settings['cart_action'], ['sidebar', 'drawer'] ) ) {
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
                    if ( class_exists( 'WooCommerce' ) ) {
                        \ShurikenElements\Class_Shuriken_WooCommerce::instance()->render_cart_content();
                    } 
                    ?>
                </div>
            </div>
            <?php
        }
	}
}
