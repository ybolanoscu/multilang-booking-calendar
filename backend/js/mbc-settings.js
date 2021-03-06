
jQuery('#tab-content').on('click', '#mbc_textCustomizationSubmit', function(){
	var abcLanguage = jQuery( "select[name='abcLanguage']").val();
	var textCheckAvailabilities = jQuery("#textCheckAvailabilities").val();
	var textSelectRoom = jQuery("#textSelectRoom").val();
	var textSelectedRoom = jQuery("#textSelectedRoom").val();
	var textOtherRooms = jQuery("#textOtherRooms").val();
	var textNoRoom = jQuery("#textNoRoom").val();
	var textAvailRooms = jQuery("#textAvailRooms").val();
	var textRoomType = jQuery("#textRoomType").val();
	var textYourStay = jQuery("#textYourStay").val();
	var textCheckin = jQuery("#textCheckin").val();
	var textCheckout = jQuery("#textCheckout").val();
	var textPersons = jQuery("#textPersons").val();
	var textBookNow = jQuery("#textBookNow").val();
	var textThankYou = jQuery("#textThankYou").val();
	var textBookingSummary = jQuery("#textBookingSummary").val();
	var textRoomPrice = jQuery("#textRoomPrice").val();
	var textEditButton = jQuery("#textEditButton").val();
	var textContinueButton = jQuery("#textContinueButton").val();
	var textFullyBooked = jQuery("#textFullyBooked").val();
	var textPartlyAvailable = jQuery("#textPartlyAvailable").val();
	var textAvailable = jQuery("#textAvailable").val();
	var textPartlyBooked = jQuery("#textPartlyBooked").val();
	jQuery('#mbc_textCustomizationSubmit').hide();
	jQuery('#mbc_textSavingLoading').show();
	data = {
			action: 'mbc_booking_editTextCustomization',
			mbc_settings_nonce: ajax_mbc_settings.mbc_settings_nonce,
			abcLanguage: abcLanguage,
			textCheckAvailabilities: textCheckAvailabilities,
			textSelectRoom: textSelectRoom,
			textSelectedRoom: textSelectedRoom,
			textOtherRooms: textOtherRooms,
			textNoRoom: textNoRoom,
			textAvailRooms: textAvailRooms,
			textRoomType: textRoomType,
			textYourStay: textYourStay,
			textCheckin: textCheckin,
			textCheckout: textCheckout,
			textPersons: textPersons,
			textBookNow: textBookNow,
			textThankYou: textThankYou,
			textBookingSummary: textBookingSummary,
			textRoomPrice: textRoomPrice,
			textEditButton: textEditButton,
			textContinueButton: textContinueButton,
			textFullyBooked: textFullyBooked,
			textPartlyAvailable: textPartlyAvailable,
			textAvailable: textAvailable,
			textPartlyBooked: textPartlyBooked,
		};
		jQuery.post(ajax_mbc_settings.ajaxurl, data, function (response){
			jQuery('#mbc_textSavingLoading').hide();
			jQuery('#mbc_textCustomizationSubmit').show();
			jQuery('#mbc_textSavingDone').show();
			jQuery("#mbc_textSavingDone").fadeOut(7000);
		});
		return false;
});

jQuery( "select[name='abcLanguage']").on('change', function () {
	var abcLanguage = jQuery( "select[name='abcLanguage']").val();
	jQuery('#mbc_textCustomizationSubmit').attr('disabled',true);
	jQuery('#languageDropdown').attr('disabled',true);
	data = {
		action: 'mbc_booking_getTextCustomization',
		mbc_settings_nonce: ajax_mbc_settings.mbc_settings_nonce,
		abcLanguage: abcLanguage
	};
	jQuery.post(ajax_mbc_settings.ajaxurl, data, function (response){
		var textLabels = jQuery.parseJSON(response);
		jQuery('#textCheckAvailabilities').val(textLabels.checkAvailabilities);
		jQuery('#textSelectRoom').val(textLabels.selectRoom);
		jQuery('#textSelectedRoom').val(textLabels.selectedRoom);
		jQuery('#textOtherRooms').val(textLabels.otherRooms);
		jQuery('#textNoRoom').val(textLabels.noRoom);
		jQuery('#textAvailRooms').val(textLabels.availRooms);
		jQuery('#textRoomType').val(textLabels.roomType);
		jQuery('#textYourStay').val(textLabels.yourStay);
		jQuery('#textCheckin').val(textLabels.checkin);
		jQuery('#textCheckout').val(textLabels.checkout);
		jQuery('#textPersons').val(textLabels.persons);
		jQuery('#textBookNow').val(textLabels.bookNow);
		jQuery('#textThankYou').val(textLabels.thankYou);
		jQuery('#textBookingSummary').val(textLabels.bookingSummary);
		jQuery('#textRoomPrice').val(textLabels.roomPrice);
		jQuery('#textEditButton').val(textLabels.editButton);
		jQuery('#textContinueButton').val(textLabels.continueButton);
		jQuery('#textFullyBooked').val(textLabels.fullyBooked);
		jQuery('#textPartlyAvailable').val(textLabels.partlyAvailable);
		jQuery('#textAvailable').val(textLabels.available);
		jQuery('#textPartlyBooked').val(textLabels.partlyBooked);
		jQuery('#mbc_textCustomizationSubmit').attr('disabled',false);
		jQuery('#languageDropdown').attr('disabled',false);
	});
	return false;
});