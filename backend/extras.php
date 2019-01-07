<?php

// Add a Extra to DB
function mbc_booking_addExtra() {
	global $wpdb;

	if ( !current_user_can( mbc_booking_admin_capabilities() ) ) {
		wp_die("Go away");
	}
	mbc_booking_setPersonCount();
	$extraLimit = '&setting=extraAdded';
	if ( isset($_POST["name"]) && isset($_POST["mandatory"]) && isset($_POST["calculation"]) 
			&& isset($_POST["price"]) && intval($_POST["persons"]) > 0) {
			
		$er = $wpdb->get_row('SELECT COUNT(id) as extras FROM '.$wpdb->prefix.'mbc_extras', ARRAY_A);
		$extras = $er["extras"];
		if($extras < 2){
			$name = sanitize_text_field($_POST["name"]);
			$explanation = sanitize_text_field($_POST["explanation"]);
			$calculation = sanitize_text_field($_POST["calculation"]);
			$mandatory = sanitize_text_field($_POST["mandatory"]);
			$price = sanitize_text_field($_POST["price"]);
			$persons = intval($_POST["persons"]);
			$wpdb->insert( $wpdb->prefix.'mbc_extras', array(
				'name' 		  => $name,
				'explanation' => $explanation,
				'calculation' => $calculation,
				'mandatory'	  => $mandatory,
				'price'	 	  => $price,
				'persons'	  => $persons
			));
			if($extras == 0 && strtotime(getAbcSetting('installdate')) < strtotime('2016-07-10')){
				$extraLimit = '&setting=extraNew';
			}
		} else {
			$extraLimit = "&setting=extraLimit";
		}	
	}

	wp_redirect(  admin_url( "admin.php?page=multilang-booking-calendar-show-extras".$extraLimit ) );
	exit;
} //==>addExtra()
add_action( 'admin_post_mbc_booking_addExtra', 'mbc_booking_addExtra' );

// Edit season 
function mbc_booking_editExtra() {
	global $wpdb;

	if ( !current_user_can( mbc_booking_admin_capabilities() ) ) {
		wp_die("Go away");
	}

	if ( isset($_POST["id"]) && isset($_POST["name"]) && isset($_POST["mandatory"]) 
			&& isset($_POST["calculation"]) && isset($_POST["price"])
			&& intval($_POST["persons"]) > 0) {
		$name = sanitize_text_field($_POST["name"]);
		$explanation = sanitize_text_field($_POST["explanation"]);
		$calculation = sanitize_text_field($_POST["calculation"]);
		$mandatory = sanitize_text_field($_POST["mandatory"]);
		$price = sanitize_text_field($_POST["price"]);	
		$persons = intval($_POST["persons"]);
		$wpdb->update($wpdb->prefix.'mbc_extras', array(
				'name' 		  => $name,
				'explanation' => $explanation,
				'calculation' => $calculation,
				'mandatory'	  => $mandatory,
				'price'	 	  => $price,
				'persons'	  => $persons),
			array('id' => intval($_POST["id"])));
	}

	wp_redirect(  admin_url( "admin.php?page=multilang-booking-calendar-show-extras&setting=changeSaved" ) );
	exit;
} //==>editExtra()
add_action( 'admin_post_mbc_booking_editExtra', 'mbc_booking_editExtra' );

// Delete Extra
function mbc_booking_delExtra() {
	global $wpdb;

	if ( !current_user_can( mbc_booking_admin_capabilities() ) ) {
		wp_die("Go away");
	}
	if ( isset($_POST["id"]) ) {
		$wpdb->delete($wpdb->prefix.'mbc_extras', array('id' => intval($_POST["id"])));
		$wpdb->delete($wpdb->prefix.'mbc_booking_extras', array('extra_id' => intval($_POST["id"])));
		wp_redirect(  admin_url( "admin.php?page=multilang-booking-calendar-show-extras&setting=extraDeleted" ) );
	}
	exit;
} //==>delExtra()
add_action( 'admin_post_mbc_booking_delExtra', 'mbc_booking_delExtra' );

// Output to backend
function multilang_booking_calendar_show_extras() {
	
	if (!current_user_can(mbc_booking_admin_capabilities())) {
		wp_die("Go away");
	}
	
	global $wpdb;
	global $abcUrl;
	wp_enqueue_script('uikit-js', $abcUrl.'backend/js/uikit.min.js', array('jquery'));
	wp_enqueue_style('uikit', $abcUrl.'/frontend/css/uikit.gradient.min.css');
	$notices = '';	
	if(isset($_GET["setting"]) ){
		switch($_GET["setting"]){
			case 'extraLimit':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Extras are limited to 2 in the free version! If you need more extras, please download our <a href="https://booking-calendar-plugin.com/pro-download/" target="_blank">Pro-Version</a>.', 'multilang-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'multilang-booking-calendar').'</span></button></div>';
				break;
			case 'extraAdded':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Extra has been added.', 'multilang-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'multilang-booking-calendar').'</span></button></div>';
				break;
			case 'extraNew':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Extra has been added.', 'multilang-booking-calendar' ).'<br/>'
										.__('Make sure to add the placeholdes for extras in your email settings', 'multilang-booking-calendar' ).': 
										<a href="'.admin_url('admin.php?page=multilang-booking-calendar-show-settings').'">'.__('Email Settings', 'multilang-booking-calendar').'</a></strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'multilang-booking-calendar').'</span></button></div>';
				break;
			case 'changeSaved':
				$notices .= '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
										<p><strong>'.__('Change has been saved.', 'multilang-booking-calendar' ).'</strong></p><button type="button" class="notice-dismiss">
										<span class="screen-reader-text">'.__('Dismiss this notice.', 'multilang-booking-calendar').'</span></button></div>';
				break;
		}
	}
	$output = '<div class="wrap">
					<h1>'.__('Extras', 'multilang-booking-calendar').'</h1>'.$notices.'';
	if(isset($_GET["action"]) && $_GET["action"] == "mbc_booking_editExtra" && intval($_GET["id"]) > 0){
		
		// Does the ID exist?
		$row = $wpdb->get_row("SELECT COUNT(*) as co FROM ".$wpdb->prefix."mbc_extras WHERE id = '".intval($_GET["id"])."'", ARRAY_A);
		if($row["co"] == 0) {
			// ID doesn't exist
			wp_die("Error! Unknown id<br />Please go <a href='admin.php?page=multilang-booking-calendar-show-extras'>back</a>");
		} else {
			//ID exists
			$row = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."mbc_extras WHERE id = '".intval($_GET["id"])."'", ARRAY_A);
			$mandatoryYes = '';
			$mandatoryNo = '';
			switch($row[0]["mandatory"]){
				case 'yes':
					$mandatoryYes = 'checked';
					break;
				case 'no':
					$mandatoryNo = 'checked';
					break;
			}
			$calculationNight = '';
			$calculationDay = '';
			$calculationOnce = '';
			$calculationPerson = '';
			$calculationPersonNight = '';
			$calculationPersonDay = '';
			switch($row[0]["calculation"]){
				case 'night':
					$calculationNight = 'selected';
					break;
				case 'day':
					$calculationDay = 'selected';
					break;
				case 'once':
					$calculationOnce = 'selected';
					break;
				case 'person':
					$calculationPerson = 'selected';
					break;
				case 'personNight':
					$calculationPersonNight = 'selected';
					break;
				case 'personDay':
					$calculationPersonDay = 'selected';
					break;
			}
			$output .= '<h3>'.__('Edit an Extra', 'multilang-booking-calendar').'</h3>
					  <div class="wrap">
						<form method="post" action="admin-post.php">
						<input type="hidden" name="action" value="mbc_booking_editExtra" />
						<input type="hidden" name="id" value="'.intval($_GET["id"]).'" />
						<table class="form-table">
						  <tr>
							<td><label for="name">'.__('Name', 'multilang-booking-calendar').'</label></td>
							<td align="left"><input name="name" id="name" type="text" class="regular-text code" value="'.$row[0]["name"].'" required />
											 <p class="description">'.__('The name will be shown in the booking form.', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="explanation">'.__('Explanation', 'multilang-booking-calendar').'</br><em>'.__('(optional)', 'multilang-booking-calendar').'</em></label></td>
							<td align="left"><input value="'.$row[0]["explanation"].'" name="explanation" id="explanation" type="text" class="regular-text code" />
												 <p class="description">'.__('Explain what the extra is for.', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="calculation">'.__('Type of calculation', 'multilang-booking-calendar').'</label></td>
							<td align="left">
								<select name="calculation" id="calculation">
									<option value="night" '.$calculationNight.'>'.__('per night', 'multilang-booking-calendar').'</option>
									<option value="day" '.$calculationDay.'>'.__('per day', 'multilang-booking-calendar').'</option>
									<option value="once" '.$calculationOnce.'>'.__('once (no matter how many persons)', 'multilang-booking-calendar').'</option>
									<option value="person" '.$calculationPerson.'>'.__('per person (once)', 'multilang-booking-calendar').'</option>
									<option value="personNight" '.$calculationPersonNight.'>'.__('per person per night', 'multilang-booking-calendar').'</option>
									<option value="personDay" '.$calculationPersonDay.'>'.__('per person per day', 'multilang-booking-calendar').'</option>
								</select>
												 <p class="description">'.__('Define how the price of the extra is getting charged.', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td>'.__('Mandatory extra', 'multilang-booking-calendar').'</td>
							<td align="left">
								<input name="mandatory" id="mandatoryYes" type="radio" value="yes" '.$mandatoryYes.'> <label for="mandatoryYes">'.__('Yes', 'multilang-booking-calendar').'&nbsp;&nbsp;</label></input>
								<input name="mandatory" id="mandatoryNo" type="radio" value="no" '.$mandatoryNo.'> <label for="mandatoryNo">'.__('No', 'multilang-booking-calendar').'</label></input>
								<p class="description">'.__('You can make an extra mandatory, so every guest has to pay for it (eg. final cleaning).', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="price">'.__('Price', 'multilang-booking-calendar').'</label></td>
							<td align="left"><input value="'.$row[0]["price"].'" name="price" id="price" type="number" step="0.01" class="regular-text code" min="0.01" required />
												 <p class="description">'.__('Enter the price you want to charge for the extra.', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="price">'.__('Persons', 'multilang-booking-calendar').'</label></td>
							<td align="left"><input value="'.$row[0]["persons"].'" name="persons" id="persons" type="number" step="1" class="regular-text code" min="1" required />
												 <p class="description">'.__('Enter the number of persons in a booking request to activate this extra. For example, if you enter &quot;4&quot;, the extra will be shown for <b>4 or more</b> persons.', 'multilang-booking-calendar').'</p></td>
						  </tr>
												 		
						</table>
						<br />
						<input class="button button-primary" type="submit" value="'.__('Save', 'multilang-booking-calendar').'" />
						<a href="admin.php?page=multilang-booking-calendar-show-extras"><input class="button button-secondary" type="button" value="'._x('Cancel', 'a change', 'multilang-booking-calendar').'" /></a>
						</form>
					  </div>';
			}	  
	}else{
		$extras = '';
		$er = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'mbc_extras ORDER BY name', ARRAY_A);
		$foreachcount = 1;
		foreach($er as $row) {
			if ($foreachcount%2 == 1) {
				$class = 'class="alternate"';
			} else {
				$class = '';
			}
			$mandatory = '';
			switch($row["mandatory"]){
				case 'yes':
					$mandatory = __('Yes', 'multilang-booking-calendar');
					break;
				case 'no':
					$mandatory = __('No', 'multilang-booking-calendar');
					break;
			}
			$calculation = '';
			switch($row["calculation"]){
				case 'night':
					$calculation = __('per night', 'multilang-booking-calendar');
					break;
				case 'day':
					$calculation = __('per day', 'multilang-booking-calendar');
					break;
				case 'once':
					$calculation = __('once (no matter how many persons)', 'multilang-booking-calendar');
					break;
				case 'person':
					$calculation = __('per person (once)', 'multilang-booking-calendar');
					break;
				case 'personNight':
					$calculation = __('per person per night', 'multilang-booking-calendar');
					break;
				case 'personDay':
					$calculation = __('per person per day', 'multilang-booking-calendar');
					break;
			}		
			$extras .= '<tr '.$class.'>
							 <td>'.esc_html($row["name"]).'</td>
							 <td>'.esc_html($row["explanation"]).'</td>
							 <td>'.$calculation.'</td>
							 <td>'.$mandatory.'</td>
							 <td>'.mbc_booking_formatPrice(esc_html($row["price"])).'</td>
							 <td>'.intval($row["persons"]).'</td>
							 <td align="left"><form style="display: inline;" action="admin.php" method="get">
												<input type="hidden" name="page" value="multilang-booking-calendar-show-extras" />
												<input type="hidden" name="action" value="mbc_booking_editExtra" />
												<input type="hidden" name="id" value="'.$row["id"].'" />
												<input class="button button-primary" type="submit" value="'.__('Edit', 'multilang-booking-calendar').'" />
											  </form>
											  <form style="display: inline;" action="admin-post.php?action=mbc_booking_delExtra" method ="post">
												<input type="hidden" name="id" value="'.$row["id"].'" />
												<input class="button button-primary" type="submit" value="'.__('Delete', 'multilang-booking-calendar').'" onclick="return confirm(\''.__('Do you really want to delete this extra?', 'multilang-booking-calendar').'\')" />
											  </form></td>
						   </tr>';
			$foreachcount++;
		}
		$output .= ' <h3>'.__('Existing Extras', 'multilang-booking-calendar').'</h3>
					  <table class="wp-list-table widefat">
						<tr>
						  <td>'.__('Name', 'multilang-booking-calendar').'</td>
						  <td>'.__('Explanation', 'multilang-booking-calendar').'</td>
						  <td>'.__('Type of calculation', 'multilang-booking-calendar').'</td>
						  <td>'.__('Mandatory', 'multilang-booking-calendar').'</td>
						  <td>'.__('Price', 'multilang-booking-calendar').'</td>
						  <td>'.__('Persons', 'multilang-booking-calendar').'</td>
						  <td align="left"></td>
						</tr>
					  '.$extras.'
					  </table>
					  <hr/>
					  <h3>'.__('Add new Extra', 'multilang-booking-calendar').'</h3>
					  <div class="wrap">
						<form method="post" action="admin-post.php">
						<input type="hidden" name="action" value="mbc_booking_addExtra" />
						<table class="form-table">
						  <tr>
							<td><label for="name">'.__('Name', 'multilang-booking-calendar').'</label></td>
							<td align="left"><input name="name" placeholder="'.__('Wifi', 'multilang-booking-calendar').'" id="name" type="text" class="regular-text code" required />
											 <p class="description">'.__('The name will be shown in the booking form.', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="explanation">'.__('Explanation', 'multilang-booking-calendar').'</br><em>'.__('(optional)', 'multilang-booking-calendar').'</em></label></td>
							<td align="left"><input placeholder="'.__('Get Wifi-access in your room.', 'multilang-booking-calendar').'" name="explanation" id="explanation" type="text" class="regular-text code"/>
												 <p class="description">'.__('Explain what the extra is for.', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="calculation">'.__('Type of calculation', 'multilang-booking-calendar').'</label></td>
							<td align="left">
								<select name="calculation" id="calculation">
									<option value="night">'.__('per night', 'multilang-booking-calendar').'</option>
									<option value="day">'.__('per day', 'multilang-booking-calendar').'</option>
									<option value="once">'.__('once (no matter how many persons)', 'multilang-booking-calendar').'</option>
									<option value="person">'.__('per person (once)', 'multilang-booking-calendar').'</option>
									<option value="personNight">'.__('per person per night', 'multilang-booking-calendar').'</option>
									<option value="personDay">'.__('per person per day', 'multilang-booking-calendar').'</option>
								</select>
												 <p class="description">'.__('Define how the price of the extra is getting charged.', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td>'.__('Mandatory extra', 'multilang-booking-calendar').'</td>
							<td align="left">
								<input name="mandatory" id="mandatoryYes" type="radio" value="yes"> <label for="mandatoryYes">'.__('Yes', 'multilang-booking-calendar').'&nbsp;&nbsp;</label></input>
								<input name="mandatory" id="mandatoryNo" type="radio" value="no" checked> <label for="mandatoryNo">'.__('No', 'multilang-booking-calendar').'</label></input>
								<p class="description">'.__('You can make an extra mandatory, so every guest has to pay for it (eg. final cleaning).', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="price">'.__('Price', 'multilang-booking-calendar').'</label></td>
							<td align="left"><input placeholder="5,00" name="price" id="price" type="number" step="0.01" class="regular-text code" min="0.01" required />
												 <p class="description">'.__('Enter the price you want to charge for the extra.', 'multilang-booking-calendar').'</p></td>
						  </tr>
						  <tr>
							<td><label for="price">'.__('Persons', 'multilang-booking-calendar').'</label></td>
							<td align="left"><input value="1" name="persons" id="persons" type="number" step="1" class="regular-text code" min="1" required />
												 <p class="description">'.__('Enter the number of persons in a booking request to activate this extra. For example, if you enter &quot;4&quot;, the extra will be shown for <b>4 or more</b> persons.', 'multilang-booking-calendar').'</p></td>
						  </tr>
						</table>
						<br />
						<input class="button button-primary" type="submit" value="'.__('Add Extra', 'multilang-booking-calendar').'" />
						</form>
						<hr />
						<p>
							'.__('Do you want to promote your business using discount codes?', 'multilang-booking-calendar').' 
							'.__('Or do you want to limit an extra to a calendar?', 'multilang-booking-calendar').'<br/>
							'.__('Take a look at our <a target="_blank" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=DiscountCodes">Pro Version</a>!', 'multilang-booking-calendar').'<br/>
							'.__('Use discount code <b>BASICUPGRADE</b> to save 10€.', 'multilang-booking-calendar').'
						</p>
					  </div>';
	}				  
	$output .= '</div>';				
	echo $output;
	// id
	// name 
	// explanation
	// calculation: night, day, person, once, per night per person
	// price
}	
?>