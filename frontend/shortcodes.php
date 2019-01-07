<?php

include('bookingform.php');
add_shortcode( 'mbc-bookingform', 'mbc_booking_showBookingForm'); // Shortcode for the booking form
add_shortcode( 'mbc-bookingwidget', 'mbc_booking_showBookingWidget'); // Shortcode for the booking widget

include('calendaroverview.php');
add_shortcode( 'mbc-overview', 'mbc_booking_showCalOverview'); // Shortcode for the calendar overview

include('singlecalendar.php');
add_shortcode( 'mbc-single', 'mbc_booking_showSingleCalendar'); // Shortcode for a single calendar
?>