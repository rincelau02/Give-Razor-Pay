<?php
/**
 * Get payment method label.
 *
 * @since 1.0
 * @return string
 */
function give_razorpay_get_payment_method_label() {
	return ( give_get_option( 'razorpay_payment_method_label', false ) ? give_get_option( 'razorpay_payment_method_label', '' ) : __( 'Razorpay', 'give-razorpay' ) );
}


/**
 * Check if sandbox mode is enabled or disabled.
 *
 * @since 1.0
 * @return bool
 */
function give_razorpay_is_test_mode_enabled() {
	return give_is_test_mode();
}


/**
 * Get razorpay merchant credentials.
 *
 * @since 1.0
 * @return array
 */
function give_razorpay_get_merchant_credentials() {
	$credentials = array(
		'merchant_key_id'     => give_get_option( 'razorpay_test_merchant_key_id', '' ),
		'merchant_secret_key' => give_get_option( 'razorpay_test_merchant_secret_key', '' ),
	);

	if ( ! give_razorpay_is_test_mode_enabled() ) {
		$credentials = array(
			'merchant_key_id'     => give_get_option( 'razorpay_live_merchant_key_id', '' ),
			'merchant_secret_key' => give_get_option( 'razorpay_live_merchant_secret_key', '' ),
		);
	}

	return $credentials;

}

/**
 * Check if the Razorpay payment gateway is active or not.
 *
 * @since 1.0
 * @return bool
 */
function give_razorpay_is_active() {
	$give_settings = give_get_settings();
	$is_active     = false;

	if (
		array_key_exists( 'razorpay', $give_settings['gateways'] )
		&& ( 1 == $give_settings['gateways']['razorpay'] )
	) {
		$is_active = true;
	}

	return $is_active;
}


/**
 * Get razorpay api object
 *
 * @since 1.1.0
 *
 * @return Razorpay\Api\Api|null $api
 */
function give_razorpay_get_api() {
	$merchant = give_razorpay_get_merchant_credentials();

	try {
		// Use your key_id and key secret.
		$api = new \Razorpay\Api\Api( $merchant['merchant_key_id'], $merchant['merchant_secret_key'] );
	} catch ( Exception $e ) {
		$api = null;
	}

	return $api;
}

/**
 * Verify payment.
 *
 * @since  1.1.0
 * @access public
 *
 * @param int   $form_id
 * @param array $razorpay_response
 *
 * @return bool
 */
function give_razorpay_validate_payment( $form_id, $razorpay_response ) {


	if (
		empty( $razorpay_response['razorpay_payment_id'] ) ||
		empty( $razorpay_response['razorpay_signature'] )
	) {
		return false;
	}

	// Setup Razorpay API.
	$api = give_razorpay_get_api();

	/* @var  \Razorpay\Api\Utility $utility */
	$utility = new \Razorpay\Api\Utility();

	// Verify response signature.
	try {

		$utility->verifyPaymentSignature( $razorpay_response );
	} catch ( Exception $e ) {

		// Record error.
		give_record_gateway_error(
			__( 'Razorpay Error', 'give-razorpay' ),
			__( 'Transaction Failed.', 'give-razorpay' )
			. '<br><br>' . sprintf( esc_attr__( 'Error Detail: %s', 'give-razorpay' ), '<br>' . print_r( $e->getMessage(), true ) )
			. '<br><br>' . sprintf( esc_attr__( 'Razorpay Response: %s', 'give-razorpay' ), '<br>' . print_r( $razorpay_response, true ) )
		);

		give_set_error( 'give-razorpay', __( 'An error occurred while processing your payment. Please try again.', 'give-razorpay' ) );

		return false;
	}

	return true;
}
