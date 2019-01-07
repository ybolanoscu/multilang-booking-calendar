function getLastDayDate(someDate){ // Returns the last day of the month for someDate
	var y = someDate.getFullYear();
	var m = someDate.getMonth();
	var d = new Date(y, m + 1, 0 );
	return d;
}

function getDateYYYYMMDD(someDate){
	var dd = someDate.getDate();
	if (dd < 10){
		dd = '0' + dd;
	}
	var mm = someDate.getMonth() + 1;
	if (mm < 10){
		mm = '0' + mm;
	}
	var yyyy = someDate.getFullYear();
	return yyyy + '-'+ mm + '-'+ dd;
}

function setDateYYYYMMDD(someDate){
	var tempDate = someDate.split("-");
	var newDate = new Date(tempDate[0], tempDate[1] - 1, tempDate[2]);
	return newDate;
}

// Single Calendar

jQuery('.mbc-singlecalendar').on('click', '.mbc-single-button-right', function(){
	var uniqid = jQuery(this).data('id');
	var calendar = jQuery(this).data('calendar');
	var abcSingleCheckin = jQuery('#mbc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var abcSingleCheckout = jQuery('#mbc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	var month = jQuery('#mbc_singlecalendar_' + uniqid).data('month-' + uniqid);
	month = month + 1;
	jQuery('#singlecalendar-month-' + uniqid).hide();
	jQuery('#mbc-calendar-days-' + uniqid).hide();
	jQuery('.mbc-single-button-right').attr('disabled',true);
	jQuery('#mbc_single_loading-' + uniqid).show();
	dateData = {
			action: 'mbc_booking_getMonth',
			mbc_nonce: ajax_mbc_booking_SingleCalendar.mbc_nonce,
			month: month
	}
	jQuery.post(ajax_mbc_booking_SingleCalendar.ajaxurl, dateData, function (response){
		jQuery('#singlecalendar-month-' + uniqid).html(response);
	});
	data = {
		action: 'mbc_booking_getSingleCalendar',
		mbc_nonce: ajax_mbc_booking_SingleCalendar.mbc_nonce,
		month: month, 
		uniqid: uniqid, 
		calendar: calendar,
		start: abcSingleCheckin,
		end: abcSingleCheckout
	};
	jQuery.post(ajax_mbc_booking_SingleCalendar.ajaxurl, data, function (response){
		jQuery('#mbc-calendar-days-' + uniqid).html(response);
		jQuery('#mbc-calendar-days-' + uniqid).show();
		jQuery('#singlecalendar-month-' + uniqid).show();
		jQuery('.mbc-single-button-right').attr('disabled',false);
		jQuery('#mbc_single_loading-' + uniqid).hide();
	});
	jQuery('#mbc_singlecalendar_' + uniqid).data('month-' + uniqid, month);
	return false;	
});

jQuery('.mbc-singlecalendar').on('click', '.mbc-single-button-left', function(){
	var uniqid = jQuery(this).data('id');
	var calendar = jQuery(this).data('calendar');
	var abcSingleCheckin = jQuery('#mbc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var abcSingleCheckout = jQuery('#mbc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	var month = jQuery('#mbc_singlecalendar_' + uniqid).data('month-' + uniqid);
	month = month - 1;
	jQuery('#singlecalendar-month-' + uniqid).hide();
	jQuery('#mbc-calendar-days-' + uniqid).hide();
	jQuery('.mbc-single-button-left').attr('disabled',true);
	jQuery('#mbc_single_loading-' + uniqid).show();
	dateData = {
			action: 'mbc_booking_getMonth',
			mbc_nonce: ajax_mbc_booking_SingleCalendar.mbc_nonce,
			month: month
	}
	jQuery.post(ajax_mbc_booking_SingleCalendar.ajaxurl, dateData, function (response){
		jQuery('#singlecalendar-month-' + uniqid).html(response);
	});
	data = {
		action: 'mbc_booking_getSingleCalendar',
		mbc_nonce: ajax_mbc_booking_SingleCalendar.mbc_nonce,
		month: month, 
		uniqid: uniqid, 
		calendar: calendar,
		start: abcSingleCheckin,
		end: abcSingleCheckout
	};
	jQuery.post(ajax_mbc_booking_SingleCalendar.ajaxurl, data, function (response){
		jQuery('#mbc-calendar-days-' + uniqid).html(response);
		jQuery('#mbc-calendar-days-' + uniqid).show();
		jQuery('#singlecalendar-month-' + uniqid).show();
		jQuery('.mbc-single-button-left').attr('disabled',false);
		jQuery('#mbc_single_loading-' + uniqid).hide();
	});
	jQuery('#mbc_singlecalendar_' + uniqid).data('month-' + uniqid, month);
	return false;	
});	

jQuery('.mbc-singlecalendar').on('click', '.mbc-date-selector', function(){
	var uniqid = jQuery(this).data('id');
	var calendar = jQuery(this).data('calendar');
	var date = jQuery(this).data('date');
	var tempDate = setDateYYYYMMDD(date);
	var abcSingleCheckin = jQuery('#mbc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var abcSingleCheckout = jQuery('#mbc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	var lastDay = getDateYYYYMMDD(getLastDayDate(tempDate));
	if(abcSingleCheckin == 0){
		abcSingleCheckin = date;
		abcSingleCheckout = 0;
		jQuery(this).addClass('mbc-date-selected');
	} else if (abcSingleCheckin != 0 && abcSingleCheckout == 0 && date > abcSingleCheckin){
		var tempDate = setDateYYYYMMDD(abcSingleCheckin);
		while(getDateYYYYMMDD(tempDate) <= date){
			if(jQuery('#mbc-day-' + uniqid + getDateYYYYMMDD(tempDate)).hasClass('mbc-booked')){
				break;
			}
			jQuery('#mbc-day-' + uniqid + getDateYYYYMMDD(tempDate)).addClass('mbc-date-selected');
			tempDate.setDate(tempDate.getDate() + 1);
		}
		tempDate.setDate(tempDate.getDate() - 1);
		abcSingleCheckout = getDateYYYYMMDD(tempDate);
	} else if (abcSingleCheckin > date 
			|| (abcSingleCheckin != 0 && abcSingleCheckout != 0 && date >= abcSingleCheckout)
			|| (abcSingleCheckin != 0 && abcSingleCheckout != 0 && date >= abcSingleCheckin)
			){
		var tempDate = setDateYYYYMMDD(abcSingleCheckin);
		jQuery('.mbc-date-selector').removeClass('mbc-date-selected');
		jQuery(this).addClass('mbc-date-selected');
		abcSingleCheckin = date;
		abcSingleCheckout = 0;
	}	
	data = {
		action: 'mbc_booking_setDataRange',
		mbc_nonce: ajax_mbc_booking_SingleCalendar.mbc_nonce,
		start: abcSingleCheckin, 
		end: abcSingleCheckout, 
		uniqid: uniqid, 
		calendar: calendar
	};
	jQuery.post(ajax_mbc_booking_SingleCalendar.ajaxurl, data, function (response){
		jQuery('#mbc-booking-' + uniqid).html(response);
	});
	jQuery('#mbc_singlecalendar_' + uniqid).data('checkin-' + uniqid, abcSingleCheckin);
	jQuery('#mbc_singlecalendar_' + uniqid).data('checkout-' + uniqid, abcSingleCheckout);
	return false;	
});

jQuery('.mbc-singlecalendar').on('mouseenter', '.mbc-date-selector', function(){
	var uniqid = jQuery(this).data('id');
	var date = jQuery(this).data('date');
	var dateDate = setDateYYYYMMDD(date);
	var abcSingleCheckin = jQuery('#mbc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var abcSingleCheckout = jQuery('#mbc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	if(date > abcSingleCheckin && abcSingleCheckin != 0 && abcSingleCheckout == 0){
		var tempDate = setDateYYYYMMDD(abcSingleCheckin);
		while(tempDate <= dateDate){
			if(jQuery('#mbc-day-'+ uniqid + getDateYYYYMMDD(tempDate)).hasClass('mbc-booked')){
				break;
			}
			jQuery('#mbc-day-'+ uniqid + getDateYYYYMMDD(tempDate)).addClass('mbc-date-selected');
			tempDate.setDate(tempDate.getDate() + 1);
		}
	}
});

jQuery('.mbc-singlecalendar').on('mouseleave', '.mbc-date-selector', function(){
	var uniqid = jQuery(this).data('id');
	var date = jQuery(this).data('date');
	var abcSingleCheckin = jQuery('#mbc_singlecalendar_' + uniqid).data('checkin-' + uniqid);
	var checkinDate = setDateYYYYMMDD(abcSingleCheckin);
	var abcSingleCheckout = jQuery('#mbc_singlecalendar_' + uniqid).data('checkout-' + uniqid);
	if(date > abcSingleCheckin && abcSingleCheckin != 0 && abcSingleCheckout == 0){
		var tempDate = setDateYYYYMMDD(date);
		while(tempDate > checkinDate){
			jQuery('#mbc-day-'+ uniqid + getDateYYYYMMDD(tempDate)).removeClass('mbc-date-selected');
			tempDate.setDate(tempDate.getDate() - 1);
		}
	}
});	

// Calendar overview
jQuery('.mbc-calendar-overview').on('click', '.mbc-overview-button', function(){
	var uniqid = jQuery(this).data('id');
	var overviewMonth = jQuery(this).data('month');
	var overviewYear = jQuery(this).data('year');
	jQuery('.mbc-overview-button').attr('disabled',true);
	jQuery('.abcMonth').attr('disabled',true);
	jQuery('.abcYear').attr('disabled',true);
	data = {
		action: 'mbc_booking_getCalOverview',
		mbc_nonce: ajax_mbc_booking_calOverview.mbc_nonce,
		month: overviewMonth,
		year: overviewYear,
		uniqid: uniqid
	};
	
	jQuery.post(ajax_mbc_booking_calOverview.ajaxurl, data, function (response){
		jQuery('#mbc-calendaroverview-' + uniqid).html(response);
		jQuery('.mbc-overview-button').attr('disabled',false);
		jQuery('.abcMonth').attr('disabled',false);
		jQuery('.abcYear').attr('disabled',false);
	});
	
	return false;	
});	

jQuery( '.mbc-calendar-overview' ).on('change', "select[name='abcMonth']", function () {
	var uniqid = jQuery(this).data('id');
	var overviewMonth = jQuery( "select[name='abcMonth']").val();
	var overviewYear = jQuery( "select[name='abcYear']").val();
	jQuery('.abcMonth').attr('disabled',true);
	jQuery('.abcYear').attr('disabled',true);
	jQuery('.mbc-button-rl').attr('disabled',true);
	data = {
		action: 'mbc_booking_getCalOverview',
		mbc_nonce: ajax_mbc_booking_calOverview.mbc_nonce,
		month: overviewMonth,
		year: overviewYear,
		uniqid: uniqid
	};
	jQuery.post(ajax_mbc_booking_calOverview.ajaxurl, data, function (response){
		jQuery('#mbc-calendaroverview-' + uniqid).html(response);
		jQuery('.mbc-button-rl').attr('disabled',false);
		jQuery('.abcMonth').attr('disabled',false);
		jQuery('.abcYear').attr('disabled',false);
	});
	return false;
});
jQuery( '.mbc-calendar-overview' ).on('change', "select[name='abcYear']", function () {
	var uniqid = jQuery(this).data('id');
	var overviewMonth = jQuery( "select[name='abcMonth']").val();
	var overviewYear = jQuery( "select[name='abcYear']").val();
	jQuery('.abcMonth').attr('disabled',true);
	jQuery('.abcYear').attr('disabled',true);
	jQuery('.mbc-button-rl').attr('disabled',true);
	data = {
		action: 'mbc_booking_getCalOverview',
		mbc_nonce: ajax_mbc_booking_calOverview.mbc_nonce,
		month: overviewMonth,
		year: overviewYear,
		uniqid: uniqid
	};
	jQuery.post(ajax_mbc_booking_calOverview.ajaxurl, data, function (response){
		jQuery('#mbc-calendaroverview-' + uniqid).html(response);
		jQuery('.mbc-button-rl').attr('disabled',false);
		jQuery('.abcMonth').attr('disabled',false);
		jQuery('.abcYear').attr('disabled',false);
	});
	return false;
});

// Booking form
function getAbcAvailabilities(calendarId){
	data = {
		action: 'mbc_booking_getBookingResult',
		from: jQuery("#mbc-from").val(),
		to: jQuery("#mbc-to").val(),
		persons: jQuery("#mbc-persons").val(),
		hide_other: ajax_mbc_booking_showBookingForm.hide_other,
		hide_tooshort: ajax_mbc_booking_showBookingForm.hide_tooshort,
		calendarId: calendarId
	};
	jQuery('#mbc-submit-button').hide();
	jQuery('#mbc-bookingresults').hide();
	jQuery('.mbc-submit-loading').show();
	jQuery.post(ajax_mbc_booking_showBookingForm.ajaxurl, data, function (response){
		jQuery('#mbc-submit-button').show();
		jQuery('.mbc-submit-loading').hide();
		jQuery('#mbc-bookingresults').html(response);
		jQuery("#mbc-bookingresults").slideDown("slow");
		jQuery('.mbc-submit').attr('disabled',false);
	});	
	return false;	
}

jQuery('#mbc-form-content').on('click', '#mbc-check-availabilities', function() {
	getAbcAvailabilities(0);
});

jQuery.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return results[1] || 0;
    }
}

jQuery(document).ready(function() {
	if(jQuery.urlParam('mbc-paypal') !== null && jQuery.urlParam('token') !== null){
		jQuery('#mbc-form-content').hide();
		jQuery('#mbc_bookinform_loading').show();
		var payerId;
		payerId = 'null';
		if(jQuery.urlParam('PayerID') !== null){
			payerId = jQuery.urlParam('PayerID');
		}
		data = {
			action: 'mbc_booking_getPayPalResponse',
			paypal: jQuery.urlParam('mbc-paypal'),
			token: jQuery.urlParam('token'),
			payerId: payerId
		};
		jQuery.post(ajax_mbc_booking_showBookingForm.ajaxurl, data, function (response){
			jQuery('#mbc_bookinform_loading').hide();
			jQuery('#mbc-form-content').html(response);
			jQuery('#mbc-form-content').fadeIn('medium');
		});	
	}
    if (jQuery("#abcPostTrigger").length && jQuery("#abcPostTrigger").val() > 0 ) {
    	getAbcAvailabilities(jQuery("#abcPostCalendarId").val());
		jQuery('html, body').animate({
                    scrollTop: jQuery("#mbc-form-content").offset().top
                }, 2000);
	}
});

jQuery('#mbc-back-to-availabilities').click(function(){
	jQuery('#mbc-from').attr('disabled',false);
	jQuery('#mbc-from').removeClass('mbc-deactivated');
	jQuery('#mbc-to').attr('disabled',false);
	jQuery('#mbc-to').removeClass('mbc-deactivated');
	jQuery('#mbc-persons').attr('disabled',false);
	jQuery('#mbc-persons').removeClass('mbc-deactivated');
	jQuery('#mbc-back-to-availabilities').hide();
	jQuery('#mbc-check-availabilities').show();
	
	return false;	
});
jQuery(document).on('click', '.mbc-bookingform-book', function(){
	jQuery('#mbc-bookingresults').fadeOut('medium');
	jQuery('#mbc-check-availabilities').hide();
	jQuery('#mbc-from').attr('disabled',true);
	jQuery('#mbc-from').addClass('mbc-deactivated');
	jQuery('#mbc-to').attr('disabled',true);
	jQuery('#mbc-to').addClass('mbc-deactivated');
	jQuery('#mbc-persons').attr('disabled',true);
	jQuery('#mbc-persons').addClass('mbc-deactivated');
	data = {
		action: 'mbc_booking_getBookingFormStep2',
		from: jQuery(this).data('from'),
		to: jQuery(this).data('to'),
		persons: jQuery(this).data('persons'),
		calendar: jQuery(this).data('calendar')
	};
	jQuery.post(ajax_mbc_booking_showBookingForm.ajaxurl, data, function (response){
		jQuery('#mbc-bookingresults').html(response);
		jQuery('#mbc-bookingresults').fadeIn('medium');
		jQuery('#mbc-back-to-availabilities').show();
	});	
	return false;	
});


jQuery(document).on('click', '#mbc-bookingform-coupon-submit', function(){
	jQuery('#mbc-coupon').attr('disabled',true);
	jQuery('#mbc-coupon').addClass('mbc-deactivated');
	data = {
		action: 'mbc_booking_validateCode',
		totalprice: jQuery('#mbc-bookingform-totalprice').data('totalprice'),
		code: jQuery('#mbc-coupon').val(),
		from: jQuery(this).data('from'),
		to: jQuery(this).data('to'),
		calendar: jQuery(this).data('calendar')
	};
	jQuery.post(ajax_mbc_booking_showBookingForm.ajaxurl, data, function (response) {
        if (response == 0) {
            //jQuery('#mbc-coupon').appendChild('<label for="address" class="mbc-form-error" id="address-error">This field is required.</label>');
            jQuery('#mbc-coupon').addClass('mbc-form-error');
            jQuery('#mbc-coupon-error').html(ajax_mbc_booking_showBookingForm.coupon_unknown);
        }else if (response == 1) {
            jQuery('#mbc-coupon').addClass('mbc-form-error');
            jQuery('#mbc-coupon-error').html(ajax_mbc_booking_showBookingForm.coupon_nightlimit);
        }else {
			jQuery('#mbc-bookingform-totalprice').html(response);
			jQuery('#mbc-coupon').removeClass('mbc-form-error');
            jQuery('#mbc-coupon-error').html('');
		}
	});
	jQuery('#mbc-coupon').attr('disabled',false);
	jQuery('#mbc-coupon').removeClass('mbc-deactivated');
	return false;
});

jQuery(document).on('click', '#mbc-bookingform-extras-submit', function(){
	jQuery('#mbc-bookingresults').fadeOut('medium');
	data = {
		action: 'mbc_booking_getBookingFormStep2',
		extrasList: jQuery("input[name=mbc-extras-checkbox]:checked").map(function () {return this.value;}).get().join(","),
		from: jQuery(this).data('from'),
		to: jQuery(this).data('to'),
		persons: jQuery(this).data('persons'),
		calendar: jQuery(this).data('calendar')
	};
	jQuery.post(ajax_mbc_booking_showBookingForm.ajaxurl, data, function (response){
		jQuery('#mbc-bookingresults').html(response);
		jQuery('#mbc-bookingresults').fadeIn('medium');
		jQuery('#mbc-back-to-availabilities').show();
	});	
	return false;
});

jQuery(document).on('click', '#mbc-bookingform-back', function(){
	jQuery('#mbc-form-content').fadeOut('medium');
	data = {
		action: 'mbc_booking_getBackToBookingResult',
		from: jQuery(this).data('from'),
		to: jQuery(this).data('to'),
		persons: jQuery(this).data('persons')
	};
	jQuery.post(ajax_mbc_booking_showBookingForm.ajaxurl, data, function (response){
		jQuery('#mbc-form-content').html(response);
		jQuery('#mbc-form-content').fadeIn('medium');
	});	
	return false;	
});

jQuery(document).on('click', '#mbc-bookingform-book-submit', function(){
		data = {
			action: 'mbc_booking_getBookingFormBook',
			from: jQuery(this).data('from'),
			to: jQuery(this).data('to'),
			persons: jQuery(this).data('persons'),
			calendar: jQuery(this).data('calendar'),
			extraslist: jQuery(this).data('extraslist'),
			firstname: jQuery('#first_name').val(),
			lastname: jQuery('#last_name').val(),
			email: jQuery('#email').val(),
			phone: jQuery('#phone').val(),
			address: jQuery('#address').val(),
			zip: jQuery('#zip').val(),
			city: jQuery('#city').val(),
			county: jQuery('#county').val(),
			country: jQuery('#country').val(),
			coupon: jQuery('#mbc-coupon').val(),
			payment: jQuery("input[name='payment']:checked").val(),
			message: jQuery('#message').val()
		};
	jQuery('.mbc-booking-form').validate({ // initialize the plugin
        errorClass:'mbc-form-error',
		rules: ajax_mbc_booking_showBookingForm.rules,
		submitHandler: function (form) { 
		jQuery('#mbc-form-content').fadeOut('medium');
		jQuery('#mbc_bookinform_loading').show();
		jQuery('html, body').animate({ scrollTop: (jQuery('#mbc-form-wrapper').offset().top - 150)}, 'slow');
		jQuery.post(ajax_mbc_booking_showBookingForm.ajaxurl, data, function (response){
			jQuery('#mbc_bookinform_loading').hide();
			jQuery('#mbc-form-content').html(response);
			jQuery('#mbc-form-content').fadeIn('medium');
		});	
		return false;
        }
    });	
});
	