<?php
/**
 * Process Razorpay Payments.
 */

use Razorpay\Api\Api;


/**
 * Process the Razorpay payment.
 *
 * @param $donation_data
 */
function give_razorpay_process_payment( $donation_data ) {
	$razorpay_response = json_decode( urldecode( $_POST['give_razorpay_response'] ), true );
	$form_id           = absint( $donation_data['post_data']['give-form-id'] );

	// Capture Razorpay payment.
	try {
		if ( ! give_razorpay_validate_payment( $form_id, $razorpay_response ) ) {
			throw new Exception( __( 'Invalid donation', 'give-razorpay' ) );
		}
	} catch ( Exception $e ) {

		give_record_gateway_error(
			__( 'Razorpay Error', 'give-razorpay' ),
			__( 'Transaction Failed.', 'give-razorpay' )
			. '<br><br>' . sprintf( esc_attr__( 'Error Detail: %s', 'give-razorpay' ), '<br>' . print_r( $e->getMessage(), true ) )
			. '<br><br>' . sprintf( esc_attr__( 'Razorpay Response: %s', 'give-razorpay' ), '<br>' . print_r( $razorpay_response, true ) )
		);

		give_set_error( 'give-razorpay', __( 'An error occurred while processing your payment. Please try again.', 'give-razorpay' ) );

		// Problems? Send back.
		give_send_back_to_checkout();
	}

	// Successful payment?
	if ( empty( $razorpay_response['error_code '] ) ) {

		// setup the payment details
		$payment_data = array(
			'price'           => $donation_data['price'],
			'give_form_title' => $donation_data['post_data']['give-form-title'],
			'give_form_id'    => $form_id,
			'give_price_id'   => isset( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : '',
			'date'            => $donation_data['date'],
			'user_email'      => $donation_data['user_email'],
			'purchase_key'    => $donation_data['purchase_key'],
			'currency'        => give_get_currency(),
			'user_info'       => $donation_data['user_info'],
			'status'          => 'pending',
			'gateway'         => $donation_data['gateway'],
		);

		// Record the pending payment
		$payment = give_insert_payment( $payment_data );

		// Verify donation payment.
		if ( ! $payment ) {
			// Record the error.
			give_record_gateway_error(
				esc_html__( 'Payment Error', 'give-razorpay' ),
				/* translators: %s: payment data */
				sprintf(
					esc_html__( 'The payment creation failed before processing the Razorpay gateway request. Payment data: %s', 'give-razorpay' ),
					print_r( $donation_data, true )
				),
				$payment
			);

			give_set_error( 'give-razorpay', __( 'An error occurred while processing your payment. Please try again.', 'give-razorpay' ) );

			// Problems? Send back.
			give_send_back_to_checkout();
		}

		give_insert_payment_note( $payment, sprintf( __( 'Transaction Successful. Razoypay Transaction ID: %s', 'give-razorpay' ), $razorpay_response['razorpay_order_id'] ) );
		give_set_payment_transaction_id( $payment, $razorpay_response['razorpay_order_id'] );
		update_post_meta( $payment, 'razorpay_donation_response', $razorpay_response );
		update_post_meta( $payment, 'donor_phone', isset( $donation_data['post_data']['give_razorpay_phone'] ) ? $donation_data['post_data']['give_razorpay_phone'] : '' );
		give_update_payment_status( $payment, 'complete' );

		// Reset session.
		$razorpay_session_data = Give()->session->get( 'razorpay' );
		unset( $razorpay_session_data["donation_{$form_id}"] );
		Give()->session->set( 'razorpay', $razorpay_session_data );

		give_send_to_success_page();
	} else {

		// An error occurred.
		give_record_gateway_error(
			__( 'Razorpay Error', 'give-razorpay' ),
			__( 'Transaction Failed.', 'give-razorpay' ) . '<br><br>' . sprintf( esc_attr__( 'Details: %s', 'give-razorpay' ), '<br>' . print_r( $razorpay_response, true ) )
		);

		give_set_error( 'give-razorpay', sprintf( __( 'The transaction failed. Details: %s', 'give-razorpay' ), $razorpay_response ) );

		// Problems? Send back.
		give_send_back_to_checkout();
	}
}

add_action( 'give_gateway_razorpay', 'give_razorpay_process_payment' );