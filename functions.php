<?php
function mbc_booking_admin_capabilities($return_capabilities_array = false) {
	$caps = array( 
		'Subscriber' =>'read',
		'Contributor'=>'edit_posts',
		'Author'=>'edit_published_posts',
		'Editor'=> 'moderate_comments',
		'Administrator'=> 'manage_options'
	);
	if($return_capabilities_array){
		return $caps;
	}
	$capability = getAbcSetting("accessLevel");
	if(!$capability)
		$capability = 'Administrator';

		$capability = $caps[$capability];

		if(current_user_can( $capability )){
			return $capability;
		} else {
			return $caps['Administrator'];
		}
}

function mbc_booking_getCustomText($text){
	$customText = 'Unknown Custom Text '.$text;
	$textCustomization = get_option('mbc_textCustomization');
	$textArray = array();
	if($textCustomization != false){
		$textArrayTemp = unserialize(get_option('mbc_textCustomization'));
		if (isset($textArrayTemp[get_locale()])){
			$textArray = $textArrayTemp[get_locale()];
		}
	}	
	if(isset($textArray[$text]) && strlen($textArray[$text]) > 0){
		$customText = $textArray[$text];
	}else{
		switch ($text) {
			case 'checkAvailabilities':
				$customText = __('Check availabilities', 'multilang-booking-calendar');
				break;
			case 'selectRoom':
				$customText = __('Select room', 'multilang-booking-calendar');
				break;
			case 'selectedRoom':
				$customText = __('Selected room', 'multilang-booking-calendar');
				break;
			case 'otherRooms':
				$customText = __('Other available rooms for your stay', 'multilang-booking-calendar');
				break;
			case 'noRoom':
				$customText = __('No rooms available for your search request.', 'multilang-booking-calendar');
				break;
			case 'availRooms':
				$customText = __('Available rooms for your stay', 'multilang-booking-calendar');
				break;
			case 'roomType':
				$customText = __('Room type', 'multilang-booking-calendar');
				break;
			case 'yourStay':
				$customText = __('Your stay', 'multilang-booking-calendar');
				break;
			case 'checkin':
				$customText = __('Checkin', 'multilang-booking-calendar');
				break;
            case 'checkout':
                $customText = __('Checkout', 'multilang-booking-calendar');
                break;
            case 'persons':
                $customText = __('Persons', 'multilang-booking-calendar');
                break;
			case 'bookNow':
				$customText = __('Book now', 'multilang-booking-calendar');
				break;
            case 'thankYou':
                $customText = __('Thank you for your booking request!', 'multilang-booking-calendar');
                break;
            case 'bookingSummary':
                $customText = __('We have sent you an email including a summary of your booking!', 'multilang-booking-calendar');
                break;
            case 'roomPrice':
                $customText = __('Price for the room', 'multilang-booking-calendar');
                break;
            case 'editButton':
                $customText = __('EDIT', 'multilang-booking-calendar');
                break;
            case 'continueButton':
                $customText = __('CONTINUE', 'multilang-booking-calendar');
                break;
            case 'fullyBooked':
                $customText = __('Fully Booked', 'multilang-booking-calendar');
                break;
            case 'partlyAvailable':
                $customText = __('Partly Available', 'multilang-booking-calendar');
                break;
            case 'available':
                $customText = __('Available', 'multilang-booking-calendar');
                break;
            case 'partlyBooked':
                $customText = __('Partly Booked', 'multilang-booking-calendar');
                break;
		}
	}	
	return $customText;
}

function mbc_booking_formatPrice($price){ // Returns price with currency symbol at the right place
    $price = floatval($price);
    $decimals = 0;
    if(intval($price) != $price){
        $decimals = 2;
    }
    if(getAbcSetting('priceformat') == ','){
        $price = number_format($price, $decimals, ',', '.');
    } else{
        $price = number_format($price, $decimals);
    }
    if(getAbcSetting('currencyPosition') == 0 ){
        $price = getAbcSetting('currency')." ".$price;
    } else {
        $price = $price." ".getAbcSetting('currency');
    }
    return $price;
}

function mbc_booking_setPersonCount(){ // Returns price with currency symbol at the right place
    global $wpdb;
    $er = $wpdb->get_row('SELECT max(maxUnits) as maxPerson FROM '.$wpdb->prefix.'mbc_calendars', ARRAY_A);
    $personCount = 2;
    if(intval($er['maxPerson']) > 0){
        $personCount = intval($er['maxPerson']);
    }
    update_option('mbc_personcount', $personCount);
}

function mbc_booking_setPageview($pagename){ // Returns tracking code for a pagename
    $rand = rand(1, 1000);
    $output = '';
    if(getAbcSetting('googleanalytics') == 1){
        $output = '<script>
		if (typeof ga == \'function\'){
			ga(\'send\', \'pageview\', \''.$pagename.'\')			
		} </script>';
    }
    return $output;
}

function mbc_booking_validateDate($date, $format = 'Y-m-d')
{
    $dateValid = false;
    switch ($format) {
        case 'd.m.Y':
            if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\.(0[1-9]|1[0-2])\.[0-9]{4}$/",$date))
            {
                $dateValid = true;
            }
            break;
        case 'd/m/Y':
            if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/",$date))
            {
                $dateValid = true;
            }
            break;
        case 'm/d/Y':
            if (preg_match("/^(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/[0-9]{4}$/",$date))
            {
                $dateValid = true;
            }
            break;
        case 'Y-m-d':
            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date))
            {
                $dateValid = true;
            }
            break;
    }
    return $dateValid;
}

function mbc_booking_formatDate($string) {
    $dateformat = getAbcSetting('dateformat');
    $old_date_timestamp = strtotime($string);
    $new_date = date($dateformat, $old_date_timestamp);
    return $new_date;
}

function mbc_booking_formatDateToDB($string) {
    $dateformat = getAbcSetting("dateformat");
    $newDate = '';
    $day = '';
    $month = '';
    $year = '';
    switch ($dateformat) {
        case 'd.m.Y':
        case 'd/m/Y':
            $day = substr($string, 0, 2);
            $month = substr($string, 3, 2);
            $year = substr($string, 6, 4);
            $newDate = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            break;
        case 'm/d/Y':
            $day = substr($string, 3, 2);
            $month = substr($string, 0, 2);
            $year = substr($string, 6, 4);
            $newDate = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
            break;
        case 'Y-m-d':
            $newDate = $string;
            break;
    }
    return $newDate;
}


function mbc_booking_dateDiffInDays($timestamp1, $timestamp2){
    $seconds1 = date("U", $timestamp1);
    $seconds2 = date("U", $timestamp2);

    $diffSeconds = $timestamp1 - $timestamp2;
    $diffSeconds = $diffSeconds/86400;

    $days = (int)$diffSeconds;
    return $days;
}

function mbc_booking_dateFormatToJS($string) {
    $dateformat = 'mm/dd/yy';
    if($string == "Y-m-d") {
        $dateformat = 'yy-mm-dd';
    } elseif($string == "d.m.Y") {
        $dateformat = 'dd.mm.yy';
    } elseif($string == "d/m/Y") {
        $dateformat = 'dd/mm/yy';
    } elseif($string == "m/d/Y") {
        $dateformat = 'mm/dd/yy';
    }
    return $dateformat;
}

function getAbcSetting($settingName) {
    $output = '';
    if($settingName == 'firstdayofweek'){
        $output = get_option('start_of_week');
    } else {
        $output = get_option('mbc_'.$settingName);
    }
    return $output;
}

// Returns 0 if the minimum number of nights to stay for a certain time is reached. Returns the number of nights needed if no reached.
function mbc_booking_checkMinimumStay($calendarId, $normFromValue, $normToValue) {
    global $wpdb;
    $calendarId = intval($calendarId);
    $minimumStay = 1;
    $er = $wpdb->get_row('SELECT max(s.minimumStay) as minimumStay FROM '.$wpdb->prefix.'mbc_seasons s 
		INNER JOIN '.$wpdb->prefix.'mbc_seasons_assignment sa ON sa.season_id = s.id
		WHERE 
			sa.calendar_id = '.$calendarId.'
			AND (  
					(sa.start <= \''.$normFromValue.'\' AND sa.end >=\''.$normToValue.'\') 
					OR (sa.start >= \''.$normFromValue.'\' AND sa.end <= \''.$normToValue.'\') 
					OR (sa.start >= \''.$normFromValue.'\' AND sa.start < \''.$normToValue.'\') 
					OR (sa.start <= \''.$normFromValue.'\' AND sa.end >= \''.$normToValue.'\') 
					OR (sa.end <= \''.$normFromValue.'\' AND sa.end >= \''.$normToValue.'\') 
					OR (sa.end > \''.$normFromValue.'\' AND sa.end <= \''.$normToValue.'\')
				)', ARRAY_A);
    if($er["minimumStay"] > 0){
        $minimumStay = $er["minimumStay"];
    }else{
        $er = $wpdb->get_row('SELECT minimumStayPreset FROM '.$wpdb->prefix.'mbc_calendars WHERE id = '.$calendarId, ARRAY_A);
        $minimumStay = $er["minimumStayPreset"];
    }

    if(mbc_booking_dateDiffInDays(strtotime($normToValue), strtotime($normFromValue)) >= $minimumStay){
        return 0;
    }else{
        return $minimumStay;
    }
}

function mbc_booking_getTotalPrice($calendarId, $startDate, $numberOfDays) {
    global $wpdb;
    $calendarId = intval($calendarId);
    $totalSum = 0;
    $normFromValue = $startDate;
    $er = $wpdb->get_row('SELECT pricePreset FROM '.$wpdb->prefix.'mbc_calendars WHERE id = '.$calendarId, ARRAY_A);
    $pricePreset = $er["pricePreset"];
    $query = 'SELECT * FROM `'.$wpdb->prefix.'mbc_seasons_assignment` a 
		INNER JOIN `'.$wpdb->prefix.'mbc_seasons` s 
		ON a.season_id = s.id 
		WHERE a.calendar_id = '.$calendarId.'
		AND a.end >= \''.$normFromValue.'\'
		ORDER BY s.lastminute DESC, a.start ASC';
    $er = $wpdb->get_results($query, ARRAY_A);
    $days = array();
    $dayCount = 0;
    if($wpdb->num_rows > 0){
        foreach($er as $row) {
            $time = strtotime($normFromValue);
            for( $i = 0; $i < $numberOfDays; $i++) {
                if(strtotime($row["start"]) <= $time && strtotime($row["end"]) >= $time && !isset($days[date("Y-m-d", $time)]) ) {
                    $totalSum += $row["price"];
                    $days[date("Y-m-d", $time)] = true;
                    $dayCount++;
                }
                $time += 86400;
            }
        }
        if($dayCount < $numberOfDays){
            $totalSum += ($numberOfDays - $dayCount)*$pricePreset;
        }
    }else{
        $totalSum += $pricePreset*$numberOfDays;
    }

    return $totalSum;
}

function mbc_booking_getBookingVars(){
    $bookingVars = array('mbc_calendar_name', 'mbc_total_price', 'mbc_room_price', 'mbc_optional_extras', 'mbc_mandatory_extras', 'mbc_checkin_date', 'mbc_checkout_date', 'mbc_person_count', 'mbc_first_name', 'mbc_last_name',
        'mbc_email', 'mbc_phone', 'mbc_address', 'mbc_zip', 'mbc_city', 'mbc_county', 'mbc_country', 'mbc_message', 'mbc_payment', 'mbc_discount');
    return $bookingVars;
}

function mbc_booking_setContentTypeHTML($content_type){
    return 'text/html';
}

// Returns a snapshot of the availability table for a booking. Used by sendAbcAdminMail()
function mbc_booking_getAvailabilityOverview($bookingData){
    global $wpdb;
    $dateformat = getAbcSetting("dateformat");
    $normFromValue = $bookingData["start"];
    $normToValue = $bookingData["end"];
    $startDate = strtotime('-4 days', strtotime($normFromValue)); // +/- 4 days to show what is going on around the current booking
    $endDate = strtotime('+4 days', strtotime($normToValue));
    $numberOfDays = mbc_booking_dateDiffInDays($endDate, $startDate);
    if($numberOfDays > 20){
        $endDate = strtotime('+24 days', $startDate);
    }
    $output = '
	<table style="width: 100%">
		<tr>
		<td>&nbsp;</td>';
    for($tempDate = $startDate; $tempDate <= $endDate; $tempDate = strtotime('+1 day', $tempDate)){
        $output .= '<td  width="10px;" colspan="2" style="font-size: 8px; text-align:center; border-left: 1px solid #dddddd;';
        if(date('w', $tempDate)%6 == 0){
            $output .= 'background-color: #dddddd;';
        }
        $output .= '">'.date_i18n('D', $tempDate).'<br/>
					<span style="font-size: 12px; font-weight: bold;">'.date_i18n('j', $tempDate).'</span><br/>
					'.date_i18n('M', $tempDate).'</td>';
    }
    $output .= '
	</tr>';
    $bookings = array();
    $bookingQuery = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'mbc_bookings WHERE end >= "'.date("Y-m-d", $startDate).'" AND start <= "'.date("Y-m-d", $endDate).'" AND state = "confirmed" ORDER BY start', ARRAY_A);
    foreach($bookingQuery as $bookingRow){
        $bookings[$bookingRow["room_id"]][] = $bookingRow;  // Getting all confirmed bookings for the current timeframe
    }
	if($bookingData['state'] !== 'confirmed'){ // Adding current booking to the already confirmed bookings, except it is a confirmed booking paid via online payment
	    $bookingData["room_id"] = getAbcRoomId($bookingData["calendar_id"], $normFromValue, $normToValue, 1); 
	    $bookings[$bookingData["room_id"]][] = $bookingData;
	}	
    $er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'mbc_rooms WHERE calendar_id='.$bookingData["calendar_id"].' ORDER BY name', ARRAY_A);
    foreach($er as $rooms) {
        $output .= '<tr><td width="60px" style="text-align: center; background-color: #23282d; color: #eeeeee; padding-left: 2px; padding-right: 2px; padding-bottom: 2px; padding-top: 2px; font-size: 12px;">'.$rooms["name"].'</td>';
        $roomRowDate = $startDate;
        for($i = 0; $i < ($numberOfDays*2); $i++){
            $colSpan = 1;
            if (isset($bookings[$rooms["id"]])){ // Checking for bookings for the current room
                $success = false;
                for($j = 0; $j < count($bookings[$rooms["id"]]); $j++){
                    // Checking if a booking started before startDate
                    if($bookings[$rooms["id"]][$j]["start"] < date("Y-m-d", $startDate) && $i==0){
                        $tempEndDate = strtotime($bookings[$rooms["id"]][$j]["end"]);
                        $dayOffset = 0;
                        if ($tempEndDate > $endDate){
                            $tempEndDate = $endDate;
                            $dayOffset = 1;
                            $success = true;
                        }
                        $dateDiff = mbc_booking_dateDiffInDays($tempEndDate, $startDate);
                        $colSpan = ($dateDiff+1)*2;
                        $text = $bookings[$rooms["id"]][$j]["last_name"].', 
								'.sprintf( _n('%d person', '%d persons', $bookings[$rooms["id"]][$j]["persons"], 'multilang-booking-calendar'), $bookings[$rooms["id"]][$j]["persons"] ).', 
								'.date($dateformat, strtotime($bookings[$rooms["id"]][$j]["start"])).' 
								- '.date($dateformat, strtotime($bookings[$rooms["id"]][$j]["end"]));
                        if(mb_strlen($text, "utf-8") > $colSpan*1.5){
                            $text = mb_substr($text, 0, $colSpan*1.5, "utf-8").'...';
                        }
                        $output .= '<td style="font-size: 12px; background-color: ';
                        $output .= getAbcColor($bookings[$rooms["id"]][$j]["id"]%12);
                        $output .= ';" colspan="'.$colSpan.'">'.$text.'</td>';
                        $i += ((mbc_booking_dateDiffInDays($tempEndDate, $startDate))*2)+1+$dayOffset;
                        $roomRowDate = strtotime('+'.$dateDiff.' day', $roomRowDate);

                    }elseif($bookings[$rooms["id"]][$j]["start"] == date("Y-m-d", $roomRowDate) && $i%2==1) {
                        $tempEndDate = strtotime($bookings[$rooms["id"]][$j]["end"]);
                        $dayOffset = 0;
                        $cssClass = '';
                        if ($tempEndDate > $endDate){
                            $tempEndDate = $endDate;
                            $dayOffset = 1;
                            $success = true;
                            $cssClass .= ' abcAvailabilityTableEnding';
                        }
                        $dateDiff = abs(mbc_booking_dateDiffInDays(strtotime($bookings[$rooms["id"]][$j]["start"]), $tempEndDate));
                        $colSpan = ($dateDiff*2)+$dayOffset;
                        $text = $bookings[$rooms["id"]][$j]["last_name"].', 
								'.sprintf( _n('%d person', '%d persons', $bookings[$rooms["id"]][$j]["persons"], 'multilang-booking-calendar'), $bookings[$rooms["id"]][$j]["persons"] ).', 
								'.date($dateformat, strtotime($bookings[$rooms["id"]][$j]["start"])).' 
								- '.date($dateformat, strtotime($bookings[$rooms["id"]][$j]["end"]));
                        if(mb_strlen($text, "utf-8") > $colSpan*1.5){
                            $text = mb_substr($text, 0, $colSpan*1.5, "utf-8").'...';
                        }
                        $output .= '<td style="font-size: 12px; background-color: ';
                        if(isset($bookings[$rooms["id"]][$j]["id"])){
                            $output .= getAbcColor($bookings[$rooms["id"]][$j]["id"]%12);
                        } else {
                            $output .= "#ff2511; color: #ffffff";
                        }
                        $output .= ';" colspan="'.$colSpan.'">'.$text.'</td>';
                        $i += ($dateDiff*2)+$dayOffset;
                        $roomRowDate = strtotime('+'.$dateDiff.' day', $roomRowDate);
                    }
                }
                if(!$success){
                    $output .= '<td style="border-top: 1px solid #dddddd;';
                    if($i%2 ==0){
                        $output .= 'border-left: 1px solid #dddddd;';
                    }
                    $output .= '">&nbsp;</td>';
                }
            } else{
                $output .= '<td style="border-top: 1px solid #dddddd;';
                if($i%2 ==0){
                    $output .= 'border-left: 1px solid #dddddd;';
                }
                $output .= '">&nbsp;</td>';
            }
            if($i%2==1 || $i == 1){
                $roomRowDate = strtotime('+1 day', $roomRowDate);
            }
        }
        $output .= '</tr>';
    }
    $output .= '
	</table>';
    return $output;
}

function sendAbcGuestMail($bookingData){
    global $wpdb;
    $row = $wpdb->get_row('SELECT name FROM '.$wpdb->prefix.'mbc_calendars WHERE id = '.intval($bookingData["calendar_id"]), ARRAY_A);
    $placeholder = array();
    $placeholder["mbc_calendar_name"] = $row["name"];
    $totalPrice = '';
    $roomPrice = 0;
    $dateformat = getAbcSetting('dateformat');
    $normFromValue = $bookingData["start"];
    $normToValue = $bookingData["end"];
    $numberOfDays = floor((strtotime($normToValue) - strtotime($normFromValue))/(60*60*24));
    if(!isset($bookingData["price"])){
        $totalPrice = mbc_booking_getTotalPrice($bookingData["calendar_id"], $normFromValue, $numberOfDays);
    } else {
        $totalPrice = $bookingData["price"];
    }
    $roomPrice = $totalPrice;
    $extrasArray = array();
    $placeholder["mbc_optional_extras"] = '';
    $placeholder["mbc_mandatory_extras"] = '';
    if((isset($bookingData["extras"]) && strlen($bookingData["extras"]) > 0) || getAbcExtrasList($numberOfDays, intval($bookingData["persons"]), 2)){
        $extrasSelected = explode(',', sanitize_text_field($bookingData["extras"]));
        foreach(getAbcExtrasList($numberOfDays, intval($bookingData["persons"])) as $extra){
            if($extra["mandatory"] == 1){
                if(!isset($bookingData["price"])){
                    $totalPrice += $extra["priceValue"];
                } else {
                    $roomPrice -= $extra["priceValue"];
                }
                if(strlen($placeholder["mbc_mandatory_extras"]) > 1){
                    $placeholder["mbc_mandatory_extras"] .= ', ';
                }
                $placeholder["mbc_mandatory_extras"] .= $extra["name"].': '.mbc_booking_formatPrice($extra["priceValue"]);
            }elseif(in_array($extra["id"], $extrasSelected)){
                if(!isset($bookingData["price"])){
                    $totalPrice += $extra["priceValue"];
                } else {
                    $roomPrice -= $extra["priceValue"];
                }
                if(strlen($placeholder["mbc_optional_extras"]) > 1){
                    $placeholder["mbc_optional_extras"] .= ', ';
                }
                $placeholder["mbc_optional_extras"] .= $extra["name"].': '.mbc_booking_formatPrice($extra["priceValue"]);
            }
        }
    }
	
    if(isset($bookingData["coupon"]) && strlen($bookingData["coupon"]) > 0 ){
        $nights = mbc_booking_dateDiffInDays(strtotime($normToValue), strtotime($normFromValue));
        $er = $wpdb->get_row('SELECT co.* FROM '.$wpdb->prefix.'multilang_booking_calendar_coupons co 
                INNER JOIN '.$wpdb->prefix.'multilang_booking_calendar_coupon_calendars cc 
                ON co.id=cc.coupon_id AND cc.calendar_id = '.$bookingData["calendar_id"].'
                WHERE co.valid_from <= \''.$normFromValue.'\' 
                AND co.valid_to >=\''.$normToValue.'\'
                AND co.code = \''.sanitize_text_field($bookingData["coupon"]).'\'', ARRAY_A);
        if($er['id'] > 0 && $er['night_limit'] <= $nights){
        	$couponId = $er['id'];
        	if($er['discount_type'] == 'abs'){
                $discount = abs($er['discount_value']);
            }elseif ($er['discount_type'] == 'rel'){
                $discount = (($er['discount_value']/100))*$totalPrice;
            }
			$placeholder["mbc_discount"] = mbc_booking_formatPrice((-1)*$discount);
            $totalPrice = $totalPrice - $discount;
		}
	}

    if(strlen($placeholder["mbc_optional_extras"]) == 0){
        $placeholder["mbc_optional_extras"] = __('No optional extras.', 'multilang-booking-calendar');
    }
    if(!isset($placeholder["mbc_discount"]) || strlen($placeholder["mbc_discount"]) == 0){
        $placeholder["mbc_discount"] = __('No discount.', 'multilang-booking-calendar');
    }
    if(strlen($placeholder["mbc_mandatory_extras"]) == 0){
        $placeholder["mbc_mandatory_extras"] = __('No mandatory extras.', 'multilang-booking-calendar');
    }
    $placeholder["mbc_room_price"] = mbc_booking_formatPrice($roomPrice);
    $placeholder["mbc_total_price"] = mbc_booking_formatPrice($totalPrice);
    $placeholder["mbc_checkin_date"] = date($dateformat, strtotime($bookingData["start"]));
    $placeholder["mbc_checkout_date"] = date($dateformat, strtotime($bookingData["end"]));
    $placeholder["mbc_person_count"] = $bookingData["persons"];
    $placeholder["mbc_first_name"] = $bookingData["first_name"];
    $placeholder["mbc_last_name"] = $bookingData["last_name"];
    $placeholder["mbc_email"] = $bookingData["email"];
    $placeholder["mbc_phone"]= $bookingData["phone"];
    $placeholder["mbc_address"] = $bookingData["address"];
    $placeholder["mbc_zip"] = $bookingData["zip"];
    $placeholder["mbc_city"] = $bookingData["city"];
    $placeholder["mbc_county"] = $bookingData["county"];
    $placeholder["mbc_country"] = $bookingData["country"];
    $placeholder["mbc_message"] = $bookingData["message"];
	$placeholder["mbc_payment"] = '';
	if(isset($bookingData["payment"])){
    	$placeholder["mbc_payment"] = $bookingData["payment"];
	}
    $adminEmail = getAbcSetting('email');
    if(!filter_var($adminEmail,FILTER_VALIDATE_EMAIL)){
        $adminEmail = get_option('admin_email');
    }
    $subject = '';
    $text = '';
    switch ($bookingData["state"]) {
        case 'open':
            $subject = get_option('mbc_subject_unconfirmed');
            $text = get_option('mbc_text_unconfirmed');
            break;
        case 'confirmed':
            $subject = get_option('mbc_subject_confirmed');
            $text = get_option('mbc_text_confirmed');
            break;
        case 'canceled':
            $subject = get_option('mbc_subject_canceled');
            $text = get_option('mbc_text_canceled');
            break;
        case 'rejected':
            $subject = get_option('mbc_subject_rejected');
            $text = get_option('mbc_text_rejected');
            break;
    }
    $bookingVars = mbc_booking_getBookingVars();
    foreach($bookingVars as $var){
        $subject = str_replace('['.$var.']', $placeholder[$var], $subject);
        $text = str_replace('['.$var.']', $placeholder[$var], $text);
    }
    $headers[] = 'MIME-Version: 1.0' . "\r\n";
    $headers[] = 'From: '.wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ).' <'.$adminEmail.'>'."\r\n";
    wp_mail($placeholder["mbc_email"], stripslashes($subject), stripslashes($text), $headers); // Sending email to customer
    if(getAbcSetting("emailcopy") == "1") { // Sending email copy to admin
    	wp_mail(getAbcSetting('email'), stripslashes(__('EMAIL COPY:', 'multilang-booking-calendar').' '.$subject), stripslashes($text), $headers);
    }
}

function sendAbcAdminMail($bookingData){
    global $wpdb;
    $requestQuery = "SELECT name FROM ".$wpdb->prefix."mbc_calendars WHERE id = ".$bookingData["calendar_id"];
    $er = $wpdb->get_row($requestQuery);
    $calendarName = $er->name;
    $dateformat = getAbcSetting('dateformat');
    $normFromValue = $bookingData["start"];
    $normToValue = $bookingData["end"];
    $totalPrice = 0;
    if(!isset($bookingData["price"])){
        $numberOfDays = floor((strtotime($normToValue) - strtotime($normFromValue))/(60*60*24));
        $totalPrice = mbc_booking_getTotalPrice($bookingData["calendar_id"], $normFromValue, $numberOfDays);
    } else {
        $totalPrice = $bookingData["price"];
    }
    $roomPrice = $totalPrice;
    $extrasArray = array();
    $optionalExtras = '';
    $mandatoryExtras = '';
    if(strlen($bookingData["extras"]) > 0 || getAbcExtrasList($numberOfDays, intval($bookingData["persons"]), 2, $bookingData["calendar_id"])){
        $extrasSelected = explode(',', sanitize_text_field($bookingData["extras"]));
        foreach(getAbcExtrasList($numberOfDays, intval($bookingData["persons"])) as $extra){
            if(in_array($extra["id"], $extrasSelected)){
                $totalPrice += $extra["priceValue"];
                if(strlen($optionalExtras) > 1){
                    $optionalExtras.= ', ';
                }
                $optionalExtras .= $extra["name"].': '.mbc_booking_formatPrice($extra["priceValue"]);
            }elseif($extra["mandatory"] == 1){
                $totalPrice += $extra["priceValue"];
                if(strlen($mandatoryExtras) > 1){
                    $mandatoryExtras .= ', ';
                }
                $mandatoryExtras.= $extra["name"].': '.mbc_booking_formatPrice($extra["priceValue"]);
            }
        }
    }
    $priceOutput = '';
    if(strlen($mandatoryExtras) > 1 || strlen($optionalExtras) > 1){
        if(strlen($mandatoryExtras) > 1){
            $priceOutput = __('Additional costs', 'multilang-booking-calendar').': '.$mandatoryExtras.'<br>';
        }

        if(strlen($optionalExtras) > 1){
            $priceOutput .= __('Selected extras', 'multilang-booking-calendar').': '.$optionalExtras.'<br>';
        }
        $priceOutput .= __('Room price', 'multilang-booking-calendar').': '.mbc_booking_formatPrice($roomPrice).'<br>';
    }
    if(isset($bookingData["coupon"]) && strlen($bookingData["coupon"]) > 0 ){
        $nights = mbc_booking_dateDiffInDays(strtotime($normToValue), strtotime($normFromValue));
        $er = $wpdb->get_row('SELECT co.* FROM '.$wpdb->prefix.'multilang_booking_calendar_coupons co 
                INNER JOIN '.$wpdb->prefix.'multilang_booking_calendar_coupon_calendars cc 
                ON co.id=cc.coupon_id AND cc.calendar_id = '.$bookingData["calendar_id"].'
                WHERE co.valid_from <= \''.$normFromValue.'\' 
                AND co.valid_to >=\''.$normToValue.'\'
                AND co.code = \''.sanitize_text_field($bookingData["coupon"]).'\'', ARRAY_A);
        if($er['id'] > 0 && $er['night_limit'] <= $nights){
        	$couponId = $er['id'];
        	if($er['discount_type'] == 'abs'){
                $discount = abs($er['discount_value']);
            }elseif ($er['discount_type'] == 'rel'){
                $discount = (($er['discount_value']/100))*$totalPrice;
            }
			$priceOutput .= __('Discount', 'multilang-booking-calendar').': '.mbc_booking_formatPrice((-1)*$discount).' ('.__('Coupon', 'multilang-booking-calendar').' '.$er['name'].')<br>';
            $totalPrice = $totalPrice - $discount;
		}
	}
	// Check for online payment
	$paymentOutput = $bookingData["payment"];
	$hideButtons = false;
	$title = __('New Booking Request', 'multilang-booking-calendar');
	if($paymentOutput == 'paypal' && isset($bookingData["payment_reference"])){
		$paymentOutput = __('Already paid via:', 'multilang-booking-calendar').' PayPal (Transaction ID: '.$bookingData["payment_reference"].')';
		$hideButtons = true;
		$title = __('New Booking - Already paid', 'multilang-booking-calendar');
	}
    $priceOutput .= __('Total price', 'multilang-booking-calendar').': '.mbc_booking_formatPrice($totalPrice).'<br>';
    $adminEmail = getAbcSetting('email');
    $headers[] = 'MIME-Version: 1.0' . "\r\n";
    $headers[] = 'Content-type: text/html; charset="UTF-8' . "\r\n";
    $headers[] = 'From: '.wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ).' <'.$adminEmail.'>'."\r\n";
    $subject = __('Booking Request', 'multilang-booking-calendar').' '.wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    $adminBody = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>'.$title.'</title>
  
 <style type="text/css">

@media screen and (max-width: 600px) {
    table[class="container"] {
        width: 95% !important;
    }
}

	#outlook a {padding:0;}
		body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
		.ExternalClass {width:100%;}
		.ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
		#backgroundTable {margin:0; padding:0; width:100% !important; line-height: 100% !important;}
		img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
		a img {border:none;}
		.image_fix {display:block;}
		p {margin: 1em 0;}
		h1, h2, h3, h4, h5, h6 {color: black !important;}

		h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: blue !important;}

		h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {
			color: red !important; 
		 }

		h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
			color: purple !important; 
		}

		table td {border-collapse: collapse;}

		table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }

		a {color: #000;}

		@media only screen and (max-device-width: 480px) {

			a[href^="tel"], a[href^="sms"] {
						text-decoration: none;
						color: black; /* or whatever your want */
						pointer-events: none;
						cursor: default;
					}

			.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
						text-decoration: default;
						color: orange !important; /* or whatever your want */
						pointer-events: auto;
						cursor: default;
					}
		}


		@media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
			a[href^="tel"], a[href^="sms"] {
						text-decoration: none;
						color: blue; /* or whatever your want */
						pointer-events: none;
						cursor: default;
					}

			.mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
						text-decoration: default;
						color: orange !important;
						pointer-events: auto;
						cursor: default;
					}
		}

		h2{
			color:#181818;
			font-family:Helvetica, Arial, sans-serif;
			font-size:22px;
			line-height: 22px;
			font-weight: normal;
		}
		a.link1{
			color:#fff;
			text-decoration:none;
			font-family:Helvetica, Arial, sans-serif;
			font-size:16px;
			border-radius:4px;

		}
		a.link2{
			color:#555555;
			text-decoration:none;
			font-family:Helvetica, Arial, sans-serif;
			font-size:16px;
			border-radius:4px;
		}
		p{
			color:#555;
			font-family:Helvetica, Arial, sans-serif;
			font-size:16px;
			line-height:160%;
		}
	</style>

<script type="colorScheme" class="swatch active">
  {
    "name":"Default",
    "bgBody":"ffffff",
    "link":"fff",
    "color":"555555",
    "bgItem":"ffffff",
    "title":"181818"
  }
</script>

</head>
<body>
<table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0">
  <tr>
    <td align="center" valign="top" bgcolor="#F0F0F0" style="background-color: #F0F0F0;">

      <br>
      <table border="0" width="600" cellpadding="0" cellspacing="0" class="container" style="width:600px;max-width:600px">
        <tr>
          <td class="container-padding header" align="left" style="font-family:Helvetica, Arial, sans-serif;font-size:24px;font-weight:bold;padding-bottom:12px;color:#DF4726;padding-left:24px;padding-right:24px">
            '.$title.'
          </td>
        </tr>
        <tr>
          <td class="container-padding content" align="left" style="padding-left:24px;padding-right:24px;padding-top:12px;padding-bottom:12px;background-color:#ffffff">
            <br>

<div class="title" style="font-family:Helvetica, Arial, sans-serif;font-size:18px;font-weight:600;color:#374550">'.date(getAbcSetting('dateformat'), strtotime($bookingData["start"])).' - '.date(getAbcSetting('dateformat'), strtotime($bookingData["end"])).'</div>
<br>

<div class="body-text" style="font-family:Helvetica, Arial, sans-serif;font-size:14px;line-height:20px;text-align:left;color:#333333">
'.__('Name', 'multilang-booking-calendar').': '.$bookingData["first_name"].' '.$bookingData["last_name"].'<br>
'.__('From', 'multilang-booking-calendar').': '.date($dateformat, strtotime($bookingData["start"])).'<br>
'.__('To', 'multilang-booking-calendar').': '.date($dateformat, strtotime($bookingData["end"])).'<br><br>
'.__('Persons', 'multilang-booking-calendar').': '.$bookingData["persons"].'<br>
'.mbc_booking_getCustomText('roomType').': '.$calendarName.'<br><br>
'.__('Email', 'multilang-booking-calendar').': '.$bookingData["email"].'<br>
'.__('Phone', 'multilang-booking-calendar').': '.$bookingData["phone"].'<br>
'.__('Address', 'multilang-booking-calendar').': '.$bookingData["address"].'<br>
'.__('ZIP Code', 'multilang-booking-calendar').': '.$bookingData["zip"].'<br>
'.__('City', 'multilang-booking-calendar').': '.$bookingData["city"].'<br>
'.__('State / County', 'multilang-booking-calendar').': '.$bookingData["county"].'<br><br>
'.__('Country', 'multilang-booking-calendar').': '.$bookingData["country"].'<br><br>
'.__('Payment Selection', 'multilang-booking-calendar').': '.$paymentOutput.'<br><br>
'.$priceOutput.'
'.__('Message', 'multilang-booking-calendar').': '.$bookingData["message"].'
  <br><br>
</div>
</td>
</tr>
<tr>
 <td class="container-padding content" align="left" style="padding-left:24px;padding-right:24px;padding-top:12px;padding-bottom:12px;background-color:#ffffff">
            
 '.mbc_booking_getAvailabilityOverview($bookingData).'
 <br><br> <br><br>
</td>
</tr>';

if(!$hideButtons){
$adminBody .='	
	<tr>
	<td style="border-radius:4px;" align="center" bgcolor="#0085ba" width="400" height="50">
		<div class="contentEditableContainer contentTextEditable">
	    	<div class="contentEditable" align="center">
				<a target="_blank" href="'.admin_url().'admin-post.php?action=mbc_booking_confBooking&id='.$bookingData["booking_id"].'" class="link1" style="color:#fff">'.__('Click here to confirm', 'multilang-booking-calendar').'</a>
			</div> 
	  	</div>
	</td>
	</tr>
	<tr>
	<td height="20">
		<div class="contentEditableContainer contentTextEditable">
	    	&nbsp;
	  	</div>
	</td>	
    </tr>
	<tr>
	<td style="border-radius:4px;" align="center" bgcolor="f7f7f7"  height="50">
		<div class="contentEditableContainer contentTextEditable">
	    	<div class="contentEditable" align="center">
				<a target="_blank" href="'.admin_url().'admin-post.php?action=mbc_booking_rejBooking&id='.$bookingData["booking_id"].'" class="link2" style="color: #555555">'.__('Click here to reject', 'multilang-booking-calendar').'</a>
			</div> 
	  	</div>
	</td>	
    </tr>
	<tr>
	<td height="20">
		<div class="contentEditableContainer contentTextEditable">
	    	&nbsp;
	  	</div>
	</td>	
    </tr>
	<tr>
	<td style="border-radius:4px;" align="center" bgcolor="f7f7f7"  height="50">
		<div class="contentEditableContainer contentTextEditable">
	    	<div class="contentEditable" align="center">
				<a target="_blank" href="'.admin_url().'admin.php?page=multilang_booking_calendar&action=customMessage&id='.$bookingData["booking_id"].'" class="link2" style="color: #555555">'.__('Answer with custom message', 'multilang-booking-calendar').'</a>
			</div> 
	  	</div>
	</td>	
    </tr>';
}	
$adminBody .='<tr>
      <td class="container-padding footer-text" align="left" style="font-family:Helvetica, Arial, sans-serif;font-size:12px;line-height:16px;color:#aaaaaa;padding-left:24px;padding-right:24px">
        <br><br>
        Are you looking for more features in a Booking Plugin, like PayPal or Stripe gateways?<br/>
		Take a look at our <a target="_blank" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=AdminEmail">Pro Version</a>!<br/>
		Use discount code <b>BASICUPGRADE</b> to save 10€.
        <br><br>
      </td>
    </tr>
    <tr>
      <td class="container-padding footer-text" align="left" style="font-family:Helvetica, Arial, sans-serif;font-size:12px;line-height:16px;color:#aaaaaa;padding-left:24px;padding-right:24px">
        <br><br>
        Sent by your WordPress Site '.wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ).' using <a target="_blank" href="https://www.booking-calendar-plugin.com">Multilang Booking Calendar</a>.
        <br><br>
      </td>
    </tr>
  </table>

    </td>
  </tr>
</table>

</body>
</html>';
    add_filter('wp_mail_content_type', 'mbc_booking_setContentTypeHTML'); // Activating HTML
    wp_mail($adminEmail, $subject, $adminBody, $headers); // Sending email
    remove_filter('wp_mail_content_type',  'mbc_booking_setContentTypeHTML'); // Deactivating HTML
}

function setAbcBooking($bookingData){ // Inserts booking in DB, returns booking ID
    global $wpdb;
    $row = $wpdb->get_row('SELECT name FROM '.$wpdb->prefix.'mbc_calendars WHERE id = '.intval($bookingData["calendar_id"]), ARRAY_A);
    $calendarName = $row["name"];
    $dateformat = getAbcSetting("dateformat");
    // Normalizing entered dates
    $normFromValue = $bookingData["start"];
    $normToValue = $bookingData["end"];
	$calendarId = intval($bookingData["calendar_id"]);
    $roomId = getAbcRoomId(intval($bookingData["calendar_id"]), $bookingData["start"], $bookingData["end"], 1);
    if($roomId < 1){
        die('No room available. Booking canceled.');
    }
    $numberOfDays = floor((strtotime($normToValue) - strtotime($normFromValue))/(60*60*24));
    $totalPrice = mbc_booking_getTotalPrice(intval($bookingData["calendar_id"]), $normFromValue, $numberOfDays);
    $extrasArray = array();
    if(strlen($bookingData["extras"]) > 0 || getAbcExtrasList($numberOfDays, intval($bookingData["persons"]), 2)){
        $extrasSelected = explode(',', sanitize_text_field($bookingData["extras"]));
        foreach(getAbcExtrasList($numberOfDays, intval($bookingData["persons"])) as $extra){
            if(in_array($extra["id"], $extrasSelected) || $extra["mandatory"] == 1){
                $totalPrice += $extra["priceValue"];
                $extrasArray[] = $extra["id"];
            }
        }
    }
	if(!isset($bookingData["payment"])){
		$bookingData["payment"] = 'n/a';
	}
    $wpdb->insert( $wpdb->prefix.'mbc_bookings', array(
        'start' 			  => $normFromValue,
        'end' 		  => $normToValue,
        'calendar_id' => $calendarId,
        'persons' => intval($bookingData["persons"]),
        'first_name'		  => sanitize_text_field($bookingData["first_name"]),
        'last_name'		  => sanitize_text_field($bookingData["last_name"]),
        'email'		  => sanitize_text_field($bookingData["email"]),
        'phone'		  => sanitize_text_field($bookingData["phone"]),
        'address'		  => sanitize_text_field($bookingData["address"]),
        'zip'		  => sanitize_text_field($bookingData["zip"]),
        'city'		  =>sanitize_text_field( $bookingData["city"]),
        'county'		  =>sanitize_text_field( $bookingData["county"]),
        'country'		  => sanitize_text_field($bookingData["country"]),
        'message'		  => sanitize_text_field($bookingData["message"]),
        'price'		  => $totalPrice,
        'state'		  => sanitize_text_field($bookingData["state"]),
        'room_id'		  => $roomId,
    	'payment'        =>sanitize_text_field($bookingData["payment"]),
    	'payment_reference' => '',
    	'created' 		=> date('Y-m-d')
    ));
    $bookingId = $wpdb->insert_id;
    foreach($extrasArray as $extra){
        $wpdb->insert( $wpdb->prefix.'mbc_booking_extras', array(
            'booking_id' 			  => $bookingId,
            'extra_id' 		  => $extra
        ));
    }
    return $bookingId;
}

function getAbcRoomId($calId, $abcFromValue, $abcToValue, $dbFormat = 0, $bookingId = 0) { // Returns id > 0, if rooms is available for a timeperiod
    global $wpdb;
    $roomId = 0;
    $normFromValue = $abcFromValue;
    $normToValue = $abcToValue;
    if($dbFormat == 0){
        $dateformat = getAbcSetting("dateformat");
        // Normalizing entered dates
        $normFromValue = mbc_booking_formatDateToDB($abcFromValue);
        $normToValue = mbc_booking_formatDateToDB($abcToValue);
    }
    $suffix = ''; // Suffix for editing a booking
    if($bookingId > 0){
    	$suffix = ' AND id != '.intval($bookingId);
    }
	$unconfirmedBookings = 'state = \'confirmed\'';
	if(get_option ('mbc_unconfirmed') == 1){
		$unconfirmedBookings = '(state = \'confirmed\' OR state = \'open\')';
	}
    // Getting lowest id for an available room
    $query = 'SELECT count(id) as bookingCount FROM '.$wpdb->prefix.'mbc_bookings WHERE calendar_id = \''.$calId.'\' AND '.$unconfirmedBookings.$suffix;
    $bookingCount = $wpdb->get_row($query, ARRAY_A);
    if($bookingCount['bookingCount'] > 0){
        $query = 'SELECT MIN(r.id ) as roomId FROM '.$wpdb->prefix.'mbc_rooms r 
				WHERE r.calendar_id = \''.$calId.'\'  
				 AND r.id not in (SELECT DISTINCT room_id FROM '.$wpdb->prefix.'mbc_bookings 
					WHERE calendar_id = \''.$calId.'\' 
					AND '.$unconfirmedBookings.' 
					AND (  
						(start <= \''.$normFromValue.'\' AND end >=\''.$normToValue.'\') 
						OR (start >= \''.$normFromValue.'\' AND end <= \''.$normToValue.'\') 
						OR (start >= \''.$normFromValue.'\' AND start < \''.$normToValue.'\') 
						OR (start <= \''.$normFromValue.'\' AND end >= \''.$normToValue.'\') 
						OR (end <= \''.$normFromValue.'\' AND end >= \''.$normToValue.'\') 
						OR (end > \''.$normFromValue.'\' AND end <= \''.$normToValue.'\')
						)
					'.$suffix.'			
					)';
        $er = $wpdb->get_row($query, ARRAY_A);
        if(isset($er["roomId"])){
            $roomId = $er["roomId"];
        } // Else $roomId = 0 ==> Overlap or no availability
    } else {
        $query = 'SELECT min(id) as roomId FROM '.$wpdb->prefix.'mbc_rooms WHERE calendar_id = \''.$calId.'\' ';
        $er = $wpdb->get_row($query, ARRAY_A);
        $roomId = $er["roomId"];
    }
    return $roomId;
}

function getAbcAvailability($calId, $abcFromValue, $abcToValue, $dbFormat = 0, $bookingId = 0) {// Checks if a calendar is availability for a timeframe. Returns true if succesful
    $success = false;
    if(getAbcRoomId($calId, $abcFromValue, $abcToValue, $dbFormat, $bookingId) > 0){
        $success = true;
    }
    return $success;
}

function getAbcExtrasForBooking($bookingId){
    global $wpdb;
    $extras = '';
    $er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'mbc_booking_extras WHERE booking_id = '.intval($bookingId), ARRAY_A);
    foreach($er as $row) {
        if(strlen($extras)>0){
            $extras .= ',';
        }
        $extras .= $row['extra_id'];
    }
    return $extras;
}

function getAbcExtrasList($numberOfDays, $abcPersons, $optionalOnly = 0){
    global $wpdb;
    $extrasList = array();
    $extrasOptional = '';
    $extrasMandatory = '';
    $additionalCosts = 0;
    $condition = '';
    if($optionalOnly == 1){
        $condition = " WHERE mandatory = 'no'";
    }elseif($optionalOnly == 2){
        $condition = " WHERE mandatory = 'yes'";
    }
    $er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'mbc_extras'.$condition.' ORDER BY name', ARRAY_A);
    foreach($er as $row) {
        $tempCosts = 0;
        $tempExplanation = '';
    	if($abcPersons >= $row["persons"]){
	        switch($row["calculation"]){
	            case 'night':
	                $extrasList[$row["id"]]["priceValue"] = $numberOfDays*$row["price"];
	                $numberOfNights = $numberOfDays;
	                $price = mbc_booking_formatPrice($row["price"]);
	                $extrasList[$row["id"]]["priceText"] = sprintf( __('%s for each of the %d nights', 'multilang-booking-calendar'), $price, $numberOfNights);
	                break;
	            case 'day':
	                $extrasList[$row["id"]]["priceValue"] = ($numberOfDays+1)*$row["price"];
	                $days = $numberOfDays+1;
	                $price = mbc_booking_formatPrice($row["price"]);
	                $extrasList[$row["id"]]["priceText"] = sprintf( __('%s for each of the %d days', 'multilang-booking-calendar'), $price, $days);
	                break;
	            case 'once':
	                $extrasList[$row["id"]]["priceValue"] = $row["price"];
	                $price = mbc_booking_formatPrice($row["price"]);
	                $extrasList[$row["id"]]["priceText"] = sprintf( __('%s paid once', 'multilang-booking-calendar'), $price);
	                break;
	            case 'person':
	                $extrasList[$row["id"]]["priceValue"] = $abcPersons*$row["price"];
	                $price = mbc_booking_formatPrice($row["price"]);
	                $extrasList[$row["id"]]["priceText"] = sprintf( __('%s for each of the %d persons', 'multilang-booking-calendar'), $price, $abcPersons);
	                break;
	            case 'personNight':
	                $extrasList[$row["id"]]["priceValue"] = $numberOfDays*$abcPersons*$row["price"];
	                $numberOfNights = $numberOfDays;
	                $price = mbc_booking_formatPrice($row["price"]);
	                $extrasList[$row["id"]]["priceText"] = sprintf( __('%s for each of the %d persons and %d nights', 'multilang-booking-calendar'), $price, $abcPersons, $numberOfNights);
	                break;
	            case 'personDay':
	                $extrasList[$row["id"]]["priceValue"] = ($numberOfDays+1)*$abcPersons*$row["price"];
	                $days = $numberOfDays+1;
	                $price = mbc_booking_formatPrice($row["price"]);
	                $extrasList[$row["id"]]["priceText"] = sprintf( __('%s for each of the %d persons and %d days', 'multilang-booking-calendar'), $price, $abcPersons, $days);
	                break;
	        }
	        $extrasList[$row["id"]]["id"] = intval($row["id"]);
	        $extrasList[$row["id"]]["name"] = esc_html($row["name"]);
	        $extrasList[$row["id"]]["explanation"] = esc_html($row["explanation"]);
	        switch($row["mandatory"]){
	            case 'yes':
	                $extrasList[$row["id"]]["mandatory"] = 1;
	                break;
	            case 'no':
	                $extrasList[$row["id"]]["mandatory"] = 0;
	                break;
	        }
    	}  
    }
    return $extrasList;
}

// Subscribing and unsubscribing to newsletter. Just the email is transmitted, nothing else.
function subscribeAbcNewsletter($email, $unsubscribe = 0){
    $url = 'https://booking-calendar-plugin.com/mc/mailchimp.php';
    $data = array('mail' => urlencode($email), 'unsubscribe' => urlencode($unsubscribe));
    $data_string = '';
    foreach($data as $key=>$value) {
        $data_string .= $key.'='.$value.'&';
    }
    rtrim($data_string, '&');
    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($data));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $data_string);

    $result = curl_exec($ch);
    curl_close($ch);
}

// Activating / Deactivating "commit Usage"
function activate_commitUsage() {
    if (! wp_next_scheduled ( 'commitUsage_event' )) {
	wp_schedule_event(time(), 'weekly', 'commitUsage_event');
    }
	update_option ('mbc_usage', 1);
}

function deactivate_commitUsage() {
	wp_clear_scheduled_hook('commitUsage_event');
	update_option ('mbc_usage', 0);
}

function mbc_commitUsage(){
	global $wpdb;
	$er = $wpdb->get_row('SELECT count(id) as roomTypeCount, SUM(maxUnits) as roomCount FROM '.$wpdb->prefix.'mbc_calendars', ARRAY_A);
	$roomCount = $er['roomCount'];
    $roomTypeCount = $er['roomTypeCount'];
    $er = $wpdb->get_row('SELECT count(id) as bookings FROM '.$wpdb->prefix.'mbc_bookings WHERE state =\'open\'', ARRAY_A);
	$openBookings = $er['bookings'];
    $er = $wpdb->get_row('SELECT count(id) as bookings FROM '.$wpdb->prefix.'mbc_bookings WHERE state =\'confirmed\'', ARRAY_A);
	$confBookings = $er['bookings'];
    $er = $wpdb->get_row('SELECT count(id) as bookings FROM '.$wpdb->prefix.'mbc_bookings WHERE state =\'error\'', ARRAY_A);
	$errorBookings = $er['bookings'];
    $er = $wpdb->get_row('SELECT count(id) as requestCount FROM '.$wpdb->prefix.'mbc_requests', ARRAY_A);
	$requestCount = $er['requestCount'];
	global $wp_version;
	$data = array(	
	    'serverAdr' => $_SERVER['SERVER_NAME'],
	    'siteUrl' => get_option('siteurl'),
	   	'wpVersion' => $wp_version,
	    'phpVersion' => phpversion(),
	    'roomCount' => $roomCount,
	    'roomTypeCount' => $roomTypeCount,
	    'openBookings' => $openBookings,
	    'confBookings' => $confBookings,
	    'errorBookings' => $errorBookings,
	    'requestCount' => $requestCount,
	    'abcVersion' => get_option('mbc_pluginversion'),
	    'currencySetting' => get_option('mbc_currency'),
	    'cookieSetting' => get_option ('mbc_cookies'),
	    'analyticsSetting' => get_option ('mbc_googleanalytics'),
	    'poweredBySetting' => get_option('mbc_poweredby'),
		'languageCode' => get_locale(),
		'payment' => get_option('mbc_paymentString')
		);
	$url = 'https://booking-calendar-plugin.com/mbc-usage/';
    $data_string = '';
    foreach($data as $key=>$value) {
        $data_string .= $key.'='.$value.'&';
    }
    rtrim($data_string, '&');
    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_POST, count($data));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $data_string);

    $result = curl_exec($ch);
    curl_close($ch);
}
add_action('commitUsage_event', 'mbc_commitUsage');

add_filter( 'cron_schedules', 'mbc_add_weekly_schedule' );
function mbc_add_weekly_schedule( $schedules ) {
  $schedules['weekly'] = array(
    'interval' => 7 * 24 * 60 * 60,
    'display' => __( 'Once Weekly', 'multilang-booking-calendar' )
  );

  return $schedules;
}

function getAbcColor($colorInt){
    $colorCode = '';
    if(is_int($colorInt)){
        $colorNumber = $colorInt%12;
        switch($colorNumber){
            case 0:
                $colorCode = '#a6c6ce';
                break;
            case 1:
                $colorCode = '#ff6d2a';
                break;
            case 2:
                $colorCode = '#00c2e6';
                break;
            case 3:
                $colorCode = '#f9cb01';
                break;
            case 4:
                $colorCode = '#217084';
                break;
            case 5:
                $colorCode = '#ff9969';
                break;
            case 6:
                $colorCode = '#4cd4ed';
                break;
            case 7:
                $colorCode = '#fbda4d';
                break;
            case 8:
                $colorCode = '#639ba9';
                break;
            case 9:
                $colorCode = '#ffc5aa';
                break;
            case 10:
                $colorCode = '#99e7f5';
                break;
            case 11:
                $colorCode = '#fdea99';
                break;
        }
    }
	return $colorCode;
}

function abcEnqueueCustomCss(){
	$customCss = get_option ('mbc_customCss');
	$output = '';
	if(strlen($customCss) > 0){
		$output = '<style>'.$customCss.'</style>';
	}
	return $output;
}
?>