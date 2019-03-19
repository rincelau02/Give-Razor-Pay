<?php
/*
* RECURRING ADDED BY WPBUFFS
* 5/29/18
*
*/
function give_razor_plan() {

	$api    = give_razorpay_get_api();
	
	// Customers
	$customer_name = $_REQUEST['donor_name'];
	$customer_email = $_REQUEST['donor_email'];
	$recurring = $_REQUEST['recurring'];
	$recurring_type = '';
	$amount = floatval(preg_replace("/[^-0-9\.]/","",$_REQUEST['amount'])) * 100;
	$customer_id = '';
	$donations = give_get_payments();
	$subscriptions = $api->subscription->all();

	foreach ($subscriptions->items as $key => $sub_item) {
		$customer = $api->customer->fetch($sub_item->customer_id);
		if($customer->email == $customer_email){
			$customer_id = $sub_item->customer_id;
			break;
		}
	} 

	/*	
	foreach($donations as $donation) {
		$custom_field = give_get_meta( $donation->ID );
		if($custom_field['_give_payment_donor_email'][0] == $customer_email){
			if(isset($custom_field['razorpay_donation_response'])){
				$subscription_id = unserialize($custom_field['razorpay_donation_response'][0]);
				if($subscription_id['razorpay_subscription_id']){
					try {
						$donation_data = $api->subscription->fetch($subscription_id['razorpay_subscription_id']);
						$customer_id = $donation_data->customer_id;
					} catch (Exception $e) {
						$customer_id = '';
					}					
				}
				break;
			}
		}
	}	
	*/

	if($recurring == 'month'){
		$recurring_type = 'monthly';		
		if($_REQUEST['total_count']){
			$total_count = $_REQUEST['total_count'];
		} else {
			$total_count = 120;
		}
		$interval = 1;
	} else if ($recurring == 'day') {
		$recurring_type = 'daily';
		if($_REQUEST['total_count']){
			$total_count = $_REQUEST['total_count'];
		} else {
			$total_count = 500;
		}
		$interval = 7;
	} else if ($recurring == 'year') {
		$recurring_type = 'yearly';
		if($_REQUEST['total_count']){
			$total_count = $_REQUEST['total_count'];
		} else {
			$total_count = 10;
		}
		$interval = 1;
	} else if ($recurring == 'week') {
		$recurring_type = 'weekly';
		if($_REQUEST['total_count']){
			$total_count = $_REQUEST['total_count'];
		} else {
			$total_count = 200;
		}
		$interval = 1;
	}
	

	if($customer_id){
		//$customer = $api->customer->edit(array('name' => $customer_name, 'email' => $customer_email)); // Edits customer
	} else {
		$customer = $api->customer->create(array('name' => $customer_name, 'email' => $customer_email)); // Creates customer
		$customer_id = $customer->id;
		//update_post_meta( $payment, 'customer_id', $customer->id );
	}

	//$customer = $api->customer->fetch($customerId); // Returns a particular customer
	
	// Subscriptions
	$plan          = $api->plan->create(array('period' => $recurring_type, 'interval' => $interval, 'item' => array('name' => $recurring_type .' plan', 'description' => $customer_name. ' '.$recurring_type.' plan', 'amount' => $amount, 'currency' => 'INR')));

	//$plans         = $api->plan->all();
	$today = strtotime("+15 minutes", strtotime(date('y-m-d h:i:s A')) );
	$subscription  = $api->subscription->create(
						array('plan_id' => $plan->id, 
						  	  'customer_notify' => 1, 
						  	  'customer_id' => $customer_id,
						  	  'total_count' => $total_count
						)
					 );
	
	//$subscription  = $api->subscription->fetch('sub_82uBGfpFK47AlA');
	//$subscriptions = $api->subscription->all();
	//$subscription  = $api->subscription->fetch('sub_82uBGfpFK47AlA')->cancel();
	//$addon         = $api->subscription->fetch('sub_82uBGfpFK47AlA')->createAddon(array('item' => array('name' => 'Extra Chair', 'amount' => 30000, 'currency' => 'INR'), 'quantity' => 2));
	//$addon         = $api->addon->fetch('ao_8nDvQYYGQI5o4H');
	//$addon         = $api->addon->fetch('ao_8nDvQYYGQI5o4H')->delete();

	setcookie("subscription_id", $subscription->id, time() + 3600, "/");
	echo $subscription->id;
	die();
}
add_action( 'wp_ajax_give_razor_plan', 'give_razor_plan' );
add_action( 'wp_ajax_nopriv_give_razor_plan', 'give_razor_plan' );