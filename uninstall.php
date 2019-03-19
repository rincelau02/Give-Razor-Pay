<?php
/**
 * Uninstall Razorpay.
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Give core settings.
$give_settings = give_get_settings();

// List of plugin settings.
$plugin_settings = array(
	'razorpay_payment_method_label',
	'razorpay_popup_image',
	'razorpay_popup_theme_color',
	'razorpay_live_merchant_key_id',
	'razorpay_live_merchant_secret_key',
	'razorpay_test_merchant_key_id',
	'razorpay_test_merchant_secret_key',
	'razorpay_phone_field',
	'razorpay_billing_details',
);

// Unset all plugin settings.
foreach ( $plugin_settings as $setting ) {
	if( isset( $give_settings[ $setting ] ) ) {
		unset( $give_settings[ $setting ] );
	}
}

// Remove payumoney from active gateways list.
if( isset( $give_settings['gateways']['razorpay'] ) ) {
	unset( $give_settings['gateways']['razorpay'] );
}


// Update settings.
update_option( 'give_settings', $give_settings );