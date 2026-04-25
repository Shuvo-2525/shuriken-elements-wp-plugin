<?php
namespace ShurikenElements\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Popup Checkout Widget.
 *
 * Elementor widget that inserts a popup checkout modal.
 *
 * @since 1.0.0
 */
class Popup_Checkout extends Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'shuriken-popup-checkout';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Popup Checkout', 'shuriken-elements' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-checkout';
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
		return [ 'shuriken-popup-checkout' ];
	}

	/**
	 * Get widget scripts dependencies.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return [ 'shuriken-popup-checkout' ];
	}

	/**
	 * Register widget controls.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {

		/* ----------------------------------------------------------------------
		 * Content Tab
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Settings', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
			'info_notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => sprintf(
					__( 'This widget outputs a hidden Popup Checkout form on this page. When a user clicks the Checkout button from the Shuriken Mobile Bottom Menu, this popup will appear instead of redirecting them. If you want to edit the checkout fields, please go to the <a href="%s" target="_blank" style="color: inherit; text-decoration: underline; font-weight: bold;">Checkout Editor</a>.', 'shuriken-elements' ),
					admin_url( 'admin.php?page=shuriken-checkout-editor' )
				),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

        $this->add_control(
			'popup_title',
			[
				'label' => esc_html__( 'Popup Title', 'shuriken-elements' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Secure Checkout', 'shuriken-elements' ),
			]
		);

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->add_control(
				'woo_missing_notice',
				[
					'type' => Controls_Manager::RAW_HTML,
					'raw' => esc_html__( 'Warning: WooCommerce is not active. The checkout form requires WooCommerce.', 'shuriken-elements' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
				]
			);
		}

		$this->end_controls_section();

		/* ----------------------------------------------------------------------
		 * Style Tab - Popup Overlay
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_overlay',
			[
				'label' => esc_html__( 'Overlay', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'overlay_color',
			[
				'label' => esc_html__( 'Overlay Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-popup-checkout-overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		/* ----------------------------------------------------------------------
		 * Style Tab - Popup Container
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
				'selector' => '.shuriken-popup-checkout-container',
			]
		);

        $this->add_responsive_control(
			'container_width',
			[
				'label' => esc_html__( 'Max Width', 'shuriken-elements' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw' ],
				'range' => [
					'px' => [
						'min' => 300,
						'max' => 1200,
					],
				],
				'selectors' => [
					'.shuriken-popup-checkout-container' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'label' => esc_html__( 'Border', 'shuriken-elements' ),
				'selector' => '.shuriken-popup-checkout-container',
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'shuriken-elements' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'.shuriken-popup-checkout-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_box_shadow',
				'label' => esc_html__( 'Box Shadow', 'shuriken-elements' ),
				'selector' => '.shuriken-popup-checkout-container',
			]
		);

		$this->end_controls_section();

        /* ----------------------------------------------------------------------
		 * Style Tab - Header & Close
		 * ---------------------------------------------------------------------- */
		$this->start_controls_section(
			'section_style_header',
			[
				'label' => esc_html__( 'Header & Close Button', 'shuriken-elements' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

        $this->add_control(
			'header_bg',
			[
				'label' => esc_html__( 'Header Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-popup-checkout-header' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Title Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-popup-checkout-title' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Title Typography', 'shuriken-elements' ),
				'selector' => '.shuriken-popup-checkout-title',
			]
		);

        $this->add_control(
			'close_color',
			[
				'label' => esc_html__( 'Close Button Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => [
					'.shuriken-popup-close' => 'color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'close_hover_bg',
			[
				'label' => esc_html__( 'Close Hover Background', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-popup-close:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

        $this->add_control(
			'close_hover_color',
			[
				'label' => esc_html__( 'Close Hover Color', 'shuriken-elements' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.shuriken-popup-close:hover' => 'color: {{VALUE}};',
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
        
        $is_elementor_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $style_display = $is_elementor_editor ? 'display: flex; opacity: 1; position: relative; transform: none; top: 0; left: 0; margin-bottom: 20px;' : '';
        $overlay_style = $is_elementor_editor ? 'display: none;' : '';

        // Force WooCommerce checkout scripts if not on checkout page to make checkout fully robust
        if ( class_exists( 'WooCommerce' ) && ! is_checkout() && ! $is_elementor_editor ) {
            wp_enqueue_script( 'wc-checkout' );
            wp_enqueue_script( 'wc-country-select' );
            wp_enqueue_script( 'wc-address-i18n' );
            wp_enqueue_script( 'selectWoo' );
            wp_enqueue_style( 'select2' );
        }
		?>
		
        <div class="shuriken-popup-checkout-overlay" style="<?php echo esc_attr($overlay_style); ?>"></div>

        <div class="shuriken-popup-checkout-container" style="<?php echo esc_attr($style_display); ?>">
            <div class="shuriken-popup-checkout-header">
                <h2 class="shuriken-popup-checkout-title"><?php echo esc_html( $settings['popup_title'] ); ?></h2>
                <button class="shuriken-popup-close" aria-label="<?php esc_attr_e( 'Close', 'shuriken-elements' ); ?>">&times;</button>
            </div>
            <div class="shuriken-popup-checkout-body">
                <?php 
                if ( class_exists( 'WooCommerce' ) ) {
                    // Render checkout form. The WooCommerce shortcode handles the complex output.
                    echo do_shortcode( '[woocommerce_checkout]' );
                } else {
                    echo '<p>' . esc_html__( 'WooCommerce is not active.', 'shuriken-elements' ) . '</p>';
                }
                ?>
            </div>
        </div>

		<?php
	}
}
