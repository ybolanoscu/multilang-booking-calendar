<?php
//Function for shortcode "mbc-bookingwidget". Form asks for customers input and opens booking form when submitted.
function mbc_booking_showBookingWidget( $atts ) {
	global $abcUrl;
	$output = '';
	wp_enqueue_style( 'styles-css', $abcUrl.'frontend/css/styles.css' );
	wp_enqueue_style( 'font-awesome', $abcUrl.'frontend/css/font-awesome.min.css' );
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('mbc-widget', $abcUrl.'frontend/js/mbc-widget.js', array('jquery'));
	$dateformat = mbc_booking_dateFormatToJS(getAbcSetting("dateformat"));
	wp_localize_script( 'mbc-widget', 'mbc_functions_vars', array( 'dateformat' => $dateformat, 'firstday' => getAbcSetting("firstdayofweek")));
	wp_enqueue_style('mbc-datepicker', $abcUrl.'/frontend/css/jquery-ui.min.css');
	$datepickerLang = array('af','ar-DZ','ar','az','be','bg','bs','ca','cs','cy-GB','da','de','el','en-AU','en-GB','en-NZ',
			'eo','es','et','eu','fa','fi','fo','fr-CA','fr-CH','fr','gl','he','hi','hr','hu','hy','id','is',
			'it-CH','it','ja','ka','kk','km','ko','ky','lb','lt','lv','mk','ml','ms','nb','nl-BE','nl','nn',
			'no','pl','pt-BR','pt','rm','ro','ru','sk','sl','sq','sr-SR','sr','sv','ta','th','tj','tr','uk',
			'vi','zh-CN','zh-HK','zh-TW');
	if(substr(get_locale(), 0,2) != 'en' && in_array(get_locale(), $datepickerLang)){
		wp_enqueue_script('jquery-datepicker-lang', $abcUrl.'frontend/js/datepicker_lang/datepicker-'.get_locale().'.js', array('jquery-ui-datepicker'));
	}elseif(substr(get_locale(), 0,2) != 'en' && in_array(substr(get_locale(), 0,2), $datepickerLang)){
		wp_enqueue_script('jquery-datepicker-lang', $abcUrl.'frontend/js/datepicker_lang/datepicker-'.substr(get_locale(), 0,2).'.js', array('jquery-ui-datepicker'));
	}
	$abcPersonValue = 1;
	if(isset($_POST['mbc-persons'])){ // Checking for cookies
		$abcPersonValue = intval($_POST['mbc-persons']);
	}elseif(isset($_COOKIE['mbc-persons'])){ // Checking for cookies
		$abcPersonValue = intval($_COOKIE['mbc-persons']);
	}
	$optionPersons = '';
	for( $i = 1; $i <= getAbcSetting('personcount'); $i++) {
		$optionPersons .= '<option value="'.$i.'"';
		if ( $i == $abcPersonValue) {
			$optionPersons .= ' selected';
		}
		$optionPersons .= '>'.$i.'</option>';
	}
	$abcFromValue = '';
	$abcToValue = '';
	if(isset($_POST['mbc-from']) && isset($_POST['mbc-to'])
			&& mbc_booking_validateDate($_POST['mbc-from'], getAbcSetting("dateformat"))
			&& mbc_booking_formatDateToDB($_POST['mbc-from']) >= date('Y-m-d')
			){ // Checking for POST variables (via single calendar)
				$abcFromValue = sanitize_text_field($_POST['mbc-from']);
				$abcToValue = sanitize_text_field($_POST['mbc-to']);
	}elseif(isset($_COOKIE['mbc-from']) && isset($_COOKIE['mbc-to'])
			&& mbc_booking_validateDate($_COOKIE['mbc-from'], getAbcSetting("dateformat"))
			&& mbc_booking_formatDateToDB($_COOKIE['mbc-from']) >= date('Y-m-d'))
	{ // Checking for cookies and checking if "from date" is in the past
		$abcFromValue = sanitize_text_field($_COOKIE['mbc-from']);
		$abcToValue = sanitize_text_field($_COOKIE['mbc-to']);
	}
	if(getAbcSetting("bookingpage") > 0){
		$output .= abcEnqueueCustomCss().'<div id="mbc-widget-wrapper">
				<div id="mbc-widget-content">
					<form class="mbc-form"  method="post" action="'.get_permalink(getAbcSetting("bookingpage")).'">
					<div class="mbc-widget">
						<label for="mbc-widget-from">'.mbc_booking_getCustomText('checkin').'</label>
						<div class="mbc-input-fa">
							<span class="fa fa-calendar"></span>
							<input id="mbc-widget-from" name="mbc-from" readonly="true" value="'.$abcFromValue.'">
						</div>
						<label for="mbc-widget-to">'.mbc_booking_getCustomText('checkout').'</label>
						<div class="mbc-input-fa">
							<span class="fa fa-calendar"></span>
							<input id="mbc-widget-to" name="mbc-to" readonly="true" value="'.$abcToValue.'">
						</div>
						<label for="mbc-persons">'.mbc_booking_getCustomText('persons').'</label>
						<div class="mbc-input-fa">
							<span class="fa fa-female mbc-guest1"></span>
							<span class="fa fa-male mbc-guest2"></span>
							<select id="mbc-persons" name="mbc-persons">
								'.$optionPersons.'
							</select>
						</div>
						<input id="mbc-trigger" type="hidden" name="mbc-trigger" value="1">
						<input id="mbc-calendarId" type="hidden" name="mbc-calendarId" value="0">
						</div>
						<div class="mbc-widget-row">
							<button type="submit" class="mbc-submit" id="mbc-widget-check-availabilities">
								<span class="mbc-submit-text">'.mbc_booking_getCustomText('checkAvailabilities').'</button>
						</div>
					</form>
				</div>
			</div>';
	}else{
		$output .='<p>'.__('There is no booking page configured. Check the settings of the Multilang Booking Calendar.', 'multilang-booking-calendar').'</p>';
	}
	return $output;
}

//Function for shortcode "mbc-bookingform". Form asks for customers input, tracks those inputs, shows availabilities and creates bookings.
function mbc_booking_showBookingForm( $atts ) {
	global $abcUrl;
	wp_enqueue_style( 'styles-css', $abcUrl.'frontend/css/styles.css' );
	wp_enqueue_style( 'font-awesome', $abcUrl.'frontend/css/font-awesome.min.css' );
	wp_enqueue_script('mbc-functions', $abcUrl.'frontend/js/mbc-functions.js', array('jquery'));
	wp_enqueue_script('mbc-ajax', $abcUrl.'frontend/js/mbc-ajax.js', array('jquery'));
	wp_enqueue_script('jquery-validate', $abcUrl.'frontend/js/jquery.validate.min.js', array('jquery'));
	wp_enqueue_script('mbc-bookingform', $abcUrl.'frontend/js/mbc-bookingform.js', array('jquery'));
	wp_enqueue_script('jquery-ui-datepicker');
	$dateformat = mbc_booking_dateFormatToJS(getAbcSetting("dateformat"));
	wp_localize_script( 'mbc-functions', 'mbc_functions_vars', array(
        'dateformat' => $dateformat,
        'firstday' => getAbcSetting("firstdayofweek")
        ));
	wp_enqueue_style('mbc-datepicker', $abcUrl.'/frontend/css/jquery-ui.min.css');
	$validateLang = array('ar','bg','bn_BD','ca','cs','da','de','el','es_AR','es_PE','es','et','eu','fa','fi',
		'fr','ge','gl','he','hr','hu','hy_AM','id','is','it','ja','ka','kk','ko','lt','lv','mk','my','nl','no',
		'pl','pt_BR','pt_PT','ro','ru','si','sk','sl','sr_lat','sr','sv','th','tj','tr','uk','vi','zh_TW','zh');
	if(substr(get_locale(), 0,2) != 'en' && in_array(get_locale(), $validateLang)){
		wp_enqueue_script('jquery-validate-lang', $abcUrl.'frontend/js/validate_lang/messages_'.get_locale().'.js', array('jquery-ui-datepicker'));
	}elseif(substr(get_locale(), 0,2) != 'en' && in_array(substr(get_locale(), 0,2), $validateLang)){
		wp_enqueue_script('jquery-validate-lang', $abcUrl.'frontend/js/validate_lang/messages_'.substr(get_locale(), 0,2).'.js', array('jquery-ui-datepicker'));
	}
	$datepickerLang = array('af','ar-DZ','ar','az','be','bg','bs','ca','cs','cy-GB','da','de','el','en-AU','en-GB','en-NZ',
		'eo','es','et','eu','fa','fi','fo','fr-CA','fr-CH','fr','gl','he','hi','hr','hu','hy','id','is',
		'it-CH','it','ja','ka','kk','km','ko','ky','lb','lt','lv','mk','ml','ms','nb','nl-BE','nl','nn',
		'no','pl','pt-BR','pt','rm','ro','ru','sk','sl','sq','sr-SR','sr','sv','ta','th','tj','tr','uk',
		'vi','zh-CN','zh-HK','zh-TW');
	if(substr(get_locale(), 0,2) != 'en' && in_array(get_locale(), $datepickerLang)){
		wp_enqueue_script('jquery-datepicker-lang', $abcUrl.'frontend/js/datepicker_lang/datepicker-'.get_locale().'.js', array('jquery'));
	}elseif(substr(get_locale(), 0,2) != 'en' && in_array(substr(get_locale(), 0,2), $datepickerLang)){
		wp_enqueue_script('jquery-datepicker-lang', $abcUrl.'frontend/js/datepicker_lang/datepicker-'.substr(get_locale(), 0,2).'.js', array('jquery'));
	}
	$bookingFormSetting = getAbcSetting("bookingform");	
	$validateRules = array('email' => array( 'required' => true, 'email' => true));
	if($bookingFormSetting["firstname"] == 2){$validateRules["first_name"]["required"] = true;}
	if($bookingFormSetting["lastname"] == 2){$validateRules["last_name"]["required"] = true;}
	if($bookingFormSetting["phone"] == 2){$validateRules["phone"]["required"] = true;}
	if($bookingFormSetting["street"] == 2){$validateRules["address"]["required"] = true;}
	if($bookingFormSetting["zip"] == 2){$validateRules["zip"]["required"] = true;}
	if($bookingFormSetting["city"] == 2){$validateRules["city"]["required"] = true;}
	if($bookingFormSetting["county"] == 2){$validateRules["county"]["required"] = true;}
	if($bookingFormSetting["country"] == 2){$validateRules["country"]["required"] = true;}
	if($bookingFormSetting["message"] == 2){$validateRules["message"]["required"] = true;}
	$hideOther = 0;
	if(isset($atts['hide_other']) && intval($atts['hide_other']) == 1){
		$hideOther = 1;
	}
	$hideTooShort = 0;
	if(isset($atts['hide_tooshort']) && intval($atts['hide_tooshort']) == 1){
		$hideTooShort = 1;
	}
	wp_localize_script( 'mbc-ajax', 'ajax_mbc_booking_showBookingForm', array(
	    'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'rules' => $validateRules,
		'hide_other' => $hideOther,
		'hide_tooshort' => $hideTooShort,
		));
	$abcFromValue = '';
	$abcToValue = '';
	$abcPostTrigger = 0;
	$abcPostCalendarId = 0;
	$bookingFormResult = '';	
	if(isset($_POST['mbc-from']) && isset($_POST['mbc-to'])
		&& mbc_booking_validateDate($_POST['mbc-from'], getAbcSetting("dateformat"))
		&& mbc_booking_formatDateToDB($_POST['mbc-from']) >= date('Y-m-d') )
		{ // Checking for POST variables (via single calendar)
			$abcFromValue = sanitize_text_field($_POST['mbc-from']);
			$abcToValue = sanitize_text_field($_POST['mbc-to']);
	}elseif(isset($_COOKIE['mbc-from']) && isset($_COOKIE['mbc-to'])
		&& mbc_booking_validateDate($_COOKIE['mbc-from'], getAbcSetting("dateformat"))
		&& mbc_booking_formatDateToDB($_COOKIE['mbc-from']) >= date('Y-m-d'))
		{ // Checking for cookies and checking if "from date" is in the past
			$abcFromValue = sanitize_text_field($_COOKIE['mbc-from']);
			$abcToValue = sanitize_text_field($_COOKIE['mbc-to']);
	}
	if(isset($_POST['mbc-trigger']) && $_POST['mbc-trigger'] > 0){
		$abcPostTrigger = $_POST['mbc-trigger'];
	}	
	if(isset($_POST['mbc-calendarId']) && $_POST['mbc-calendarId'] > 0){
		$abcPostCalendarId = $_POST['mbc-calendarId'];
	}	
	$abcPersonValue = 1;
	if(isset($_POST['mbc-persons'])){ // Checking for cookies
		$abcPersonValue = intval($_POST['mbc-persons']);
	}elseif(isset($_COOKIE['mbc-persons'])){ // Checking for cookies
		$abcPersonValue = intval($_COOKIE['mbc-persons']);
	}
	$optionPersons = '';
	for( $i = 1; $i <= getAbcSetting('personcount'); $i++) { 
		$optionPersons .= '<option value="'.$i.'"';
		if ( $i == $abcPersonValue) {
			$optionPersons .= ' selected';
		}
		$optionPersons .= '>'.$i.'</option>';
	}
	$bookingFormResult .= '
	<!-- Multilang Booking Calendar Booking Form - https://booking-calendar-plugin.com -->
	'.abcEnqueueCustomCss().'
	<div id="mbc-form-wrapper">
		<img alt="'.__('Loading...', 'multilang-booking-calendar').'" src="'.admin_url('/images/wpspin_light.gif').'" align="middle" class="waiting" id="mbc_bookinform_loading" style="display:none" />				
		<div id="mbc-form-content">
			<form class="mbc-form"  method="post">
				<div class="mbc-column">
					<label for="mbc-from">'.mbc_booking_getCustomText('checkin').'</label>
					<div class="mbc-input-fa">
						<span class="fa fa-calendar"></span>
						<input id="mbc-from" name="mbc-from" readonly="true" class="mbc-from" value="'.$abcFromValue.'">
					</div>
				</div>		
				<div class="mbc-column">
					<label for="mbc-to">'.mbc_booking_getCustomText('checkout').'</label>
					<div class="mbc-input-fa">
						<span class="fa fa-calendar"></span>
						<input id="mbc-to" name="mbc-to" readonly="true" class="mbc-to" value="'.$abcToValue.'">
					</div>
				</div>			
				<div class="mbc-column mbc-form">	
						<label for="mbc-persons">'.mbc_booking_getCustomText('persons').'</label>
						<div class="mbc-input-fa">
							<span class="fa fa-female mbc-guest1"></span>
							<span class="fa fa-male mbc-guest2"></span>
							<select id="mbc-persons" name="mbc-persons">
								'.$optionPersons.'
							</select>
						</div>
						<input id="abcPostTrigger" type="hidden" name="abcPostTrigger" value="'.$abcPostTrigger.'">	
						<input id="abcPostCalendarId" type="hidden" name="abcPostCalendarId" value="'.$abcPostCalendarId.'">	
				</div>
			</form>	
			<div class="mbc-form-row">
				<button class="mbc-submit" id="mbc-check-availabilities"><span id="mbc-submit-button" class="mbc-submit-text">'.mbc_booking_getCustomText('checkAvailabilities').'</span><span class="mbc-submit-loading" />'.__('<img alt="Loading..." src="'.admin_url('/images/wpspin_light.gif').'" align="middle" class="waiting" />', 'multilang-booking-calendar').'</span></button>
				<button class="mbc-submit" id="mbc-back-to-availabilities" style="display: none;"><span class="mbc-submit-text">'.mbc_booking_getCustomText('editButton').'</span></button>
			</div>	
			
			<div id="mbc-bookingresults"></div>
		</div>';
	
	if(getAbcSetting('poweredby') == 1){
		$bookingFormResult .= '<div class="mbc-powered-by">
				'.__('Powered by:', 'multilang-booking-calender').'&nbsp;
				<a href="https://booking-calendar-plugin.com" target="_blank">Multilang Booking Calendar</a>
			</div>';
	}
	$bookingFormResult .= '</div>';
	return $bookingFormResult;
}

//AJAX-Request for saving the customer inputs to the cookie, checking availabilities and calculating prices.
function ajax_mbc_booking_getBookingResult () {
	$dateformat = getAbcSetting("dateformat");
	if (isset($_POST["from"])  && isset($_POST["to"]) 
		&& mbc_booking_validateDate($_POST["from"], $dateformat)
		&& mbc_booking_validateDate($_POST["to"], $dateformat)
		&& $_POST["from"] != $_POST["to"]){
		global $wpdb;
		$abcFromValue = sanitize_text_field($_POST["from"]); 
		$abcToValue = sanitize_text_field($_POST["to"]);
		$abcPersons = intval($_POST["persons"]);
		// Normalizing entered dates
		$normFromValue = mbc_booking_formatDateToDB($abcFromValue);
		$normToValue = mbc_booking_formatDateToDB($abcToValue);
		$isSuccessful = false;
		
		//Setting Cookies
		if(getAbcSetting("cookies") == 1) {
			$domain = str_replace('www', '', str_replace('https://','',str_replace('http://','',get_site_url()))); // Getting domain-name for creating cookies
			setcookie('mbc-from', $abcFromValue, time()+3600*24*30*6, '/',  $domain);
			setcookie('mbc-to', $abcToValue, time()+3600*24*30*6, '/', $domain );
			setcookie('mbc-persons', $abcPersons, time()+3600*24*30*6, '/', $domain );
		}
					
		//Getting actual results
		$requestQuery = "SELECT * FROM ".$wpdb->prefix."mbc_calendars WHERE maxUnits >= ".$abcPersons;
		$er = $wpdb->get_results($requestQuery, ARRAY_A);
		$getBookingResultReturn = '';
		$numberOfDays = floor((strtotime($normToValue) - strtotime($normFromValue))/(60*60*24));
		$priceformat = getAbcSetting('priceformat');
		$availableRooms = '';
		$selectedRoom = '';
		foreach($er as $row) {
			if (getAbcAvailability($row["id"], sanitize_text_field($_POST["from"]), sanitize_text_field($_POST["to"])) > 0){
				$isSuccessful = true;
				$totalSum = mbc_booking_getTotalPrice($row["id"], $normFromValue, $numberOfDays);
				$tempRoom = '<div class="mbc-result-calendar">';
				if ($row["infoPage"] == 0){
					$tempRoom .= '<span class="mbc-result-roomname" title="'.esc_html($row["infoText"]).'">'.esc_html($row["name"]).', </span>';
				} else {
					$tempRoom.= '<span class="mbc-result-roomname"><a href="'.get_permalink($row["infoPage"]).'" title="'.esc_html($row["infoText"]).'">'.esc_html($row["name"]).'</a>, </span>';
				}
				for( $i = 1; $i <= $row["maxUnits"]; $i++) {
					$tempRoom .= '<span class="fa fa-male"></span>';
				}
				$tempRoom .= ' 
						<br/><span>'.esc_html($row["infoText"]).'<br/>';
				$minimumStay = mbc_booking_checkMinimumStay($row["id"], $normFromValue, $normToValue);
				if($minimumStay > 0){
					$tempRoom .= '<b><span class="mbc-too-short">'.sprintf( __('Your stay is too short. Minimum stay for those dates is %d nights.', 'multilang-booking-calendar'), $minimumStay ).'</span></b>';
				}else {
					$tempRoom .= '
							<form action="'.get_permalink().'" method="post">
								<div data-persons="'.$abcPersons.'" data-from="'.$abcFromValue.'" data-to="'.$abcToValue.'" data-calendar="'.$row["id"].'" class="mbc-bookingform-book mbc-submit">
									<span class="fa fa-chevron-right"></span>
									<span>'.mbc_booking_getCustomText('selectRoom').'</span>
								</div>
							</form>';
				}
				$tempRoom .= '</span></div>';
				if(isset($_POST["calendarId"]) && $_POST["calendarId"] == $row["id"]){
					$selectedRoom = '<span class="mbc-result-header">'.mbc_booking_getCustomText('selectedRoom').':</span>'.$tempRoom;
				}elseif($minimumStay == 0 || $_POST["hide_tooshort"] != 1){
					$availableRooms .= $tempRoom;
				}
				
			}
		}
		if ($isSuccessful && isset($_POST["calendarId"]) && $_POST["calendarId"] > 0){
			$getBookingResultReturn .= $selectedRoom;
			if(strlen($availableRooms) > 1 && $_POST["hide_other"] == 0){
				$getBookingResultReturn .='<span class="mbc-result-header">'.mbc_booking_getCustomText('otherRooms').':</span>'.$availableRooms;
			}
			$getBookingResultReturn .= mbc_booking_setPageview('bookingform/rooms-available'); // Google Analytics Tracking
		}elseif ($isSuccessful){
			$getBookingResultReturn .= $selectedRoom.'<span class="mbc-result-header">'.mbc_booking_getCustomText('availRooms').':</span>'.$availableRooms;
			$getBookingResultReturn .= mbc_booking_setPageview('bookingform/rooms-available'); // Google Analytics Tracking
		}else {
			$getBookingResultReturn .= '<span class="mbc-result-header">'.mbc_booking_getCustomText('noRoom').'</span>';
			$getBookingResultReturn .= mbc_booking_setPageview('bookingform/rooms-unavailable'); // Google Analytics Tracking
		}	
		//Saving inputs for tracking
		$wpdb->insert($wpdb->prefix.'mbc_requests',
			array('date_from' => $normFromValue,
			'date_to' => $normToValue,
			'persons' => $abcPersons,
			'successful' => $isSuccessful));
		
		//Returning output
		echo $getBookingResultReturn;
	} else {
		echo __('Something went wrong.', 'multilang-booking-calendar');
	}	
	die();
}
add_action('wp_ajax_mbc_booking_getBookingResult', 'ajax_mbc_booking_getBookingResult');
add_action( 'wp_ajax_nopriv_mbc_booking_getBookingResult', 'ajax_mbc_booking_getBookingResult');

function ajax_mbc_booking_getBookingFormStep2 () {
	if (isset($_POST["from"])  && isset($_POST["to"]) && isset($_POST["persons"]) && isset($_POST["calendar"]) ) {//&& date("Y-m-d", strtotime($_POST["from"])) >= date("Y-m-d")
		global $wpdb;
		$dateformat = getAbcSetting("dateformat");
		$abcFromValue = sanitize_text_field($_POST["from"]); 
		$abcToValue = sanitize_text_field($_POST["to"]);
		// Normalizing entered dates
		$normFromValue = mbc_booking_formatDateToDB($abcFromValue);
		$normToValue = mbc_booking_formatDateToDB($abcToValue);
		$abcPersons = intval($_POST["persons"]);
		$calendarId = intval($_POST["calendar"]);
		$requestQuery = "SELECT name FROM ".$wpdb->prefix."mbc_calendars WHERE id = ".$calendarId;
		$er = $wpdb->get_row($requestQuery);
		$calendarName = $er->name;
		$numberOfDays = floor((strtotime($normToValue) - strtotime($normFromValue))/(60*60*24));
		$totalPrice = mbc_booking_getTotalPrice($calendarId, $normFromValue, $numberOfDays);
		$bookingFormOutput = '';
		$extrasOptional = getAbcExtrasList($numberOfDays, $abcPersons, 1);
		if(!isset($_POST["extrasList"]) && count($extrasOptional) > 0){
			$amountOfExtras = count($extrasOptional);
			$bookingFormOutput .= '
				<hr class="mbc-form-hr" />
				<form class="mbc-booking-form" action="'.get_permalink().'" method="post">
					<div class="mbc-form-row">
								<div class="mbc-fullcolumn">
									<span class="mbc-result-header">'._n('Optional extra', 'Optional extras', $amountOfExtras, 'multilang-booking-calendar').'</span>
								</div>
							</div>
							<div class="mbc-form-row">';
			$i = 1;
			foreach($extrasOptional as $extra){
				$tempText = '<span  class="mbc-extra-name mbc-pointer">'.$extra["name"].', '.mbc_booking_formatPrice($extra["priceValue"]).'</span>';
				if(strlen($extra["explanation"]) > 1){
					$tempText .= '<span class="mbc-extra-cost mbc-pointer"></br>('.$extra["priceText"].')</br>'.$extra["explanation"].'</span>';
				}
				$extraTemp = '<div class="mbc-column">
								<div class="mbc-option">
									<div class="mbc-optional-column-checkbox">
										<input type="checkbox" id="checkbox'.$extra["id"].'" name="mbc-extras-checkbox" class="mbc-extra-checkbox" value="'.$extra["id"].'">
									</div>
									<div class="mbc-optional-column-text">
										<label for="checkbox'.$extra["id"].'">'.$tempText.'</label>
									</div>
								</div>
							  </div>';
				if($i % 2 == 1){
					$bookingFormOutput .= '<div class="mbc-form-row">'.$extraTemp;
				}else{
					$bookingFormOutput .= $extraTemp.'</div>';
				}
				$i++;
			}					
			$bookingFormOutput .= '
					</div>
					<div class="mbc-form-row">
						<div  id="mbc-bookingform-extras-submit" data-persons="'.$abcPersons.'" data-from="'.$abcFromValue.'" data-to="'.$abcToValue.'" data-calendar="'.$calendarId.'" class="mbc-submit">
							<span class="fa fa-chevron-right"></span>
							<span>'.mbc_booking_getCustomText('continueButton').'</span>
						</div>	
					</div>
				</form>	';
		}else {
			$extrasComplete = getAbcExtrasList($numberOfDays, $abcPersons);
			$extrasSelected = '';
			$extrasString = '';
			if(isset($_POST["extrasList"])){
				$extrasSelected = explode(',', sanitize_text_field($_POST["extrasList"]));
				$extrasString = sanitize_text_field($_POST["extrasList"]);
			}
			$extrasOptional = '';
			$extrasMandatory = '';
			$optionalCosts = 0;
			$mandatoryCosts = 0;
			$optionalCounter = 0;
			$extrasOutput = '';
			foreach($extrasComplete as $extra) {
				switch($extra["mandatory"]){
					case '1':
						$mandatoryCosts += $extra["priceValue"];
						$tempText = '<span class="mbc-extra-name">'.$extra["name"].', '.mbc_booking_formatPrice($extra["priceValue"]).'</span>';
						if(strlen($extra["explanation"]) > 1){
							$tempText .= '<span class="mbc-extra-cost"></br>('.$extra["priceText"].')</br>'.$extra["explanation"].'</span>';
						}
						$extrasMandatory .= '<div class="mbc-column">'.$tempText.'</div>';
						break;
					case '0':
						if(in_array($extra["id"], $extrasSelected)){
							$optionalCounter++;
							$optionalCosts += $extra["priceValue"];
							if(strlen($extrasOptional) > 1){
								$extrasOptional .= ', ';
							} 
							$extrasOptional .= $extra["name"].': '.mbc_booking_formatPrice($extra["priceValue"]);
						}
						break;	
				}
			}
			if(strlen($extrasMandatory) > 1){
				$extrasOutput .= '<div class="mbc-form-row">
								<div class="mbc-column">
									<span class="mbc-result-header">'.__('Additional costs', 'multilang-booking-calendar').'</span>
								</div>
							</div>
							<div class="mbc-form-row">
					'.$extrasMandatory.'
					</div>
					<div class="mbc-clearfix">
						<hr class="mbc-form-hr" />
					</div>';
			}
			if($optionalCounter > 0){
				$extrasOptional = _n('Selected extra', 'Selected extras', $optionalCounter, 'multilang-booking-calendar').': '.$extrasOptional.'<br/>';
			}
			$priceOutput = '';
			if($optionalCosts >0){
				$priceOutput .= __('Costs for the extras', 'multilang-booking-calendar').': '.mbc_booking_formatPrice($optionalCosts).'<br/>';
			}
			if($mandatoryCosts >0){
				$priceOutput .= __('Additional costs', 'multilang-booking-calendar').': '.mbc_booking_formatPrice($mandatoryCosts).'<br/>';
			}
			if($mandatoryCosts >0 || $optionalCosts >0){
				$priceOutput .= mbc_booking_getCustomText('roomPrice').': '.mbc_booking_formatPrice($totalPrice).'<br/>';
				$totalPrice = $totalPrice + $optionalCosts + $mandatoryCosts;
			}
			$bookingFormOutput = '
				<hr class="mbc-form-hr" style="margin: 15px 0;" />
				<form class="mbc-booking-form" action="'.get_permalink().'" method="post">';
			$bookingFormSetting = getAbcSetting("bookingform");	
			$paymentSettings = get_option('mbc_paymentSettings');
			$paymentSettings = unserialize($paymentSettings);
			$paymentAvailable = false;
			$paymentGateways = '';
			if($paymentSettings["cash"]["activate"] == 'true') {
				$cashchecked = '';
				if($paymentAvailable == false) $cashchecked = 'checked';
				$paymentGateways .= '<input type="radio" name="payment" id="cash" value="cash" '.$cashchecked.' />&nbsp;<label for="cash">'.$paymentSettings["cash"]["text"].'</label><br />';
				$paymentAvailable = true;
			}
			if($paymentSettings["onInvoice"]["activate"] == 'true') {
				$inchecked = '';
				if($paymentAvailable == false) $inchecked = 'checked';
				$paymentGateways .= '<input type="radio" name="payment" id="onInvoice" value="onInvoice" '.$inchecked.' />&nbsp;<label for="onInvoice">'.$paymentSettings["onInvoice"]["text"].'</label>';
				$paymentAvailable = true;
			}
			
			
			$bookingFormColumn = ceil(($bookingFormSetting["inputs"]+1)/2);
			$rowCount = 0;
			if($bookingFormSetting["firstname"] > 0){
				$bookingFormOutput .= '<div class="mbc-column">
						<label for="first_name">'.__('First Name', 'multilang-booking-calendar').'</label>
						<input type="text" id="first_name" name="first_name" class="mbc-form" placeholder="'.__('Jhon', 'multilang-booking-calendar').'">
					</div>';
				$rowCount++;
			}
			if($bookingFormSetting["lastname"] > 0){
				$bookingFormOutput .= '<div class="mbc-column">
						<label for="last_name">'.__('Last Name', 'multilang-booking-calendar').'</label>
						<input type="text" id="last_name" name="last_name" placeholder="'.__('Doe', 'multilang-booking-calendar').'">
					</div>';
				$rowCount++;
			}
			$bookingFormOutput .= '<div class="mbc-column">
						<label for="email">'.__('Email Address', 'multilang-booking-calendar').'</label>
						<input type="email" id="email" name="email" placeholder="'.__('xxxxxx@xxxxxx.xx', 'multilang-booking-calendar').'">
					</div>';
			$rowCount++;
			if($bookingFormSetting["phone"] > 0){
				$bookingFormOutput .= '<div class="mbc-column">
						<label for="phone">'.__('Phone Number', 'multilang-booking-calendar').'</label>
						<input type="text" id="phone" name="phone" placeholder="'.__('+XXXXXXXXXXXX', 'multilang-booking-calendar').'">
					</div>';
				$rowCount++;
			}	
			if($bookingFormSetting["street"] > 0){
				$bookingFormOutput .= '<div class="mbc-column">
						<label for="address">'.__('Street Address, House no.', 'multilang-booking-calendar').'</label>
						<input type="text" id="address" name="address" placeholder="'.__('Street Address, House no.', 'multilang-booking-calendar').'">
					</div>';
				$rowCount++;
			}	
			if($bookingFormSetting["city"] > 0){
				$bookingFormOutput .= '<div class="mbc-column">
						<label for="city">'.__('City', 'multilang-booking-calendar').'</label>
						<input type="text" id="city" name="city" placeholder="'.__('City', 'multilang-booking-calendar').'">
					</div>';
				$rowCount++;
			}	
			if($bookingFormSetting["county"] > 0){
				$bookingFormOutput .= '<div class="mbc-column">
						<label for="county">'.__('State / County', 'multilang-booking-calendar').'</label>
						<input type="text" id="county" name="county" placeholder="'.__('State / County', 'multilang-booking-calendar').'">
					</div>';
				$rowCount++;
			}	
			if($bookingFormSetting["country"] > 0){
				$bookingFormOutput .= '<div class="mbc-column">
						<label for="country">'.__('Country', 'multilang-booking-calendar').'</label>
						<input type="text" id="country" name="country" placeholder="'.__('Country', 'multilang-booking-calendar').'">
					</div>';
				$rowCount++;
			}	
			if($bookingFormSetting["zip"] > 0){
				$bookingFormOutput .= '<div class="mbc-column">
						<label for="zip">'.__('ZIP Code', 'multilang-booking-calendar').'</label>
						<input type="text" id="zip" name="zip" placeholder="'.__('ZIP Code', 'multilang-booking-calendar').'">
					</div>';
				$rowCount++;
			}	
			if($bookingFormSetting["message"] > 0){
				$bookingFormOutput .= '<div class="mbc-column mbc-fullcolumn">
						<label for="message">'.__('Message', 'multilang-booking-calendar').'</label>
						<textarea id="message" name="message" placeholder="'.__('Leave us an aditional message if you have any other request concerning your booking!', 'multilang-booking-calendar').'"></textarea>
					</div>';
				$rowCount++;
			}
			
			#Payment Gateways
			if($paymentAvailable) {
				$bookingFormOutput .= '	
                    </div>
					<div class="mbc-clearfix">
						<hr class="mbc-form-hr" />
					</div>
					<div class="mbc-fullcolumn">';
				$bookingFormOutput .= '<label for="payment">'.__('Payment Selection', 'multilang-booking-calendar').'</label><br />
							'.$paymentGateways;
				$rowCount++;
			}
            $bookingFormOutput .= '</div>
					<div class="mbc-clearfix">
						<hr class="mbc-form-hr" />
					</div>
					'.$extrasOutput.'
					<div class="mbc-fullcolumn">
						<span>
							<b>'.mbc_booking_getCustomText('yourStay').':</b><br/>
							'.mbc_booking_getCustomText('checkin').': '.$abcFromValue.'<br/>
							'.mbc_booking_getCustomText('checkout').': '.$abcToValue.'<br/>
							'.mbc_booking_getCustomText('roomType').': '.$calendarName.'<br/>
							'.$extrasOptional.'</b></span><br/>
						</span>
					</div>
					<div class="mbc-form-row">
						<button class="mbc-submit" id="mbc-bookingform-book-submit" data-persons="'.$abcPersons.'" data-from="'.$abcFromValue.'" 
							data-to="'.$abcToValue.'" data-calendar="'.$calendarId.'" data-extraslist="'.$extrasString.'">
							'.mbc_booking_getCustomText('bookNow').'
						</button>	
					</div>
				</form>	
					';
			$bookingFormOutput .= mbc_booking_setPageview('bookingform/bookingpage'); // Google Analytics Tracking
		}
		echo $bookingFormOutput;
	}
	die();
}
add_action('wp_ajax_mbc_booking_getBookingFormStep2', 'ajax_mbc_booking_getBookingFormStep2');
add_action( 'wp_ajax_nopriv_mbc_booking_getBookingFormStep2', 'ajax_mbc_booking_getBookingFormStep2');


// Creating a booking in the DB and sending a mail to the customer and the owner
function ajax_mbc_booking_getBookingFormBook () {
	global $wpdb;
	$bookingFormOutput = '';
	$bookingForm = getAbcSetting("bookingform");
	if (isset($_POST["from"])  && isset($_POST["to"]) && mbc_booking_formatDateToDB($_POST["from"]) >= date('Y-m-d')
		&& isset($_POST["persons"]) && isset($_POST["calendar"]) && filter_var($_POST["email"],FILTER_VALIDATE_EMAIL)
		&& getAbcAvailability(sanitize_text_field($_POST["calendar"]),sanitize_text_field($_POST["from"]), sanitize_text_field($_POST["to"])) 
		&& mbc_booking_checkMinimumStay(intval($_POST["calendar"]), mbc_booking_formatDateToDB($_POST["from"]), mbc_booking_formatDateToDB($_POST["to"])) == 0
		&& (isset($_POST["firstname"]) || ($bookingForm["firstname"] < 2))  && (isset($_POST["lastname"]) || ($bookingForm["lastname"] < 2))
		&& (isset($_POST["phone"]) || ($bookingForm["phone"] < 2)) && (isset($_POST["address"]) || ($bookingForm["street"] < 2))
		&& (isset($_POST["zip"]) || ($bookingForm["zip"] < 2)) && (isset($_POST["county"]) || ($bookingForm["county"] < 2))
		&& (isset($_POST["city"]) || ($bookingForm["city"] < 2)) && (isset($_POST["country"]) || ($bookingForm["country"] < 2))
		&& (isset($_POST["message"]) || ($bookingForm["message"] < 2))
	){
		// Sanitizing inputs
		$bookingData = array();
		$bookingData["start"] = mbc_booking_formatDateToDB($_POST["from"]);
		$bookingData["end"] = mbc_booking_formatDateToDB($_POST["to"]);
		$bookingData["persons"] = intval($_POST["persons"]);
		$bookingData["calendar_id"] = intval($_POST["calendar"]);
		if(isset($_POST["extraslist"])){
			$bookingData["extras"] = sanitize_text_field($_POST["extraslist"]);
		}else{
			$bookingData["extras"] = '';
		}	
		if(isset($_POST["firstname"])){
			$bookingData["first_name"]= sanitize_text_field($_POST["firstname"]);
		}else{
			$bookingData["first_name"] = '';
		}
		if(isset($_POST["lastname"])){
			$bookingData["last_name"] = sanitize_text_field($_POST["lastname"]);
		}else{
			$bookingData["last_name"] = '';
		}
		if(isset($_POST["email"])){
			$bookingData["email"] = sanitize_email($_POST["email"]);
		}else{
			$bookingData["email"] = '';
		}
		if(isset($_POST["phone"])){			
			$bookingData["phone"] = sanitize_text_field($_POST["phone"]);
		}else{
			$bookingData["phone"] = '';
		}
		if(isset($_POST["address"])){			
			$bookingData["address"] = sanitize_text_field($_POST["address"]);
		}else{
			$bookingData["address"] = '';
		}
		if(isset($_POST["zip"])){			
			$bookingData["zip"] = sanitize_text_field($_POST["zip"]);
		}else{
			$bookingData["zip"] = '';
		}
		if(isset($_POST["city"])){			
			$bookingData["city"] = sanitize_text_field($_POST["city"]);
		}else{
			$bookingData["city"] = '';
		}
		if(isset($_POST["country"])){			
			$bookingData["country"] = sanitize_text_field($_POST["country"]);
		}else{
			$bookingData["country"] = '';
		}
		if(isset($_POST["message"])){			
			$bookingData["message"] = sanitize_text_field($_POST["message"]);
		}else{
			$bookingData["message"] = '';
		}
		if(isset($_POST["county"])){			
			$bookingData["county"] = sanitize_text_field($_POST["county"]);
		}else{
			$bookingData["county"] = '';
		}
		if(!isset($_POST["payment"])){
			$bookingData["payment"] = 'n/a';
		}else{
			$bookingData["payment"] = sanitize_text_field($_POST["payment"]);
		}	
		$bookingData["state"] = "open";
			
		// Saving booking request in DB and getting booking ID
		$bookingData["booking_id"] = setAbcBooking($bookingData);
		
		// Sending emails 
		sendAbcGuestMail($bookingData);
		sendAbcAdminMail($bookingData);
		
		// Returning Thank-You-Page
		$bookingFormOutput .= '
				<div class="mbc-form-row">
					<span class="mbc-result-header">'.mbc_booking_getCustomText('thankYou').'</span></br>
					<span>'.mbc_booking_getCustomText('bookingSummary').'</span>
				</div>';
		$bookingFormOutput .= mbc_booking_setPageview('bookingform/booking-successful'); // Google Analytics Tracking
		
	} else {
		$bookingFormOutput .= '<div class="mbc-form-row">'.__('Something went wrong, your booking could not be completed. Please try again.', 'multilang-booking-calendar').'</div>';
		$bookingFormOutput .= mbc_booking_setPageview('bookingform/booking-error'); // Google Analytics Tracking
	}
	echo $bookingFormOutput;
	die();
}
add_action('wp_ajax_mbc_booking_getBookingFormBook', 'ajax_mbc_booking_getBookingFormBook');
add_action( 'wp_ajax_nopriv_mbc_booking_getBookingFormBook', 'ajax_mbc_booking_getBookingFormBook');
?>