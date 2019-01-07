<?php

//Outputs the overview depending on the offset. 
function mbc_booking_getCalOverview ($atts) {
	global $wpdb;
	$dateformat = getAbcSetting('dateformat');
	$firstdayofweek = getAbcSetting('firstdayofweek');
	$divId = sanitize_text_field($atts['uniqid']);
	if(!isset($atts['month'])) {
		$cMonth = date("n");
	} else {
		$cMonth = sanitize_text_field($atts['month']);
	}
	if(!isset($atts['year'])) {
		$cYear = date("Y");
	} else {
		$cYear = sanitize_text_field($atts['year']);
	}
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
	$startDate = mktime(0,0,0,$cMonth,1,$cYear);
	$maxday = date("t",$startDate);
	$thismonth = getdate ($startDate);
	
	$tempDate = $startDate;
	$tableHead = '';
	for($i=0; $i<$maxday ; $i++){ // Creating dates for table head
		$tableHead .= '<th colspan="2" class="abcCellBorderBottom abcCellBorderLeft abcDayNumber';
		if(date_i18n('w', $tempDate)%6 == 0){
			$tableHead .= ' abcDayWeekend';
		}
		$tableHead .= '">
					<span class="abcDayName">'.date_i18n('D', $tempDate).'</span><br/>
					'.date('j', $tempDate).'<br/></th>';
		$tempDate = strtotime('+1 day', $tempDate);
	}
	$endDate = strtotime('-1 day', $tempDate);
	$initialYear = date("Y", $startDate);
	$output = '<div class="mbc-calendar-overview">
				<table>
				<thead>
					<tr>
						<th class="abcDateSelector">
							<button data-id="'.$divId.'" data-year="'.$prev_year.'" data-month="'.$prev_month.'" class="fa fa-chevron-left mbc-button-rl mbc-overview-button-left mbc-overview-button"></button>
							<button data-id="'.$divId.'" data-year="'.$next_year.'" data-month="'.$next_month.'" class="fa fa-chevron-right mbc-button-rl mbc-overview-button-right mbc-overview-button"></button>
						
						<div class="abcDateForm">
							<select data-id="'.$divId.'" class="abcMonthSelector" name="abcMonth" size="1">';
	for($i=1; $i<=12; $i++){
		$output .= '<option value="'.$i.'"';
		if($i == $cMonth){
			$output .= ' selected';
		}
		$output .= '>'.date_i18n("M", strtotime("2016-".$i."-1")).'</option>';
	}
	$output .='							</select>
								<select data-id="'.$divId.'" class="abcYearSelector" name="abcYear" size="1">';
	for ($i = -2; $i <3; $i++){
		$currYear = date("Y", $startDate)+$i;
		$currMonth = date("m", $startDate);
		$output .= '<li><option value="'.$currYear.'"';
		if($currYear == date("Y", $startDate)){
			$output .= ' selected';
		}
		$output .= '>'.$currYear.'</option>';
	} 
	$output .= '						</select>
						</div></th>';
	$output .= $tableHead.'		</tr>
				</thead>
				<tbody>';
	$bookings = array();
	$normFromValue = strtotime("-1 day", $startDate);
	$normToValue = date("Y-m-d", strtotime("+".$maxday."days", $startDate));
	$unconfirmedBookings = 'state = \'confirmed\'';
	if(get_option ('mbc_unconfirmed') == 1){
		$unconfirmedBookings = '(state = \'confirmed\' OR state = \'open\')';
	}
	$bookingQuery = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'mbc_bookings 
					WHERE '.$unconfirmedBookings.'
					AND ( (start <= \''.$normFromValue.'\' AND end >=\''.$normToValue.'\') 
						OR (start >= \''.$normFromValue.'\' AND end <= \''.$normToValue.'\') 
						OR (start >= \''.$normFromValue.'\' AND start <= \''.$normToValue.'\') 
						OR (start <= \''.$normFromValue.'\' AND end >= \''.$normToValue.'\') 
						OR (end <= \''.$normFromValue.'\' AND end >= \''.$normToValue.'\') 
						OR (end >= \''.$normFromValue.'\' AND end <= \''.$normToValue.'\')
						)', ARRAY_A); 
	foreach($bookingQuery as $bookingRow){
		$bookings[$bookingRow["calendar_id"]][] = $bookingRow;  // Getting all confirmed bookings for the current month
	}
	$maxAvailability = array();
	$maxAvailabilityQuery = $wpdb->get_results('SELECT calendar_id, count(calendar_id) as availability FROM '.$wpdb->prefix.'mbc_rooms GROUP BY calendar_id ORDER BY calendar_id', ARRAY_A);
	foreach($maxAvailabilityQuery as $maxAvailabilityRow){
		$maxAvailability[$maxAvailabilityRow["calendar_id"]] = $maxAvailabilityRow["availability"];  // Getting max availabilities per calendar
	}
	$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'mbc_calendars ORDER BY name', ARRAY_A);
	foreach($er as $row) { // Creating rows for table
		$partlyBooked = intval($row["partlyBooked"]);
		$output .= '<tr>
					<td data-uk-tooltip="{pos:\'right\'}" title="'.esc_html($row["infoText"]).'" class="abcCalendarName">';
		if ($row["infoPage"] == 0){
			$output .= esc_html($row["name"]).'</td>';
		}else{
			$output .= '<a href="'.get_permalink($row["infoPage"]).'">'.esc_html($row["name"]).'</a></td>';
		}
			
		$cTime = $normFromValue;
		for ($i=0; $i<($maxday*2); $i++) {
			$cAvailability = '';
			$availDates[date('Y-m-d', $cTime)] = $maxAvailability[$row["id"]];
			$cssClass = 'mbc-box mbc-col-day ';
			$titleText = '';
			if(isset($bookings[$row["id"]])){
				foreach($bookings[$row["id"]] as $br) {
					if ($cTime >= strtotime($br["start"]) && $cTime < strtotime($br["end"])){
						$availDates[date('Y-m-d', $cTime)] -= 1;
					}
				}
			}
			$cssClass = '';
			if($availDates[date('Y-m-d', $cTime)] == $maxAvailability[$row["id"]] 
				|| (($maxAvailability[$row["id"]] - $availDates[date('Y-m-d', $cTime)]) < $partlyBooked && $availDates[date('Y-m-d', $cTime)] > 0)
				){
				$cssClass .= " abcDayAvail";
				$titleText = __('Available', 'multilang-booking-calendar');
			}elseif($availDates[date('Y-m-d', $cTime)] < $maxAvailability[$row["id"]] && $availDates[date('Y-m-d', $cTime)] !=0){
				$cssClass .= " abcDayPartly";
				$titleText = __('Partly booked', 'multilang-booking-calendar');
			}else{
				$cssClass .= " abcDayBooked";
				$titleText = __('Fully booked', 'multilang-booking-calendar');
			}
			if($i%2 ==0){
				$cssClass .= ' abcCellBorderLeft';
			}
			$output .= '<td class="'.$cssClass.'" title="'.$titleText.'">&nbsp;</td>';
			if($i%2==0 ){
				$cTime = strtotime('+1 day', $cTime);
			}
		}	
		$output .= '</tr>';
	}	
	$output .= '</tbody>
			</table>
		</div>';
	$output .= mbc_booking_setPageview('calendar-overview/'.date('Y-m', $startDate)); // Google Analytics Tracking
	return $output; 
}

//AJAX-Request for updating the overview in the shortcode without reload.
function ajax_mbc_booking_getCalOverview () {
	if(!isset( $_POST['mbc_nonce'] ) || !wp_verify_nonce($_POST['mbc_nonce'], 'mbc-nonce') ){
		die('Permissions check failed!');
	}	
	if(!isset($_POST['month'])){
		echo 'Month not set.';
	} else {	
		echo mbc_booking_getCalOverview($_POST);
	}
	die();
}
add_action('wp_ajax_mbc_booking_getCalOverview', 'ajax_mbc_booking_getCalOverview');
add_action( 'wp_ajax_nopriv_mbc_booking_getCalOverview', 'ajax_mbc_booking_getCalOverview');


//Function for shortcode "mbc-overview". Shows availabilities for all calendars.
function mbc_booking_showCalOverview ( $atts ) {
	global $abcUrl;
	$divId = uniqid();
	wp_enqueue_style( 'styles-css', $abcUrl.'frontend/css/styles.css' );
	wp_enqueue_style( 'font-awesome', $abcUrl.'frontend/css/font-awesome.min.css' );
	wp_enqueue_style( 'tooltip', $abcUrl.'backend/css/tooltip.gradient.min.css' );
	wp_enqueue_script('jquery-ui-button');
	wp_enqueue_script('mbc-ajax', $abcUrl.'frontend/js/mbc-ajax.js', array('jquery'));
	wp_enqueue_script('uikit-js', $abcUrl.'backend/js/uikit.min.js', array('jquery'));
	wp_enqueue_script('jqury-tooltip', $abcUrl.'backend/js/tooltip.min.js', array('jquery'));
	wp_localize_script( 'mbc-ajax', 'ajax_mbc_booking_calOverview', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'mbc_nonce' => wp_create_nonce('mbc-nonce')));
	$calOverviewResult = '<!-- Begin Multilang Booking Calendar WordPress plugin: https://www.booking-calendar-plugin.com -->'.abcEnqueueCustomCss();
	$legend = '';
	if(isset($atts['legend']) && intval($atts['legend']) == 1){
		global $wpdb;
		$row = $wpdb->get_row('SELECT max(maxAvailabilities) as roomMax FROM '.$wpdb->prefix.'mbc_calendars', ARRAY_A);
		$legend .= '<div class="mbc-overview-legend">
				<span class="fa fa-square mbc-overview-legend-available"></span>
				'.__('Available', 'multilang-booking-calendar');
		if($row["roomMax"] > 1){
			 $legend .= '<span class="fa fa-square mbc-overview-legend-partly"></span>
					'.__('Partly booked', 'multilang-booking-calendar');
		}		
		$legend .= '<span class="fa fa-square mbc-overview-legend-fully"></span>
				'.__('Fully booked', 'multilang-booking-calendar').'
			</div>';
	}	
	$atts = shortcode_atts(
		array(
			'hidetext' => 'no',
			'days' => 0,
			'uniqid' => $divId
		), $atts, 'mbc-overview' );
	$calOverviewResult .= '
		<div class="mbc-box mbc-calendar-overview" id="mbc-calendaroverview-'.$divId.'">
				'.mbc_booking_getCalOverview($atts).'
		</div>'.$legend;
	if(getAbcSetting('poweredby') == 1){
		$calOverviewResult .= '<div class="mbc-powered-by">
				'.__('Powered by:', 'multilang-booking-calender').'&nbsp;
<a href="https://booking-calendar-plugin.com" target="_blank">Multilang Booking Calendar</a>
			</div>';
	}
	$calOverviewResult .='<!-- End Multilang Booking Calendar WordPress plugin: https://www.booking-calendar-plugin.com -->';
	return $calOverviewResult; 
}

?>