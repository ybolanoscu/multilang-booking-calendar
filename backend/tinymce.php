<?php
add_action('admin_head', 'mbc_booking_add_tinymce_button');

function mbc_booking_add_tinymce_button() {
    global $typenow;
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
		return;
    }
    if( ! in_array( $typenow, array( 'post', 'page' ) ) ){
		return;
	}
	
    if ( get_user_option('rich_editing') == 'true') {
    	global $wpdb;
		$calendar = array();
    	$calendarRows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."mbc_calendars", ARRAY_A);
		foreach($calendarRows as $row) {
			$calendar[] = array('text' => $row["name"], 'value' => $row["id"]);
		}
		echo "<script type='text/javascript'>"; 
	    echo "var mbc_tinymce_calendars = ".json_encode($calendar).";";
		echo "</script>"; 
    }
}

add_filter('mce_buttons', 'mbc_booking_register_tinymce_button');
add_filter("mce_external_plugins", "mbc_booking_add_tinymce_plugin");

function mbc_booking_add_tinymce_plugin($plugin_array) {
    $plugin_array['mbc_booking_button'] = plugins_url( '/js/mbc-tinymce.js', __FILE__ ); // CHANGE THE BUTTON SCRIPT HERE
    return $plugin_array;
}

function mbc_booking_register_tinymce_button($buttons) {
   array_push($buttons, "mbc_booking_button");
   return $buttons;
}

function mbc_booking_tinymce_css() {
    wp_enqueue_style('mbc-booking-tinymce', plugins_url('css/tinymce.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'mbc_booking_tinymce_css');
	
function mbc_booking_tinymce_lang($locales) {
    $locales['multilang-booking-calendar'] = plugin_dir_path ( __FILE__ ) . 'tinymce-translations.php';
    return $locales;
}
 
add_filter( 'mce_external_languages', 'mbc_booking_tinymce_lang');
?>