<?php
/*
Plugin Name: Multilang Booking Calendar
Plugin URI: https://booking-calendar-plugin.com/
Description: The Booking System that makes managing your online reservations easy. A great Booking Calendar plugin for Accommodations.
Author: Multilang Booking Calendar
Author URI: https://booking-calendar-plugin.com
Version: 1.4.9
Text Domain: multilang-booking-calendar
Domain Path: /languages/
*/

include('functions.php');
include('widget.php');
include('backend/bookings.php');
include('backend/seasons-calendars.php');
include('frontend/shortcodes.php');
include('backend/analytics.php');
include('backend/settings.php');
include('backend/extras.php');
include('backend/coupons.php');
include('backend/tinymce.php');


global $abcUrl;
$abcUrl = plugin_dir_url(__FILE__);

// Loading translations
add_action( 'plugins_loaded', 'mbc_booking_load_textdomain' );
function mbc_booking_load_textdomain() {
	load_plugin_textdomain('multilang-booking-calendar', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
}

//Install
function multilang_booking_calendar_install() {
	global $wpdb;
	$bookingTable = $wpdb->prefix . "mbc_bookings";

	if(get_option('mbc_pluginversion') === false && $wpdb->get_var("show tables like '$bookingTable'") != $bookingTable)
	{
	$bookings = "CREATE TABLE `".$wpdb->prefix."mbc_bookings` (
                 `id` int(255) NOT NULL AUTO_INCREMENT,
                 `start` date NOT NULL,
                 `end` date NOT NULL,
                 `calendar_id` int(255) NOT NULL,
                 `persons` int(32) NOT NULL,
                 `first_name` varchar(255) NOT NULL,
                 `last_name` varchar(255) NOT NULL,
                 `email` varchar(255) NOT NULL,
                 `phone` varchar(255) NOT NULL,
                 `address` varchar(255) NOT NULL,
                 `zip` varchar(255) NOT NULL,
                 `city` varchar(255) NOT NULL,
                 `county` varchar(255) NOT NULL,
                 `country` varchar(255) NOT NULL,
                 `message` text NOT NULL,
                 `price` float NOT NULL,
                 `state` varchar(32) NOT NULL,
                 `room_id` int(11) NOT NULL,
                 `payment` varchar(255) NOT NULL,
                 `payment_reference` varchar(255) NOT NULL,
                 `created` date NOT NULL,
                 PRIMARY KEY (`id`)
                ) CHARSET=utf8";

	$requests = "CREATE TABLE `".$wpdb->prefix."mbc_requests` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `current_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
         `date_from` date NOT NULL,
         `date_to` date NOT NULL,
         `persons` int(10) NOT NULL,
         `successful` tinyint(1) NOT NULL,
         PRIMARY KEY (`id`)
        ) CHARSET=utf8";

	$rooms = "CREATE TABLE `".$wpdb->prefix."mbc_rooms` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `calendar_id` int(11) NOT NULL,
         `name` varchar(255) NOT NULL,
         UNIQUE KEY `id_2` (`id`),
         KEY `id` (`id`)
        ) CHARSET=utf8";

	$calendars = "CREATE TABLE `".$wpdb->prefix."mbc_calendars` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `name` varchar(255) NOT NULL,
         `maxUnits` int(11) NOT NULL,
         `maxAvailabilities` int(11) NOT NULL,
         `pricePreset` double NOT NULL,
         `minimumStayPreset` int(16) NOT NULL,
         `partlyBooked` int(16) NOT NULL,
         `infoPage` int(11) NOT NULL,
         `infoText` text NOT NULL,
         PRIMARY KEY (`id`)
        ) CHARSET=utf8";

	$seasons = "CREATE TABLE `".$wpdb->prefix."mbc_seasons` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `title` varchar(255) NOT NULL,
         `price` double NOT NULL,
 		 `lastminute` int(11) NOT NULL,
         `minimumStay` int(16) NOT NULL,
         PRIMARY KEY (`id`)
        ) CHARSET=utf8";

	$seasonsAssignment = "CREATE TABLE `".$wpdb->prefix."mbc_seasons_assignment` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `calendar_id` int(11) NOT NULL,
         `season_id` int(11) NOT NULL,
         `start` date NOT NULL,
         `end` date NOT NULL,
         PRIMARY KEY (`id`)
        ) CHARSET=utf8";
	
	$extras ="CREATE TABLE `".$wpdb->prefix."mbc_extras` ( 
		`id` INT(16) NOT NULL AUTO_INCREMENT , 
		`name` TEXT NOT NULL , 
		`explanation` TEXT NOT NULL , 
		`calculation` TEXT NOT NULL ,
		`mandatory` TEXT NOT NULL , 
		`price` FLOAT(32) NOT NULL ,
		`persons` int(32) NOT NULL ,
		 PRIMARY KEY (`id`) ) charset=utf8";
		 
	$bookingExtras ="CREATE TABLE `".$wpdb->prefix."mbc_booking_extras` ( 
		`id` INT(32) NOT NULL AUTO_INCREMENT , 
		`booking_id` INT(32) NOT NULL , 
		`extra_id` INT(32) NOT NULL , 
		 PRIMARY KEY (`id`) ) charset=utf8";
       	 
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($extras);
	dbDelta($bookingExtras);
	dbDelta($bookings);
	dbDelta($requests);
	dbDelta($rooms);
	dbDelta($calendars);
	dbDelta($seasons);
	dbDelta($seasonsAssignment);
	}
	add_option('mbc_pluginversion', '149');
	add_option ('mbc_email', get_option( 'admin_email' ));
	add_option ('mbc_bookingpage', 0);
	add_option ('mbc_dateformat', "Y-m-d");
	add_option ('mbc_priceformat', ".");
	add_option ('mbc_cookies', 0);
	add_option ('mbc_googleanalytics', 0);
	$locale = localeconv();
	add_option ('mbc_currency', $locale['currency_symbol']);
	add_option ('mbc_newsletter_10th_asked', 0);
	add_option ('mbc_newsletter_100th_asked', 0);
	add_option ('mbc_newsletter_20000revenue_asked', 0);
	$mbc_text_details = __("Your details", "mbc-booking").":\n";
	$mbc_greeting = __("Hi [mbc_first_name]!", "mbc-booking")."\n";
	$mbc_goodbye = sprintf(__('Your %s-Team', 'multilang-booking-calendar'), get_option( 'blogname' ));
	$mbc_text_details .= __("Room type", "mbc-booking").": [mbc_calendar_name]\n";
	$mbc_text_details .= __("Room price", "mbc-booking").": [mbc_room_price]\n";
	$mbc_text_details .= __("Selected extras", "mbc-booking").": [mbc_optional_extras]\n";
	$mbc_text_details .= __("Additional costs", "mbc-booking").": [mbc_mandatory_extras]\n";
	$mbc_text_details .= __("Discount", "mbc-booking").": [mbc_discount]\n";
	$mbc_text_details .= __("Total price", "mbc-booking").": [mbc_total_price]\n";
	$mbc_text_details .= __("Checkin - Checkout", "mbc-booking").": [mbc_checkin_date] - [mbc_checkout_date]\n";
	$mbc_text_details .= __("Number of guests", "mbc-booking").": [mbc_person_count]\n";
	$mbc_text_details .= __("Full Name", "mbc-booking").": [mbc_first_name] - [mbc_last_name]\n";
	$mbc_text_details .= __("Email", "mbc-booking").": [mbc_email]\n";
	$mbc_text_details .= __("Phone", "mbc-booking").": [mbc_phone]\n";
	$mbc_text_details .= __("Address", "mbc-booking").": [mbc_address], [mbc_zip] [mbc_city], [mbc_county], [mbc_country] \n";
	$mbc_text_details .= __("Your message to us", "mbc-booking").": [mbc_message]\n\n";
	$mbc_subject_unconfirmed = sprintf(__('Your booking at %s', 'multilang-booking-calendar'),get_option( 'blogname' )).' - [mbc_checkin_date] - [mbc_checkout_date]';
	add_option ('mbc_subject_unconfirmed', $mbc_subject_unconfirmed);
	$mbc_text_unconfirmed =  $mbc_greeting;
	$mbc_text_unconfirmed .=  sprintf(__("Thank you for booking at %s. Your booking has not yet been confirmed. Please wait for an additional confirmation email.", "mbc-booking"), get_option( 'blogname' ))."\n\n";
	$mbc_text_unconfirmed .= $mbc_text_details;
	$mbc_text_unconfirmed .= $mbc_goodbye;
	add_option ('mbc_text_unconfirmed', $mbc_text_unconfirmed);
	$mbc_subject_confirmed = sprintf(__('Confirming your booking at %s', 'multilang-booking-calendar'),get_option( 'blogname' )).' - [mbc_checkin_date] - [mbc_checkout_date]';
	add_option ('mbc_subject_confirmed', $mbc_subject_confirmed);
	$mbc_text_confirmed = $mbc_greeting;
	$mbc_text_confirmed .= __("We are happy to confirm your booking!", "mbc-booking")."\n\n";
	$mbc_text_confirmed .= $mbc_text_details;
	$mbc_text_confirmed .= __("If you have any questions regard your stay, feel free to contact us.", "mbc-booking")."\n\n";
	$mbc_text_confirmed .= $mbc_goodbye;
	add_option ('mbc_text_confirmed', $mbc_text_confirmed);
	$mbc_subject_canceled = sprintf(__('Canceling your booking at %s', 'multilang-booking-calendar'),get_option( 'blogname' ));
	add_option ('mbc_subject_canceled', $mbc_subject_canceled);
	$mbc_text_canceled = $mbc_greeting;
	$mbc_text_canceled .= __("We are very sorry to cancel your booking! We already had another reservation for your requested travel period.", "mbc-booking")."\n";
	$mbc_text_canceled .= sprintf(__("Please check our website at %s for an alternative. We would be very happy to welcome you any time soon.", "mbc-booking"), get_site_url())."\n\n";
	$mbc_text_canceled .= $mbc_goodbye;
	add_option ('mbc_text_canceled', $mbc_text_canceled);
	$mbc_subject_rejected = sprintf(__('Rejecting your booking at %s', 'multilang-booking-calendar'),get_option( 'blogname' ));
	add_option ('mbc_subject_rejected', $mbc_subject_rejected);
	$mbc_text_rejected = $mbc_greeting;
	$mbc_text_rejected .= __("We are very sorry to reject your booking! We already had another reservation for your requested travel period.", "mbc-booking")."\n";
	$mbc_text_rejected .= sprintf(__("Please check our website at %s for an alternative. We would be very happy to welcome you any time soon.", "mbc-booking"), get_site_url())."\n\n";
	$mbc_text_rejected .= $mbc_goodbye;
	add_option ('mbc_text_rejected', $mbc_text_rejected);
	add_option('mbc_installdate', date('Y-m-d'));
	add_option('mbc_poweredby', 0);
	add_option('mbc_feedbackModal01', 0);
	add_option('mbc_currencyPosition', 1);
	add_option ('mbc_customCss', '');
	add_option('mbc_personcount', 2);
	add_option('mbc_bookingform', array(
		 		'firstname' => '2',
		 		'lastname' => '2',
		 		'phone' => '2',
		 		'street' => '2',
		 		'zip' => '2',
		 		'city' => '2',
		 		'county' => '0',
		 		'country' => '2',
		 		'message' => '1',
		 		'inputs' => '8'
				));
	add_option('mbc_bookingformvalidated', 0);
	add_option('mbc_deletion', 0);
	add_option('mbc_accessLevel','Administrator');
	add_option ('mbc_emailcopy', 0);
	if (! wp_next_scheduled ( 'clearWaitingBookings_event' )) {
		wp_schedule_event(time(), 'daily', 'clearWaitingBookings_event');
    }
    //Insert default values for textCustomization
    $textCustomization = array();
    $textCustomization[get_locale()] = array(
    						'checkAvailabilities' => '',
    						'selectRoom' => '',
    						'selectedRoom' => '',
    						'otherRooms' => '',
    						'noRoom' => '',
    						'availRooms' => '',
    						'roomType' => '',
    						'yourStay' => '',
    						'checkin' => '',
    						'checkout' => '',
    						'persons' => '',
    						'bookNow' => '',
    						'thankYou' => '',
    						'bookingSummary' => '',
    						'roomPrice' => '',
    						'editButton' => '',
    						'continueButton' => '',
							'fullyBooked' => '',
							'partlyAvailable' => '',
							'available' => '',
							'partlyBooked' => '',
    				);
    add_option( 'mbc_textCustomization',
    		serialize($textCustomization));
    
    //Insert default values for payment Settings Array
    add_option( 'mbc_paymentSettings',
    		serialize(
    				array(	'cash' => array('activate' => 'false',
    								'text' => ''),
    						'onInvoice' => array('activate' => 'false',
    								'text' => '')
    				)));
	
} //==>multilang_booking_calendar_install()
register_activation_hook( __FILE__, 'multilang_booking_calendar_install');


//Uninstall
function multilang_booking_calendar_uninstall() {
	deactivate_commitUsage();
	if(get_option('mbc_deletion') == 1){
		global $wpdb;
		wp_clear_scheduled_hook('clearWaitingBookings_event');
		$wpdb->query("DROP TABLE IF EXISTS 
			`".$wpdb->prefix."mbc_bookings`,
			`".$wpdb->prefix."mbc_booking_extras`,
			`".$wpdb->prefix."mbc_calendars`,
			`".$wpdb->prefix."mbc_extras`,
			`".$wpdb->prefix."mbc_requests`,
			`".$wpdb->prefix."mbc_rooms`,
			`".$wpdb->prefix."mbc_seasons`,
			`".$wpdb->prefix."mbc_seasons_assignment`
			");
		delete_option ('mbc_email');
		delete_option ('mbc_bookingpage');
		delete_option ('mbc_dateformat');
		delete_option ('mbc_priceformat');
		delete_option ('mbc_cookies');
		delete_option ('mbc_googleanalytics');
		delete_option ('mbc_currency');
		delete_option ('mbc_newsletter_10th_asked');
		delete_option ('mbc_newsletter_100th_asked');
		delete_option ('mbc_newsletter_20000revenue_asked');
		delete_option ('mbc_subject_unconfirmed');
		delete_option ('mbc_text_unconfirmed');
		delete_option ('mbc_subject_confirmed');
		delete_option ('mbc_text_confirmed');
		delete_option ('mbc_subject_canceled');
		delete_option ('mbc_text_canceled');
		delete_option ('mbc_subject_rejected');
		delete_option ('mbc_text_rejected');
		delete_option('mbc_pluginversion');
		delete_option('mbc_installdate');
		delete_option('mbc_poweredby');
		delete_option('mbc_feedbackModal01');
		delete_option('mbc_currencyPosition');
		delete_option('mbc_bookingform');
		delete_option('mbc_bookingformvalidated');
		delete_option('mbc_usage');
		delete_option('mbc_deletion');
		delete_option('mbc_newsletter');
		delete_option('mbc_paymentSettings');
		delete_option('mbc_paymentString');
		delete_option('mbc_personcount');
		delete_option('mbc_textCustomization');
		delete_option('mbc_accessLevel');
		delete_option('mbc_emailcopy');
	} else {
		$adminEmail = getAbcSetting('email');
   		$headers = 'From: '.get_option('blogname').' <'.$adminEmail.'>'."\r\n";
    	$subject = __('Plugin uninstalled', 'multilang-booking-calendar').' '.get_option('blogname');
		$body = __("Hi!", "multilang-booking-calendar").' <br/>';
		$body .= __("you just uninstalled the plugin Multilang Booking Calendar on your WordPress website, but for safety reasons the data in the database created by the plugin was not deleted.",  "multilang-booking-calendar").'<br/>';
		$body .= __("If this was a mistake, please install the plugin again, go the plugins settings, tick the box for plugin deletion and uninstall it again.",  "multilang-booking-calendar").'<br/><br/>';
		$body .= __("Kindly,",  "multilang-booking-calendar").'<br/>';
		$body .= 'Team of Multilang Booking Calendar <br/> https://booking-calendar-plugin.com';
		wp_mail($adminEmail, $subject, $body, $headers);
	}	

} //==>multilang_booking_calendar_uninstall()
register_uninstall_hook( __FILE__, 'multilang_booking_calendar_uninstall');

//Backend Actions:
function multilang_booking_calendar_admin_actions() {
	$capability = mbc_booking_admin_capabilities();
	//Backend Menu
	add_menu_page('Multilang Booking Calendar',
			'Multilang Booking Calendar',
			$capability, 
			'multilang_booking_calendar',
			'multilang_booking_calendar_show_bookings',
			'dashicons-calendar-alt',
			30
			);
			
	//Submenu "Bookings"
	add_submenu_page('multilang_booking_calendar',
			'Multilang Booking Calendar - '.__('Bookings', 'multilang-booking-calendar'),
			__('Bookings', 'multilang-booking-calendar'),
			$capability,
			'multilang_booking_calendar',
			'multilang_booking_calendar_show_bookings'
	);
	//Submenu "Seasons & Calendars"
	add_submenu_page('multilang_booking_calendar',
			'Multilang Booking Calendar - '.__('Seasons & Calendars', 'multilang-booking-calendar'),
			__('Seasons & Calendars', 'multilang-booking-calendar'),
			$capability,
			'multilang-booking-calendar-show-seasons-calendars',
			'multilang_booking_calendar_show_seasons_calendars'
	);
	//Submenu "Extras"
	add_submenu_page('multilang_booking_calendar',
			'Multilang Booking Calendar - '.__('Extras', 'multilang-booking-calendar'),
			__('Extras', 'multilang-booking-calendar'),
			$capability,
			'multilang-booking-calendar-show-extras',
			'multilang_booking_calendar_show_extras'
	);
	//Submenu "Analytics"
	add_submenu_page('multilang_booking_calendar',
			'Multilang Booking Calendar - '.__('Analytics', 'multilang-booking-calendar'),
			__('Analytics', 'multilang-booking-calendar'),
			$capability,
			'multilang-booking-calendar-show-analytics',
			'multilang_booking_calendar_show_analytics'
	);
	//Submenu "Settings"
	add_submenu_page('multilang_booking_calendar',
			'Multilang Booking Calendar - '.__('Settings', 'multilang-booking-calendar'),
			__('Settings', 'multilang-booking-calendar'),
			$capability,
			'multilang-booking-calendar-show-settings',
			'multilang_booking_calendar_show_settings'
	);

} //==>multilang_booking_calendar_admin_actions()
add_action('admin_menu', 'multilang_booking_calendar_admin_actions');

// Links on Plugin-Page
add_filter( 'plugin_row_meta', 'mbc_plugin_row_meta', 10, 2 );

function mbc_plugin_row_meta( $links, $file ) {

	if ( strpos( $file, 'multilang-booking-calendar.php' ) !== false ) {
		$new_links = array(
					'<a href="https://twitter.com/BookingCal" target="_blank">Twitter</a>',
					'<a href="https://booking-calendar-plugin.com/setup-guide" target="_blank">Setup Guide</a>'
				);
		
		$links = array_merge( $links, $new_links );
	}
	
	return $links;
}

// Update Check
function multilang_booking_update_check(){
	if ( intval(get_option( 'mbc_pluginversion' )) < '110' || intval(get_option( 'mbc_pluginversion' )) == 0) {
		update_option('mbc_pluginversion', '110');
		add_option('mbc_installdate', date('Y-m-d'));
		add_option('mbc_poweredby', 0);
		add_option('mbc_feedbackModal01', 0);
		add_option('mbc_currencyPosition', 1);
    }
    if(intval(get_option( 'mbc_pluginversion' )) < '117'){
		mbc_booking_setPersonCount();
	}
    if(intval(get_option( 'mbc_pluginversion' )) < '118'){
		update_option('mbc_pluginversion', '118');
		global $wpdb;
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."multilang_booking_calendar_calendars` ADD `minimumStayPreset` INT(16) NOT NULL AFTER `pricePreset`;");
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."multilang_booking_calendar_seasons` ADD `minimumStay` INT(16) NOT NULL AFTER `lastminute`;");
		$wpdb->query("UPDATE `".$wpdb->prefix."multilang_booking_calendar_calendars` SET `minimumStayPreset` = 1;");
		$wpdb->query("UPDATE `".$wpdb->prefix."multilang_booking_calendar_seasons` SET `minimumStay` = 1;");
	}
	if(intval(get_option( 'mbc_pluginversion' )) < '119'){
		update_option('mbc_pluginversion', '119');
		global $wpdb;
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."multilang_booking_calendar_calendars` ADD `partlyBooked` INT(16) NOT NULL AFTER `minimumStayPreset`;");
		$wpdb->query("UPDATE `".$wpdb->prefix."multilang_booking_calendar_calendars` SET `partlyBooked` = 1;");
	}
	
	if(intval(get_option( 'mbc_pluginversion' )) < '120'){
		update_option('mbc_pluginversion', '120');
		global $wpdb;
		$extras ="CREATE TABLE `".$wpdb->prefix."multilang_booking_calendar_extras` ( 
			`id` INT(16) NOT NULL AUTO_INCREMENT , 
			`name` TEXT NOT NULL , 
			`explanation` TEXT NOT NULL , 
			`calculation` TEXT NOT NULL ,
			`mandatory` TEXT NOT NULL , 
			`price` FLOAT(32) NOT NULL ,
			 PRIMARY KEY (`id`) ) charset=utf8";
		$bookingExtras ="CREATE TABLE `".$wpdb->prefix."multilang_booking_calendar_booking_extras` ( 
			`id` INT(32) NOT NULL AUTO_INCREMENT , 
			`booking_id` INT(32) NOT NULL , 
			`extra_id` INT(32) NOT NULL , 
			 PRIMARY KEY (`id`) ) charset=utf8";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($extras);
		dbDelta($bookingExtras);
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."multilang_booking_calendar_bookings` ADD `county` TEXT NOT NULL AFTER `city`;");
		update_option('mbc_bookingform', array(
		 		'firstname' => '2',
		 		'lastname' => '2',
		 		'phone' => '2',
		 		'street' => '2',
		 		'zip' => '2',
		 		'city' => '2',
		 		'county' => '0',
		 		'country' => '2',
		 		'message' => '1',
		 		'inputs' => '8'
				));
	}
	if(get_option( 'mbc_pluginversion' ) < '130'){
		update_option( 'mbc_pluginversion', '130');
		update_option ('mbc_usage', '0');
		update_option ('mbc_deletion', '0');
		if(getAbcSetting('bookingpage') > 0){
			$content_post = get_post(getAbcSetting('bookingpage'));
			if( strpos($content_post->post_content, 'mbc-bookingform') !== false) {
				update_option ('mbc_bookingformvalidated', 1);
			}else{
				update_option ('mbc_bookingformvalidated', 0);
			}			
		}
		global $wpdb;
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."multilang_booking_calendar_bookings` ADD `payment` TEXT NOT NULL AFTER `room_id`;");
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."multilang_booking_calendar_bookings` ADD `payment_reference` TEXT NOT NULL AFTER `payment`;");
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."multilang_booking_calendar_bookings` ADD `created` date NOT NULL AFTER `payment_reference`;");
	}
	if(get_option( 'mbc_pluginversion' ) < '137'){
		update_option( 'mbc_pluginversion', '137');
	    //Insert default values for textCustomization
	    add_option( 'mbc_textCustomization',
	    		serialize(
	    				array(
    						'checkAvailabilities' => '',
    						'selectRoom' => '',
    						'selectedRoom' => '',
    						'otherRooms' => '',
    						'noRoom' => '',
    						'availRooms' => '',
    						'roomType' => '',
    						'yourStay' => '',
    						'checkin' => '',
    						'checkout' => '',
    						'persons' => '',
    						'bookNow' => '',
    						'thankYou' => '',
							'bookingSummary' => '',
    						'roomPrice' => '',
    						'editButton' => '',
    						'continueButton' => '',
							'fullyBooked' => '',
							'partlyAvailable' => '',
							'available' => '',
							'partlyBooked' => '',
	    				)));
	    
	    //Insert default values for payment Settings Array
	    add_option( 'mbc_paymentSettings',
	    		serialize(
	    				array(	'cash' => array('activate' => 'false',
	    								'text' => ''),
	    						'onInvoice' => array('activate' => 'false',
	    								'text' => '')
	    				)));
	}
	if(get_option( 'mbc_pluginversion' ) < '138'){
		if(get_option( 'mbc_textCustomization') != false) {
			$textCustomization = array();
			$textCustomization[get_locale()] = unserialize(get_option( 'mbc_textCustomization') );
			update_option( 'mbc_textCustomization', serialize($textCustomization));
		}
		update_option( 'mbc_pluginversion', '138');
	}	
	if(get_option( 'mbc_pluginversion' ) < '140'){
		global $wpdb;
		$extraPersonsAlter = "ALTER TABLE `".$wpdb->prefix."multilang_booking_calendar_extras` ADD `persons` INT(32) NOT NULL AFTER `price`;";
		$wpdb->query($extraPersonsAlter);
		$wpdb->query("UPDATE `".$wpdb->prefix."multilang_booking_calendar_extras` SET `persons` = 1;");
		update_option( 'mbc_pluginversion', '140');
	}
	if(get_option( 'mbc_pluginversion' ) < '142'){
		add_option ('mbc_customCss', '');
		update_option ('mbc_unconfirmed', '0');
		update_option( 'mbc_pluginversion', '142');
	}	
	if(get_option( 'mbc_pluginversion' ) < '143'){
		if(getAbcSetting('installdate') <= date("Y-m-d", strtotime('-1 month'))){
			update_option('mbc_feedbackModal01', 0);
		}
		update_option( 'mbc_pluginversion', '143');
	}	
	if(get_option( 'mbc_pluginversion' ) < '144'){
		add_option('mbc_accessLevel','Administrator');
		update_option( 'mbc_pluginversion', '144');
	}
	if(get_option( 'mbc_pluginversion' ) < '147'){
		add_option ('mbc_emailcopy', 0);
		update_option( 'mbc_pluginversion', '147');
	}
	if(get_option( 'mbc_pluginversion' ) < '148'){
		global $wpdb;
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'multilang_booking_calendar_bookings` TO `'.$wpdb->prefix.'mbc_bookings`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'multilang_booking_calendar_requests` TO `'.$wpdb->prefix.'mbc_requests`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'multilang_booking_calendar_rooms` TO `'.$wpdb->prefix.'mbc_rooms`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'multilang_booking_calendar_calendars` TO `'.$wpdb->prefix.'mbc_calendars`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'multilang_booking_calendar_seasons` TO `'.$wpdb->prefix.'mbc_seasons`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'multilang_booking_calendar_seasons_assignment` TO `'.$wpdb->prefix.'mbc_seasons_assignment`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'multilang_booking_calendar_extras` TO `'.$wpdb->prefix.'mbc_extras`';
		$wpdb->query($rename_table);
		$rename_table = 'RENAME TABLE `'.$wpdb->prefix.'multilang_booking_calendar_booking_extras` TO `'.$wpdb->prefix.'mbc_booking_extras`';
		$wpdb->query($rename_table);
		update_option( 'mbc_pluginversion', '148');
	}
	if(get_option( 'mbc_pluginversion' ) < '149'){
		update_option( 'mbc_pluginversion', '149');
	}
}
add_action( 'plugins_loaded', 'multilang_booking_update_check' );
?>