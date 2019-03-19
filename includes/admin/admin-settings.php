<?php

/**
 * Class Give_Razorpay_Gateway_Settings
 *
 * @since 1.0
 */
class Give_Razorpay_Gateway_Settings {
	/**
	 * @since  1.0
	 * @access static
	 * @var Give_Razorpay_Gateway_Settings $instance
	 */
	static private $instance;

	/**
	 * @since  1.0
	 * @access private
	 * @var string $section_id
	 */
	private $section_id;

	/**
	 * @since  1.0
	 * @access private
	 * @var string $section_label
	 */
	private $section_label;

	/**
	 * Give_Razorpay_Gateway_Settings constructor.
	 */
	private function __construct() {
	}

	/**
	 * get class object.
	 *
	 * @since 1.0
	 * @return Give_Razorpay_Gateway_Settings
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0
	 */
	public function setup_hooks() {
		$this->section_id    = 'razorpay';
		$this->section_label = __( 'Razorpay', 'give-razorpay' );

		// Add payment gateway to payment gateways list.
		add_filter( 'give_payment_gateways', array( $this, 'add_gateways' ) );

		if ( is_admin() ) {

			// Add section to payment gateways tab.
			add_filter( 'give_get_sections_gateways', array( $this, 'add_section' ) );

			// Add section settings.
			add_filter( 'give_get_settings_gateways', array( $this, 'add_settings' ) );
		}
	}

	/**
	 * Add payment gateways to gateways list.
	 *
	 * @since 1.0
	 *
	 * @param array $gateways array of payment gateways.
	 *
	 * @return array
	 */
	public function add_gateways( $gateways ) {
		$gateways[ $this->section_id ] = array(
			'admin_label'    => $this->section_label,
			'checkout_label' => give_razorpay_get_payment_method_label(),
		);

		return $gateways;
	}

	/**
	 * Add setting section.
	 *
	 * @since 1.0
	 *
	 * @param array $sections Array of section.
	 *
	 * @return array
	 */
	public function add_section( $sections ) {
		$sections[ $this->section_id ] = $this->section_label;

		return $sections;
	}

	/**
	 * Add plugin settings.
	 *
	 * @since 1.0
	 *
	 * @param array $settings Array of setting fields.
	 *
	 * @return array
	 */
	public function add_settings( $settings ) {
		$current_section = give_get_current_setting_section();

		if ( $this->section_id == $current_section ) {
			$settings = array(
				array(
					'id'   => 'give_razorpay_payments_setting',
					'type' => 'title',
				),
				array(
					'title' => __( 'Live Key ID', 'give-razorpay' ),
					'id'    => 'razorpay_live_merchant_key_id',
					'type'  => 'api_key',
					'desc'  => __( 'Required live merchant key id provided by Razorpay.', 'give-razorpay' ),
				),
				array(
					'title' => __( 'Live Secret Key', 'give-razorpay' ),
					'id'    => 'razorpay_live_merchant_secret_key',
					'type'  => 'api_key',
					'desc'  => __( 'Required live merchant secret key provided by Razorpay.', 'give-razorpay' ),
				),
				array(
					'title' => __( 'Test Key ID', 'give-razorpay' ),
					'id'    => 'razorpay_test_merchant_key_id',
					'type'  => 'api_key',
					'desc'  => __( 'Required test merchant key id provided by Razorpay.', 'give-razorpay' ),
				),
				array(
					'title' => __( 'Test Secret Key', 'give-razorpay' ),
					'id'    => 'razorpay_test_merchant_secret_key',
					'type'  => 'api_key',
					'desc'  => __( 'Required test merchant secret key provided by Razorpay.', 'give-razorpay' ),
				),
				array(
					'title'   => __( 'Payment Method Label', 'give-razorpay' ),
					'id'      => 'razorpay_payment_method_label',
					'type'    => 'text',
					'default' => give_razorpay_get_payment_method_label(),
					'desc'    => __( 'Payment method label will be appear on frontend.', 'give-razorpay' ),
				),
				array(
					'title' => __( 'Popup Image', 'give-razorpay' ),
					'id'    => 'razorpay_popup_image',
					'type'  => 'file',
					'desc'  => __( 'This image will be use to show on Razorpay popup. Image size should be 80px * 80px', 'give-razorpay' ),
				),
				array(
					'title' => __( 'Popup Theme Color', 'give-razorpay' ),
					'id'    => 'razorpay_popup_theme_color',
					'type'  => 'colorpicker',
					'desc'  => __( 'This color will be use to set theme color for Razorpay popup', 'give-razorpay' ),
					'default' => '#2bc253'
				),
				array(
					'title'   => __( 'Phone Field', 'give-razorpay' ),
					'id'      => 'razorpay_phone_field',
					'type'    => 'radio_inline',
					'desc'    => __( 'Enable this setting if you want to show phone field on the donation form. This will pre-fill the field within the Razorpay popup.', 'give-razorpay' ),
					'default' => 'enabled',
					'options' => array(
						'enabled'  => __( 'Enabled', 'give-razorpay' ),
						'disabled' => __( 'Disabled', 'give-razorpay' ),
					),
				),
				array(
					'title'       => __( 'Collect Billing Details', 'give-razorpay' ),
					'id'          => 'razorpay_billing_details',
					'type'        => 'radio_inline',
					'options'     => array(
						'enabled'  => esc_html__( 'Enabled', 'give-razorpay' ),
						'disabled' => esc_html__( 'Disabled', 'give-razorpay' ),
					),
					'default'     => 'disabled',
					'description' => __( 'This option will enable the billing details section for Razorpay which requires the donor\'s address to complete the donation. These fields are not required by Razorpay to process the transaction, but you may have the need to collect the data.', 'give-razorpay' ),
				),
				array(
					'id'   => 'give_razorpay_payments_setting',
					'type' => 'sectionend',
				),
			);
		}// End if().

		return $settings;
	}
}

Give_Razorpay_Gateway_Settings::get_instance()->setup_hooks();
