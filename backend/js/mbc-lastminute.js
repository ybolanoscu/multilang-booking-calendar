jQuery.validator.addMethod("anyDate",
    function(value, element) {
        return value.match(/^(0?[1-9]|[12][0-9]|3[0-1])[/., -](0?[1-9]|1[0-2])[/., -](19|20)?\d{2}$/);
    },
    "Please enter a date in the format!"
);

jQuery('#mbc-lastminute-form').validate({
     rules: {
    	'calendar_id[]': {
          required: true,
          minlength: 1
       },
       'weekdays[]': {
           required: true,
           minlength: 1
         },
        discountamount: {
            required: true,
            digits: true,
            max: {
		        param: 100,  
            	depends: function(element) {
		                return (jQuery("#discounttype").val() == 'rel');
            		}
		        }
        },
        days: {
            required: true,
            digits: true
        },
        start: {
            required: true,
            anyDate: true
        },
        end: {
            required: true,
            anyDate: true
        }
     },
     errorPlacement: function(error, element) {
         if(element.attr("name") == 'calendar_id[]'){
             jQuery( "#label-calendar_id").css( "color", "red" );
         } else if(element.attr("name") == 'weekdays[]'){
             jQuery( "#label-weekdays").css( "color", "red" );
         } else {
            jQuery( "#label-" + element.attr("name")).css( "color", "red" );
            jQuery(element).css( "border-color", "red" );
        }
    }
});


// Pagination for overview
jQuery('[data-uk-pagination]').on('select.uk.pagination', function(e, pageIndex){
	var itemsOnPage = jQuery(this).data('mbc-itemsonpage');
	var offset = (pageIndex)*itemsOnPage;
	var divid = '#' + jQuery(this).data('mbc-divid');
	jQuery(divid).fadeOut('medium');
	data = {
		action: 'mbc_getLastMinuteContent',
		mbc_lastminute_nonce: ajax_mbc_lastminute.mbc_lastminute_nonce,
		state: jQuery(this).data('mbc-state'),
		offset: offset,
		itemsOnPage: itemsOnPage
	};
	jQuery.post(ajax_mbc_lastminute.ajaxurl, data, function (response){
		jQuery(divid).html(jQuery(response));
		jQuery(divid).fadeIn('fast');
	});
	return false;
});
