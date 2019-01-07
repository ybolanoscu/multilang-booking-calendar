<?php
function mbc_booking_showSingleCalendar( $atts ) {
	if(!isset($atts['calendar'])) { return '<p>Calendar ID not set. Please check the shortcode.</p>';}
	else {
		global $abcUrl;
		wp_enqueue_style( 'styles-css', $abcUrl.'frontend/css/styles.css' );
		wp_enqueue_style( 'font-awesome', $abcUrl.'frontend/css/font-awesome.min.css' );
		global $wpdb;
		$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'mbc_calendars WHERE id = '.intval($atts['calendar']), ARRAY_A);
		if(isset($er[0])) {
			$divId = uniqid();
			$atts['uniqid'] = $divId;
			wp_enqueue_script('mbc-ajax', $abcUrl.'frontend/js/mbc-ajax.js', array('jquery'));
			wp_localize_script( 'mbc-ajax', 'ajax_mbc_booking_SingleCalendar', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'mbc_nonce' => wp_create_nonce('mbc-nonce'), 'mbc_calendar' =>  $atts['calendar'] ));
			wp_enqueue_script('jquery-ui-button');
			wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
			if(getAbcSetting('firstdayofweek') == 0) {
				$weekdayRow = '<div class="mbc-box mbc-col-day mbc-dayname">'.__('Su', 'multilang-booking-calendar').'</div>
						<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Mo', 'multilang-booking-calendar').'</div>
						<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Tu', 'multilang-booking-calendar').'</div>
						<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('We', 'multilang-booking-calendar').'</div>
						<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Th', 'multilang-booking-calendar').'</div>
						<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Fr', 'multilang-booking-calendar').'</div>
						<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Sa', 'multilang-booking-calendar').'</div>';
			} else {
				$weekdayRow = '<div class="mbc-box mbc-col-day mbc-dayname">'.__('Mo', 'multilang-booking-calendar').'</div>
					<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Tu', 'multilang-booking-calendar').'</div>
					<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('We', 'multilang-booking-calendar').'</div>
					<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Th', 'multilang-booking-calendar').'</div>
					<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Fr', 'multilang-booking-calendar').'</div>
					<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Sa', 'multilang-booking-calendar').'</div>
					<div class="mbc-box mbc-col-day mbc-dayname mbc-dotted">'.__('Su', 'multilang-booking-calendar').'</div>';
			}	
			$calSingleOutput = abcEnqueueCustomCss().'
				<div class="mbc-singlecalendar" data-checkin-'.$divId.'="0" data-offset-'.$divId.'="0" data-month-'.$divId.'="0" id="mbc_singlecalendar_'.$divId.'">
					<div class="mbc-box mbc-single-row">
						<div data-calendar="'.sanitize_text_field($atts['calendar']).'" data-id="'.$divId.'" class="mbc-box mbc-button mbc-single-button-left">
							<button class="fa fa-chevron-left mbc-button-rl"></button>
						</div>
						<div class="mbc-box mbc-month">
							<img alt="'.__('Loading...', 'multilang-booking-calendar').'" src="'.admin_url('/images/wpspin_light.gif').'" class="waiting" id="mbc_single_loading-'.$divId.'" style="display:none" /><span id="singlecalendar-month-'.$divId.'">'.date_i18n('F').' '.date_i18n('Y').'</span></div>
							<div data-calendar="'.sanitize_text_field($atts['calendar']).'" data-id="'.$divId.'" class="mbc-box mbc-button mbc-single-button-right">
								<button class="fa fa-chevron-right mbc-button-rl"></button>
						</div>
					</div>
					<div class="mbc-box mbc-single-row">
					'.$weekdayRow.'
					</div>
					<div id="mbc-calendar-days-'.$divId.'">
						'.mbc_booking_getSingleCalendar($atts).'
					</div>';
				if(isset($atts['legend']) && intval($atts['legend']) == 1){	
					$calSingleOutput .= '<div class="mbc-single-legend">
											<span class="fa fa-square-o mbc-single-legend-available"></span>
											'.mbc_booking_getCustomText('available');
					if($er[0]["maxAvailabilities"] > 1){						
						$calSingleOutput .= '<span class="fa fa-square mbc-single-legend-partly"></span>
											'.mbc_booking_getCustomText('partlyBooked');
					}							
					$calSingleOutput .= '<span class="fa fa-square mbc-single-legend-fully"></span>
										'.mbc_booking_getCustomText('fullyBooked').'
										</div>';
					}
				$calSingleOutput .= '<div id="mbc-booking-'.$divId.'" class="mbc-booking-selection">
					</div>
				</div>';
				return $calSingleOutput;
			
		} else { return ' ID unknown.';}	
	}		
}

function mbc_booking_getMonth($atts){
	if(!isset($atts['month'])) {
		$cMonth = date("n");
	} else {
		$cMonth = date("n") + intval(sanitize_text_field($atts['month']));
	}
	
	$cYear = date("Y");
	 
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;
	 
	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
	$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
	return date_i18n('F', $timestamp).' '.date('Y', $timestamp);
}

function ajax_mbc_booking_getMonth() {
	
	if(!isset( $_POST['mbc_nonce'] ) || !wp_verify_nonce($_POST['mbc_nonce'], 'mbc-nonce') )
		die('Permissions check failed!');
		
	if(!isset($_POST['month'])){
		echo 'Month not set.';
	} else {	
		echo mbc_booking_getMonth($_POST);
	}
	die();
}
add_action('wp_ajax_mbc_booking_getMonth', 'ajax_mbc_booking_getMonth');
add_action( 'wp_ajax_nopriv_mbc_booking_getMonth', 'ajax_mbc_booking_getMonth');

function mbc_booking_getCssForSingleCalendar($currentAvailability, $previousAvailability){
	$cssClass=' ';
	$cssClass = 'mbc-booked ';
	$cssClass = 'mbc-partly-avail ';
	$cssClass = 'mbc-avail mbc-date-selector ';
	$switcher = '';
	if ($previousAvailability == -100){ // First day of the month, therefore previous = current
		$previousAvailability = $currentAvailability;
	}
	switch ($previousAvailability){
		case -1:
			$switcher = 'available';
			break;
		case 0:
			$switcher = 'booked';
			break;
		default:
			$switcher = 'partly';
			break;	
	}
	switch ($currentAvailability){
		case -1:
			$switcher .= '-available';
			break;
		case 0:
			$switcher .= '-booked';
			break;
		default:
			$switcher .= '-partly';
			break;	
	}
	switch ($switcher){
		case 'available-available':
			$cssClass = 'mbc-avail mbc-date-selector ';
			break;
		case 'available-partly':
			$cssClass = 'mbc-avail-partly-avail mbc-date-selector ';
			break;
		case 'available-booked':
			$cssClass = 'mbc-avail-booked mbc-date-selector ';
			break;
		case 'partly-available':
			$cssClass = 'mbc-partly-avail-avail mbc-date-selector ';
			break;
		case 'partly-partly':
			$cssClass = 'mbc-partly-avail mbc-date-selector ';
			break;
		case 'partly-booked':
			$cssClass = 'mbc-partly-booked mbc-date-selector ';
			break;
		case 'booked-available':
			$cssClass = 'mbc-booked-avail mbc-date-selector ';
			break;
		case 'booked-partly':
			$cssClass = 'mbc-booked-partly mbc-date-selector ';
			break;
		case 'booked-booked':
			$cssClass = 'mbc-booked ';
			break;
	}
	return $cssClass;
}

function mbc_booking_getSingleCalendar($atts){
	$dateformat = getAbcSetting('dateformat');
	$firstdayofweek = getAbcSetting('firstdayofweek');
	$calSingleOutput ='';
	$divId = sanitize_text_field($atts['uniqid']);
	if(!isset($atts['month'])) {
		$cMonth = date("n");
	} else {
		$cMonth = date("n") + sanitize_text_field($atts['month']);
	}
	$cYear = date("Y");
	$prev_year = $cYear;
	$next_year = $cYear;
	$prev_month = $cMonth-1;
	$next_month = $cMonth+1;
	 
	if ($prev_month == 0 ) {
		$prev_month = 12;
		$prev_year = $cYear - 1;
	}
	if ($next_month == 13 ) {
		$next_month = 1;
		$next_year = $cYear + 1;
	}
	$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
	$maxday = date("t",$timestamp);
	$thismonth = getdate ($timestamp);
	
	// Getting confirmed Bookings for the current month
	global $wpdb;
	$normFromValue = date("Y-m-", $timestamp).'01';
	$normToValue = date("Y-m-", $timestamp).$maxday;
	$unconfirmedBookings = 'state = \'confirmed\'';
	if(get_option ('mbc_unconfirmed') == 1){
		$unconfirmedBookings = '(state = \'confirmed\' OR state = \'open\')';
	}
	$query = 'SELECT * FROM '.$wpdb->prefix.'mbc_bookings 
			WHERE calendar_id = '.$atts['calendar'].'
			AND '.$unconfirmedBookings.'
			AND ( (start <= \''.$normFromValue.'\' AND end >=\''.$normToValue.'\') 
				OR (start >= \''.$normFromValue.'\' AND end <= \''.$normToValue.'\') 
				OR (start >= \''.$normFromValue.'\' AND start <= \''.$normToValue.'\') 
				OR (start <= \''.$normFromValue.'\' AND end >= \''.$normToValue.'\') 
				OR (end <= \''.$normFromValue.'\' AND end >= \''.$normToValue.'\') 
				OR (end >= \''.$normFromValue.'\' AND end <= \''.$normToValue.'\') 
			)';
	$bookings = $wpdb->get_results($query, ARRAY_A);
	
	$priceDates = array();
	$lastminutePriceDates = array();
	
	 // Getting last minute offers
	$queryLastminute = 'SELECT * FROM `'.$wpdb->prefix.'mbc_seasons_assignment` a
		INNER JOIN `'.$wpdb->prefix.'mbc_seasons` s
		ON a.season_id = s.id
		WHERE a.calendar_id = '.intval(sanitize_text_field($atts['calendar'])).'
		AND a.end >= \''.date("Y-m-d", $timestamp).'\'
		AND s.lastminute != 0
		ORDER BY a.start';
	$er = $wpdb->get_results($queryLastminute, ARRAY_A);
	foreach($er as $row) {
		$time = strtotime(date_i18n("Y-m-d", $timestamp));
		for( $i = 0; $i < $maxday; $i++) {
			if(strtotime($row["start"]) <= $time && strtotime($row["end"]) >= $time) {
				$lastminutePriceDates[date_i18n("Y-m-d", $time)] = $row["price"];
			}
				$time += 86400;
		}
	} 
	
	// Getting Prices for the current month for the standard seasons
	$query = 'SELECT * FROM `'.$wpdb->prefix.'mbc_seasons_assignment` a 
		INNER JOIN `'.$wpdb->prefix.'mbc_seasons` s 
		ON a.season_id = s.id 
		WHERE a.calendar_id = '.intval(sanitize_text_field($atts['calendar'])).'
		AND a.end >= \''.date("Y-m-d", $timestamp).'\'
		AND s.lastminute = 0
		ORDER BY a.start DESC';
	$er = $wpdb->get_results($query, ARRAY_A);
	foreach($er as $row) {
		$time = strtotime(date("Y-m-d", $timestamp));
		for( $i = 0; $i < $maxday; $i++) {
			if(!isset($priceDates[date("Y-m-d", $time)]) && $row["lastminute"] == 0){
				if(strtotime($row["start"]) <= $time && strtotime($row["end"]) >= $time) {
					$priceDates[date("Y-m-d", $time)] = $row["price"];
				}
				$time += 86400;
			}
		} 
	}
	
	$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'mbc_calendars WHERE id = '.intval(sanitize_text_field($atts['calendar'])), ARRAY_A);
	$maxAvailability = $er["maxAvailabilities"];
	$pricePreset = esc_html($er["pricePreset"]);
	$calendarName = esc_html($er["name"]);
	$partlyBooked = intval($er["partlyBooked"]);
	$startday = $thismonth['wday'];
	if ($firstdayofweek == 1 ){// If first day of the week is a monday
		if($startday == 0){
			$startday = 7;
		}
	} else {
		$startday += 1;
	}	
	$cTime = $timestamp;
	$emptyDays = 0;
	$availDates = array();
	$prevAvailability = -100;
	for ($i=1; $i<($maxday+$startday); $i++) {
		$cAvailability = '';
		$availDates[date('Y-m-d', $cTime)] = $maxAvailability;
		$cssClass = 'mbc-box mbc-col-day ';
		foreach($bookings as $br) {
			if ($cTime >= strtotime($br["start"]) && $cTime < strtotime($br["end"])){
				$availDates[date('Y-m-d', $cTime)] -= 1;
			}
		}
		if($i % 7 > 1 || $i % 7 == 0 ) {
			$cssClass .= 'mbc-dotted ';
		}
		if($i % 7 == 1) {
			$calSingleOutput .= '<div class="mbc-box mbc-single-row">';
		}
		if($i < $startday){
			$calSingleOutput .='<div class="'.$cssClass.'">&nbsp;</div>';
			$emptyDays++;
		} else {
			$newCurrentTime = $cTime-86400*$emptyDays; // Getting a new current time, due to "empty days"
			$cPrice = 0;
			if(isset($priceDates[date('Y-m-d', $newCurrentTime)])) {
				$cPrice = mbc_booking_formatPrice($priceDates[date('Y-m-d', $newCurrentTime)]);
			} else {
				$cPrice = mbc_booking_formatPrice($pricePreset);
			}
			$priceOutput = '<br /><span class="mbc-single-price">';
			$title = '';
			if(isset($atts["start"]) && isset($atts["end"])){
				// Check if date has been selected by user
				if(date('Y-m-d', $newCurrentTime) >= sanitize_text_field($atts["start"]) && date('Y-m-d', $newCurrentTime) <= sanitize_text_field($atts["end"])){ 
					$cssClass .= 'mbc-date-selected ';
				}
			}
			if(isset($availDates[date('Y-m-d', $newCurrentTime)]) && date('Y-m-d', $newCurrentTime)>= date('Y-m-d') ) {
				$cAvailability = $availDates[date('Y-m-d', $newCurrentTime)];
				if($cAvailability == 0){ 
					$cAvailability = '0';
					$cssClass .= mbc_booking_getCssForSingleCalendar($cAvailability, $prevAvailability);
					$priceOutput .= '&nbsp;';
					$title = mbc_booking_getCustomText('fullyBooked');
					$prevAvailability = $cAvailability;
				}elseif($cAvailability > 0 && ($maxAvailability-$cAvailability) >= $partlyBooked){
					$cssClass .= mbc_booking_getCssForSingleCalendar($cAvailability, $prevAvailability);
					$priceOutput .= $cPrice;
					$title = mbc_booking_getCustomText('partlyAvailable')."\n".date($dateformat, $newCurrentTime)/*.": ".$cPrice*/;
					$prevAvailability = $cAvailability;
				}else{
					$cAvailability = -1;
					$cssClass .= mbc_booking_getCssForSingleCalendar($cAvailability, $prevAvailability);
					$prevAvailability = -1;
					$priceOutput .= $cPrice;
					$title = mbc_booking_getCustomText('available').":\n".date($dateformat, $newCurrentTime)/*.": ".$cPrice*/;
				}
				switch ($cAvailability) {
					case $maxAvailability:
						break;
					case 0:
						break;
					default:
						break;
				}
			} elseif(date('Y-m-d', $newCurrentTime)>= date('Y-m-d')) {
				$cAvailability = -1;
				$cssClass .= mbc_booking_getCssForSingleCalendar($cAvailability, $prevAvailability);
				$prevAvailability = -1;
				$priceOutput .= $cPrice;
				$title = mbc_booking_getCustomText('available').":\n".date($dateformat, $newCurrentTime)/*.": ".$cPrice*/;
			} else {
				$cssClass .= 'mbc-past ';
			}
			$priceOutput .= '</span>';
			$cssClass .= 'mbc-date-item ';
			$calSingleOutput .='<div title="'.$title.'" data-calendar="'.intval(sanitize_text_field($atts['calendar'])).'" data-id="'.$divId.'" data-date="'.date('Y-m-d', $newCurrentTime).'" class="'.$cssClass.'" id="mbc-day-'.$divId.date('Y-m-d', $newCurrentTime).'">'.date('j', ($newCurrentTime))./*$priceOutput.*/'</div>';
		}
		if($i % 7 == 0 OR $i == ($maxday+$startday-1)) { // Closing row if week is over or last day of month has been reached.
			$calSingleOutput .= '</div>';
		}
		$cTime += 86400;
	}
	$calSingleOutput .= mbc_booking_setPageview('single-calendar/'.sanitize_title_with_dashes($calendarName).'/'.date_i18n('Y-m', $timestamp)); // Google Analytics Tracking
	return $calSingleOutput;
}

function ajax_mbc_booking_getSingleCalendar() {
	
	if(!isset( $_POST['mbc_nonce'] ) || !wp_verify_nonce($_POST['mbc_nonce'], 'mbc-nonce') )
		die('Permissions check failed!');
		
	if(!isset($_POST['month'])){
		echo 'Month not set.';
	} else {	
		echo mbc_booking_getSingleCalendar($_POST);
	}
	die();
}
add_action('wp_ajax_mbc_booking_getSingleCalendar', 'ajax_mbc_booking_getSingleCalendar');
add_action( 'wp_ajax_nopriv_mbc_booking_getSingleCalendar', 'ajax_mbc_booking_getSingleCalendar');

// Called by jQuery, when user clicks on available dates.
function ajax_mbc_booking_setDataRange() {
	$output = '';
	$success = false; // Triggers Google Analytics Tracking, if user selected a date range
	if(!isset( $_POST['mbc_nonce'] ) || !wp_verify_nonce($_POST['mbc_nonce'], 'mbc-nonce') )
		die('Permissions check failed!');
		
	if(!isset($_POST['start']) OR !isset($_POST['end'])){
		$output = 'Dates not set.';
	} else {
		$start = strtotime(sanitize_text_field($_POST['start']));
		$end = strtotime(sanitize_text_field($_POST['end']));
		$calendarId = sanitize_text_field($_POST['calendar']);
		$dateformat = getAbcSetting('dateformat');
		$currency = getAbcSetting('currency');
		if($start != 0){
			$output .= '<div class="mbc-column"><b>'.mbc_booking_getCustomText('checkin').':</b> '.date($dateformat, $start).'<br/>
				<b>'.mbc_booking_getCustomText('checkout').':</b> ';
			if($end != 0 && $end > $start){
				$success = true;
				$output .= date($dateformat, $end);
				$numberOfDays = mbc_booking_dateDiffInDays($end, $start);
				//$output .= '<br/><b>'.mbc_booking_getCustomText('roomPrice').': </b>
				//		'.mbc_booking_formatPrice(mbc_booking_getTotalPrice($calendarId, date("Y-m-d", $start), $numberOfDays));
				$minimumStay = mbc_booking_checkMinimumStay($calendarId, sanitize_text_field($_POST['start']), sanitize_text_field($_POST['end']));
				if($minimumStay > 0){ // Checking if the minimum number of nights to stay is reached
					$output .= '</div>
						<div class="mbc-column"><b>'.sprintf( __('Your stay is too short. Minimum stay for those dates is %d nights.', 'multilang-booking-calendar'), $minimumStay ).'</b>';
				}elseif(getAbcSetting("bookingpage") > 0 && get_option('mbc_bookingformvalidated') == 1){ // Checking if bookingpage in the settings has been defined
					$output .='</div>
						<div class="mbc-column">
							<form action="'.get_permalink(getAbcSetting("bookingpage")).'" method="post">
							<button class="mbc-submit">
								<span class="mbc-submit-text">'.mbc_booking_getCustomText('bookNow').'</span>
							</button>
							';
				}
				if(getAbcSetting("cookies") == 1) { // Storing selected dates in cookie, if activated
					$domain = str_replace('www', '', str_replace('https://','',str_replace('http://','',get_site_url()))); // Getting domain-name for creating cookies
					setcookie('mbc-from', date($dateformat, $start), time()+3600*24*30*6, '/',  $domain);
					setcookie('mbc-to', date($dateformat, $end), time()+3600*24*30*6, '/', $domain );
					setcookie('mbc-calendar', $calendarId, time()+3600*24*30*6, '/', $domain );
				} 
					$output .= '<input type="hidden" name="mbc-from" value="'.date($dateformat, $start).'">';
					$output .= '<input type="hidden" name="mbc-to" value="'.date($dateformat, $end).'">';
					$output .= '<input type="hidden" name="mbc-calendarId" value="'.$calendarId.'">';
					$output .= '<input type="hidden" name="mbc-trigger" value="'.$calendarId.'">';
				$output .= '</form>';	
			} else {
				$output .= '-';
			}
			$output .= '</div><div style="clear:both"></div>';
		}
	}
	if($success){
		global $wpdb;
		$er = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'mbc_calendars WHERE id = '.$calendarId, ARRAY_A);
		$calendarName = esc_html($er["name"]);
		$output .= mbc_booking_setPageview('single-calendar/'.sanitize_title_with_dashes($calendarName).'/date-selected'); // Google Analytics Tracking
	}
	echo $output;
	die();
}
add_action('wp_ajax_mbc_booking_setDataRange', 'ajax_mbc_booking_setDataRange');
add_action( 'wp_ajax_nopriv_mbc_booking_setDataRange', 'ajax_mbc_booking_setDataRange');
?>