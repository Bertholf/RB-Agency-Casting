<?php
// *************************************************************************************************** //
// Prepare Page

	session_start();

	header("Cache-control: private"); //IE 6 Fix

	global $wpdb;
	global $current_user, $wp_roles;
	get_currentuserinfo();

	// include casting class
	include(dirname(dirname(__FILE__)) ."/app/casting.class.php");	

	// Get Settings
	$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_profilenaming  = (int)$rb_agency_options_arr['rb_agency_option_profilenaming'];
	$rb_agencyinteract_option_registerconfirm = (int)$rb_agency_interact_options_arr['rb_agencyinteract_option_registerconfirm'];

	/* Check if users can register. */
	$registration = get_option( 'users_can_register' );	

	function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}

	/* If user registered, input info. */
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'adduser' ) {
		
		$user_login = $_POST['casting_user_name'];
		$first_name = $_POST['casting_first_name'];
		$last_name  = $_POST['casting_last_name'];
		$user_email = $_POST['casting_email'];
		$CastingGender = $_POST['CastingGender'];
		$user_pass  = NULL;

		if ($rb_agencyinteract_option_registerconfirm == 1) {
			$user_pass = $_POST['casting_password'];
		} else {
			$user_pass = wp_generate_password();
		}

		$userdata = array(
			'user_pass' => $user_pass ,
			'user_login' => esc_attr( $user_login ),
			'first_name' => esc_attr( $first_name ),
			'last_name' => esc_attr( $last_name ),
			'user_email' => esc_attr( $user_email ),
			'role' => get_option( 'default_role' )
		);

		// Error checking
		$error = "";
		$have_error = false;
		
		if (!$userdata['user_login']) {
			$error .= __("A username is required for registration.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		if ( username_exists($userdata['user_login'])) {
			$error .= __("Sorry, that username already exists!<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		if ( !is_email($userdata['user_email'], true)) {
			$error .= __("You must enter a valid email address.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		if ( email_exists($userdata['user_email'])) {
			$error .= __("Sorry, that email address is already used!<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		
		if ( empty($_POST['casting_company'])) {
			$error .= __("Company is required.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['casting_website'])) {
			$error .= __("website is required.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['casting_address'])) {
			$error .= __("Address is required.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['casting_city'])) {
			$error .= __("City is required.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['CastingState'])) {
			$error .= __("State is required.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['casting_zip'])) {
			$error .= __("Zip is required.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['CastingCountry'])) {
			$error .= __("Country is required.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}

		if ( $_POST['casting_agree'] <> "yes") {
			$error .= __("You must agree to the terms and conditions to register.<br />", rb_agency_interact_TEXTDOMAIN);
			$have_error = true;
		}

		// Bug Free!
		if($have_error == false){

			$new_user = wp_insert_user( $userdata );

			$CastingIsActive		= 3;

			//create folder
			$CastingGallery 		= "casting-agent-" . $new_user;
			if (!is_dir(rb_agency_UPLOADPATH . $CastingGallery)) {
				mkdir(rb_agency_casting_UPLOADPATH . $CastingGallery, 0755);
				chmod(rb_agency_casting_UPLOADPATH . $CastingGallery, 0777);
			}

			if ($rb_agency_option_profilenaming == 0) { 
				$ProfileContactDisplay = $ProfileContactNameFirst . " ". $ProfileContactNameLast;
			} elseif ($rb_agency_option_profilenaming == 1) { 
				$ProfileContactDisplay = $ProfileContactNameFirst . " ". substr($ProfileContactNameLast, 0, 1);
			} elseif ($rb_agency_option_profilenaming == 2) { 
				$error .= "<b><i>". __(LabelSingular ." must have a display name identified", rb_agency_interact_TEXTDOMAIN) . ".</i></b><br>";
				$have_error = true;
			} elseif ($rb_agency_option_profilenaming == 3) { // by firstname
				$ProfileContactDisplay = "ID ". $ProfileID;
			} elseif ($rb_agency_option_profilenaming == 4) {
							$ProfileContactDisplay = $ProfileContactNameFirst;
			}
			
			// Create Record
			$insert = "INSERT INTO " . table_agency_casting .
						" (CastingUserLinked,
						   CastingGallery,
						   CastingContactDisplay,
						   CastingContactNameFirst,
						   CastingContactNameLast,
						   CastingContactEmail,
						   CastingContactCompany,
						   CastingContactWebsite,
						   CastingLocationStreet,
						   CastingLocationCity,
						   CastingLocationState,
						   CastingLocationZip,
						   CastingLocationCountry,
						   CastingDateCreated,
						   CastingIsActive)" .
						"VALUES (". $new_user . 
						 ",'" . $wpdb->escape($CastingGallery) . "','" . 
								$wpdb->escape($CastingContactDisplay) . 
						"','" . $wpdb->escape($first_name) . "','" . 
								$wpdb->escape($last_name) . 
						"','" . $wpdb->escape($user_email) . "','" . 
								$wpdb->escape($_POST['casting_company']) . "','" . 
								$wpdb->escape($_POST['casting_website']) . "','" . 
								$wpdb->escape($_POST['casting_street']) . "','" . 
								$wpdb->escape($_POST['casting_city']) . "','" . 
								$wpdb->escape($_POST['CastingState']) . "','" . 
								$wpdb->escape($_POST['casting_zip']) . "','" . 
								$wpdb->escape($_POST['CastingCountry']) . "'" . 
								",now(), ". 
								$CastingIsActive .")";

				$results = $wpdb->query($insert) or die(mysql_error());
				$CastingID = $wpdb->insert_id;

				// Log them in if no confirmation required.			
				if ($rb_agencyinteract_option_registerconfirm == 1) {

					global $error;
					
					$login = wp_login( $user_login, $user_pass );
					$login = wp_signon( array( 'user_login' => $user_login, 'user_password' => $user_pass, 'remember' => 1 ), false );	

				}

				// Notify admin and user
				RBAgency_Casting::rb_casting_send_notification($new_user, $user_pass);
		}
		
		// Log them in if no confirmation required.
		if ($rb_agencyinteract_option_registerconfirm == 1) {
			if($login){
				header("Location: ". get_bloginfo("wpurl"). "/casting-member/");
			}
		}

	
	}


// *************************************************************************************************** //
// Prepare Page
	// add scripts
	wp_deregister_script('jquery'); 
	wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
	wp_enqueue_script('jquery_latest');
	wp_enqueue_script( 'casting',  rb_agency_casting_BASEDIR . 'js/casting.js');
	
	echo $rb_header = RBAgency_Common::rb_header();
	
	echo "<div id=\"primary\" class=\"".$column_class." column rb-agency-interact rb-agency-interact-register\">\n";
	echo "  <div id=\"content\">\n";

   
		// ****************************************************************************************** //
		// Already logged in 
			
		if ( is_user_logged_in() && !current_user_can( 'create_users' ) ) {

	echo "    <p class=\"log-in-out alert\">\n";
	echo "		". __("You are currently logged in as .", rb_agency_interact_TEXTDOMAIN) ." <a href=\"/casting-member/\" title=\"". $login->display_name ."\">". $login->display_name ."</a>\n";
				//printf( __("You are logged in as <a href="%1$s" title="%2$s">%2$s</a>.  You don\'t need another account.', rb_agency_interact_TEXTDOMAIN), get_author_posts_url( $curauth->ID ), $user_identity );
	echo "		 <a href=\"". wp_logout_url( get_permalink() ) ."\" title=\"". __("Log out of this account", rb_agency_interact_TEXTDOMAIN) ."\">". __("Log out", rb_agency_interact_TEXTDOMAIN) ." &raquo;</a>\n";
	echo "    </p><!-- .alert -->\n";


		} elseif ( $new_user ) {

	echo "    <p class=\"alert\">\n";
				if ( current_user_can( 'create_users' ) )
					printf( __("A user account for %1$s has been created.", rb_agency_interact_TEXTDOMAIN), $_POST['user-name'] );
				else 
					printf( __("Thank you for registering, %1$s.", rb_agency_interact_TEXTDOMAIN), $_POST['user-name'] );
					echo "<br/>";
					printf( __("Please check your email address. That's where you'll receive your login password.<br/> (It might go into your spam folder)", rb_agency_interact_TEXTDOMAIN) );
	echo "    </p><!-- .alert -->\n";

		} else {

			if ( $error ) {
				echo "<p class=\"error\">". $error ."</p>\n";
			}

			// Show some admin loving.... (Admins can create)
			if ( current_user_can("create_users") && $registration ) {
	echo "    <p class=\"alert\">\n";
	echo "      ". __("Users can register themselves or you can manually create users here.", rb_agency_interact_TEXTDOMAIN);
	echo "    </p><!-- .alert -->\n";
			} elseif ( current_user_can("create_users")) {
	echo "    <p class=\"alert\">\n";
	echo "      ". __("Users cannot currently register themselves, but you can manually create users here.", rb_agency_interact_TEXTDOMAIN);
	echo "    </p><!-- .alert -->\n";
			}

			// Self Registration
			if ( $registration || current_user_can("create_users") ) {
	echo "  <header class=\"entry-header\">";
	echo "  	<h1 class=\"entry-title\">Join Our Team</h1>";
	echo "  </header>";
	echo "  <div id=\"client-register\" class=\"rbform\">";
	echo "	  <p class=\"rbform-description\">To Join Our Team please complete the application below.</p>";
	echo "    <form method=\"post\" action=\"". get_bloginfo('wpurl') ."/casting-register/\">\n";
	echo "       <div id=\"casting-username\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"casting_user_name\">". __("Username (required)", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_user_name\" type=\"text\" id=\"casting_user_name\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_user_name'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #rofile-username -->\n";

	if ($rb_agencyinteract_option_registerconfirm == 1) {
	echo "       <div id=\"casting-password\" class=\"rbfield rbpassword rbsingle\">\n";
	echo "       	<label for=\"casting_password\">". __("Password (required)", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_password\" type=\"password\" id=\"casting_password\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_password'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-password -->\n";
	}

	echo "       <div id=\"casting-first-name\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"casting_first_name\">". __("First Name", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_first_name\" type=\"text\" id=\"casting_first_name\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_first_name'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-first-name -->\n";

	echo "       <div id=\"casting-last-name\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"casting_last_name\">". __("Last Name", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_last_name\" type=\"text\" id=\"casting_last_name\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_last_name'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting_last_name -->\n";

	echo "       <div id=\"casting-email\" class=\"rbfield rbemail rbsingle\">\n";
	echo "       	<label for=\"email\">". __("E-mail (required)", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_email\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_email'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-email -->\n";

	echo "       <div id=\"casting-company\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"company\">". __("Company", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_company\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_company'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-company -->\n";

	echo "       <div id=\"casting-website\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"website\">". __("Website", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_website\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_website'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-website -->\n";
	
	echo "       <div id=\"casting-street-address\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"street-address\">". __("Street Address", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_address\" type=\"text\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_address'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-street-address -->\n";

	echo "       <div id=\"casting-city\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"city\">". __("City", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_city\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_city'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-city -->\n";	

	echo "       <div id=\"casting-country\" class=\"rbfield rbtext rbsingle\">\n";
				echo "		<label>". __("Country", rbagency_TEXTDOMAIN) ."</label>\n";
				echo "		<div>\n";

				$query_get ="SELECT * FROM `".table_agency_data_country."` ORDER BY CountryTitle ASC" ;
				$result_query_get = $wpdb->get_results($query_get);
				$location=site_url().'/club/';
				echo '<select name="CastingCountry" id="CastingCountry"  onchange="javascript:populateStates();">';
				echo '<option value="">'. __("Select country", _TEXTDOMAIN) .'</option>';
					foreach($result_query_get as $r){
						echo '<option value='.$r->CountryID.' '.selected($_POST['CastingCountry'],$r->CountryID,false).' >'.$r->CountryTitle.'</option>';
					}
				echo '</select>';
	echo "       </div></div><!-- #casting-country -->\n";

	echo "       <div id=\"casting-state\" class=\"rbfield rbselect rbsingle\">\n";
				echo "		<label>". __("State", rbagency_TEXTDOMAIN) ."</label>\n";
				echo "		<div>\n";

				if(isset($_POST['CastingCountry']) && !empty($_POST['CastingCountry'])){
						$query_get ="SELECT * FROM ".table_agency_data_state." WHERE CountryID = " . $_POST['CastingCountry'] ;
				} else {
						$query_get ="SELECT * FROM `".table_agency_data_state."`" ;
				}
				$result_query_get = $wpdb->get_results($query_get);
				echo '<select name="CastingState" id="CastingState">';
				echo '<option value="">'. __("Select state", rbagency_TEXTDOMAIN) .'</option>';
					foreach($result_query_get as $r){

						echo '<option value='.$r->StateID.' '.selected($_POST['CastingState'],$r->StateID,false).' >'.$r->StateTitle.'</option>';
					}
				echo '</select>';

	echo "       </div></div><!-- #casting-state -->\n";

	echo "       <div id=\"casting-zip\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"zip\">". __("Zip", rb_agency_interact_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_zip\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo wp_specialchars( $_POST['casting_zip'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-zip -->\n";

	echo "		<input type='hidden' value='".admin_url('admin-ajax.php')."' id='url'>";

	echo "       <div id=\"casting-argee\" class=\"rbfield rbcheckbox rbsingle\">\n";
					$casting_agree = get_the_author_meta("casting_agree", $current_user->ID );
	echo "       	<label></label>\n";
	echo "       	<div><input type=\"checkbox\" name=\"casting_agree\" value=\"yes\" /> ". sprintf(__("I agree to the %s terms of service", rb_agency_interact_TEXTDOMAIN), "<a href=\"/terms-of-use/\" target=\"_blank\">") ."</a></div>\n";
	echo "       </div><!-- #casting-agree -->\n";
 
	echo "       <div id=\"casting-submit\" class=\"rbfield rbsubmit rbsingle\">\n";
	echo "       	<input name=\"adduser\" type=\"submit\" id=\"addusersub\" class=\"submit button\" value='Register'/>";

					// if ( current_user_can("create_users") ) {  _e("Add User", rb_agency_interact_TEXTDOMAIN); } else {  _e("Register", rb_agency_interact_TEXTDOMAIN); } echo "\" />\n";

					wp_nonce_field("add-user");

	echo "       	<input name=\"action\" type=\"hidden\" id=\"action\" value=\"adduser\" />\n";
	echo "       </div><!-- #casting-submit -->\n";

	echo "   </form>\n";
	echo "   </div><!-- .rbform -->\n";

			}

}

if(!$registration){ echo "<p class='alert'>The administrator currently disabled the registration.<p>"; }

echo "  </div><!-- #content -->\n";
echo "</div><!-- #container -->\n";

// Get Sidebar 
	$LayoutType = "";
	if ($rb_agencyinteract_option_castingmanage_sidebar) {
		$LayoutType = "casting";
		get_sidebar(); 
	}
	
// Get Footer
echo $rb_footer = RBAgency_Common::rb_footer();
?>
