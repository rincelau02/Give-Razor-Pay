<?php
/**
 * Show transaction ID under donation meta.
 *
 * @since 1.0
 *
 * @param $transaction_id
 */
function give_razorpay_link_transaction_id( $transaction_id ) {
	$razorpay_trans_url = 'https://dashboard.razorpay.com/#/app/orders/';
	echo sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $razorpay_trans_url.$transaction_id, $transaction_id );
}

add_filter( 'give_payment_details_transaction_id-razorpay', 'give_razorpay_link_transaction_id', 10, 2 );

/**
 * Add razorpay donor detail to "Donor Detail" metabox
 *
 * @since 1.0
 *
 * @param $payment_id
 *
 * @return bool
 */
function give_razorpay_view_details( $payment_id ) {
	// Bailout.
	if ( 'razorpay' !== give_get_payment_gateway( $payment_id ) ) {
		return false;
	}

	$donor_phone = get_post_meta( absint( $_GET['id'] ), 'donor_phone', true );
	
	// Check if contact exit in razorpay response.
	if ( empty( $donor_phone ) ) {
		return false;
	}
	?>
	<div class="column">
		<p>
			<strong><?php _e( 'Phone', 'give-razorpay' ); ?></strong><br>
			<?php echo $donor_phone; ?>
		</p>
	</div>
	<?php
}

add_action( 'give_payment_view_details', 'give_razorpay_view_details' );