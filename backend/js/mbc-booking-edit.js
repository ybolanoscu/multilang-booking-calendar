jQuery( "#end").on('change', function () {
	mbc_checkDates();
});
jQuery( "#start").on('change', function () {
	mbc_checkDates();
});

jQuery(document).on('click', '#postAbcBooking', function(){
	
	jQuery('#mbc-booking-form').validate({
	    rules: {
	        email: {
	            required: {
			        depends: function(element) {
			          return jQuery("#radio-yes").is(":checked");
			        }
			    },
	            email: {
			        depends: function(element) {
			          return jQuery("#radio-yes").is(":checked");
			        }
			    }
	        }
	    },
		submitHandler: function (form) {
			form.submit();
			}
	});	
    
});

function mbc_checkDates() {
	var extrasList = jQuery("input[name=mbc-extras-checkbox]:checked").map(function () {return this.value;}).get().join(",");
	if( jQuery("#start").val() && jQuery("#end").val() ){
		jQuery('#mbc_dateStatus').html('<span class="uk-text-muted"><i><br/>Loading...</i></span>');
		var from = jQuery( "#start").val();
		var to = jQuery( "#end").val();
		var persons = jQuery( "#persons").val();
		dataAvailability = {
			action: 'mbc_booking_checkDates',
			mbc_bookings_nonce: ajax_mbc_bookings.mbc_bookings_nonce,
			from: from,
			to: to,
			calId: ajax_mbc_bookings.calendar_id,
			bookingId: ajax_mbc_bookings.booking_id,
			persons: persons
		};
		jQuery.post(ajax_mbc_bookings.ajaxurl, dataAvailability, function (response){
				jQuery('#mbc_dateStatus').html("<br/>"+response);
			});
		return false;
	}	
}