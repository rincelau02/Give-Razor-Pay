/**
 * Give - Razorpay Popup Checkout JS
 */
var give_global_vars, give_razorpay_vars;

/**
 * On document ready setup Razorpay events.
 */
jQuery(document).ready(function ($) {
	// Cache donation button title early to reset it if razorpay checkout popup close.
	var donate_button_titles = [], razorpay_handler = [];

	$('input[type="submit"].give-submit').each(function (index, $submit_btn) {
		$submit_btn = $($submit_btn);
		var $form   = $submit_btn.parents('form'),
			form_id = $('input[name="give-form-id"]', $form).val();

		donate_button_titles[form_id] = $submit_btn.val();
	});

	/**
	 * On form submit prevent submission for Razorpay only.
	 */
	$('form[id^=give-form]').on('submit', function (e) {

		// Form that has been submitted.
		var $form = $(this);

		// Check that Razorpay is indeed the gateway chosen.
		if (
			!give_is_razorpay_gateway_selected($form)
			|| ( typeof $form[0].checkValidity === "function" && $form[0].checkValidity() === false )
		) {
			return true;
		}

		e.preventDefault();

		return false;
	});

	/**
	 * When the submit button is clicked.
	 */
	$(document).on('give_form_validation_passed', function (e) {

		var $form          = $(e.target),
			$submit_button = $form.find('input[type="submit"]'),
			form_id        = $('input[name="give-form-id"]', $form).val(),
			donor_name     = ( $form.find('input[name="give_first"]').val().trim() + ' ' + $form.find('input[name="give_last"]').val().trim() ).trim(),
			donor_email    = $form.find('input[name="give_email"]').val(),
			donor_contact  = $form.find('input[name="give_razorpay_phone"]').val(),
			form_name      = $form.find('input[name="give-form-title"]').val(),
			amount         = $form.find('.give-final-total-amount').data('total'),
			currency       = Give.form.fn.getInfo('currency_code',$form);


		// Check that Razorpay is indeed the gateway chosen.
		if (
			!give_is_razorpay_gateway_selected($form)
			|| ( ( typeof $form[0].checkValidity === "function" && $form[0].checkValidity() === false ) )
		) {
			return false;
		}

		$.post(give_razorpay_vars.setup_order_url, { form  : form_id, amount: amount, currency: currency})
			.done(function (response) {
				if( ! response.success ) {
					return false;
				}

				// Increase razorpay's z-index to appear above Give's modal.
				$('.razorpay-container').css('z-index', '2147483543');

				// Set razorpay handler for form.
				if ('undefined' != razorpay_handler[form_id]) {
					razorpay_handler[form_id] = new Razorpay({
						'key'   : give_razorpay_vars.merchant_key_id,
						'amount': response.data.amount,
						'name'  : form_name,
						'order_id': response.data.order_id,
						'image' : give_razorpay_vars.popup.image || '',
						'handler': function (response) {
							// Insert the token into the form so it gets submitted to the server.
							$form.prepend('<input type="hidden" name="give_razorpay_response" value="' + encodeURI( JSON.stringify( response ) ) + '" />');

							// Remove loading animations.
							$form.find('.give-loading-animation').hide();

							// Re-enable submit button and add back text.
							$submit_button.prop('disabled', false).val(donate_button_titles[form_id]);

							// Submit form after charge token brought back from Razorpay.
							$form.get(0).submit();
						},

						// You can add custom data here and fields limited to 15.
						'notes': {
							'name'     : donor_name,
							'address'  : $form.find('input[name="card_address"]').val(),
							'address_2': $form.find('input[name="card_address_2"]').val(),
							'city'     : $form.find('input[name="card_city"]').val(),
							'state'    : $form.find('input[name="card_state"]').val(),
							'country'  : $form.find('input[name="billing_country"]').val(),
							'zipcode'  : $form.find('input[name="card_zip"]').val()
						},

						'prefill': {
							'name'   : donor_name,
							'email'  : donor_email,
							'contact': donor_contact
						},

						'modal': {
							'ondismiss': function () {
								// Remove loading animations.
								$form.find('.give-loading-animation').hide();

								// Re-enable submit button and add back text.
								$submit_button.prop('disabled', false).val(donate_button_titles[form_id]);
							}
						},

						'theme': {
							'color': give_razorpay_vars.popup.color
						}
					});
				}

				// Open checkout
				razorpay_handler[form_id].open({});
			})
			.fail(function () {
			})
			.always(function () {

				// Enable form submit button.
				$submit_button.prop('disabled', false);
			});

		e.preventDefault();
	});

	/**
	 * Check if razorpay gateway selected or not
	 *
	 * @param $form
	 * @returns {boolean}
	 */
	function give_is_razorpay_gateway_selected($form) {
		return ( $('input[name="give-gateway"]', $form).val() === 'razorpay' )
	}
});
