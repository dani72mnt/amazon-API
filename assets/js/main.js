jQuery(document).ready(function ($) {

	// add product to cart and redirect to cart page
	$(document).on('click', '#confirmAndContinue', function(e) {
		e.preventDefault();
		$.ajax({
			url: vishar_plugin_ajax_object.ajax_url,
			type: 'POST',
			data: {
				action: 'add_product_to_cart',
			},
			success: function(response) {
				if (response.status === 'success') {
					// Redirect to the cart page
					window.location.href = response.redirect_url;
				} else {
					// Handle error response if needed
					console.log(response.message);
				}
			},
			error: function(xhr, textStatus, errorThrown) {
				// Handle errors if needed
			}
		});
	});

	// Get Product Data from Link
	$('#getLinkFormSubmit').on('click', function(e){
		e.preventDefault();
		var form = $(this).closest("#product-link-form");
		
		var $name = form.find('input.name').val();
		var $last_name = form.find('input.last_name').val();
		var $email = form.find('input.email').val();
		var $product_link = form.find('input.product_link').val();
		var $website = form.find('select.website').val();
		var $password = form.find('input.password').val();
		var $password_repeate = form.find('input.password_repeate').val();

		$.ajax({
			url:  vishar_plugin_ajax_object.ajax_url,
			type: 'POST',
			data: {
				action: 'get_product_from_link',
				name: $name,
				last_name: $last_name,
				email: $email,
				product_link: $product_link,
				website: $website,
				password: $password,
				password_repeate: $password_repeate
			},
			success: function (response) {
				if (response.status === 'success') {
					$('#responseSection').html(response.output);
				} else {
					alertify.notify(response.message, response.status, 5);
				}
			}
		});	
	});

});