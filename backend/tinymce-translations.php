<?php
 
if ( ! defined( 'ABSPATH' ) )
    exit;
 
if ( ! class_exists( '_WP_Editors' ) )
    require( ABSPATH . WPINC . '/class-wp-editor.php' );
 
function mbc_booking_tinymce_translation() {
    $strings = array(
        'bookingform' => __('Booking Form', 'multilang-booking-calendar'),
        'hideother' => __('Hide other Rooms when coming from a Single Calendar link.', 'multilang-booking-calendar-pro'),
        'hidetooshort' => __('Hide Rooms when minimum number of nights is too short.', 'multilang-booking-calendar-pro'),
        'calendaroverview' => __('Calendar Overview', 'multilang-booking-calendar'),
        'addsingle' => __('Add Single Calendar', 'multilang-booking-calendar'),
        'calendar' => __('Calendar', 'multilang-booking-calendar'),
        'legend' => __('Show legend', 'multilang-booking-calendar'),
        'singlecalendar' => __('Single Calendar', 'multilang-booking-calendar')
    );
 
    $locale = _WP_Editors::$mce_locale;
    $translated = 'tinyMCE.addI18n("' . $locale . '.multilang-booking-calendar", ' . json_encode( $strings ) . ");\n";
 
    return $translated;
}
 
$strings = mbc_booking_tinymce_translation();
?>