<?php
// *************************************************************************************************** //
// Prepare Page

	if (!headers_sent()) {
		header("Cache-control: private"); //IE 6 Fix
	}
	global $wpdb;
	global $current_user, $wp_roles;
	get_currentuserinfo();

	// include casting class
	include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "agency_casting_types (
			CastingTypeID BIGINT(20) NOT NULL AUTO_INCREMENT,
			CastingTypeTitle VARCHAR(255) NOT NULL,
			CastingTypeSlug VARCHAR(255),
			PRIMARY KEY (CastingTypeID)
			);";
	dbDelta($sql);

	// Get Settings
	$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_profilenaming  = isset($rb_agency_options_arr['rb_agency_option_profilenaming'])?(int)$rb_agency_options_arr['rb_agency_option_profilenaming']:0;
	$rb_agency_interact_options_arr = get_option('rb_agencyinteract_options');
	$rb_agencyinteract_option_registerconfirm = isset($rb_agency_interact_options_arr['rb_agencyinteract_option_registerconfirm']) ?(int)$rb_agency_interact_options_arr['rb_agencyinteract_option_registerconfirm']:0;
	$rb_agency_option_casting_toc = isset($rb_agency_options_arr['rb_agency_option_agency_casting_toc'])?$rb_agency_options_arr['rb_agency_option_agency_casting_toc']:"/casting-terms-and-conditions";


	
	/* Check if users can register. */
	$registration = get_option( 'users_can_register' );

	if(!function_exists("base64_url_decode")){
		function base64_url_decode($input) {
			return base64_decode(strtr($input, '-_', '+/'));
		}
	}

	/* If user registered, input info. */
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'adduser' ) {

		$user_login = $_POST['casting_user_name'];
		$first_name = $_POST['casting_first_name'];
		$last_name  = $_POST['casting_last_name'];
		$user_email = $_POST['casting_email'];
		$CastingGender = isset($_POST['CastingGender'])?$_POST['CastingGender']:0;
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
			$error .= __("A username is required for registration.<br />",RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( username_exists($userdata['user_login'])) {
			$error .= __("Sorry, that username already exists!<br />",RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( !is_email($userdata['user_email'])) {
			$error .= __("You must enter a valid email address.<br />",RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( email_exists($userdata['user_email'])) {
			$error .= __("Sorry, that email address is already used!<br />",RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_POST['casting_company'])) {
			$error .= __("Company is required.<br />",RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['casting_website'])) {
			//$error .= __("website is required.<br />",RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}
		if ( empty($_POST['casting_address'])) {
			//$error .= __("Address is required.<br />",RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}
		if ( empty($_POST['casting_city'])) {
			//$error .= __("City is required.<br />",RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}
		if ( empty($_POST['CastingState'])) {
			//$error .= __("State is required.<br />",RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}
		if ( empty($_POST['casting_zip'])) {
			//$error .= __("Zip is required.<br />",RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}
		if ( empty($_POST['CastingCountry'])) {
			//$error .= __("Country is required.<br />",RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}

		if (!isset($_POST['casting_agree'])) {
			$error .= __("You must agree to the terms and conditions to register.<br />",RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		// Bug Free!
		if($have_error == false){

			$new_user = wp_insert_user( $userdata );

			$rb_agencyinteract_options_arr = get_option('rb_agencyinteract_options');


			/*

			manually approve(0) (pending for approval(3) or, active(1))
			automatically approve(1) (inactive(0) or archived(2))
			if( manually approve(0) == active good

			*/
			$_registerapproval = (int)$rb_agencyinteract_options_arr['rb_agencyinteract_casting_option_registerapproval'];//manually , automatic
			$_default_registered = (int)$rb_agencyinteract_options_arr['rb_agencyinteract_casting_option_default_registered_users']; // options

			//manually approve(0)
			if($_registerapproval == 0){
				$CastingIsActive = $_default_registered;
			}else{
				//automatic but do not allow the active as default..
				if($_default_registered != 1){
					$CastingIsActive = $_default_registered;
				}else{
					$CastingIsActive = 1; 
				}
			}
			

			//create folder
			$CastingGallery 		= "casting-agent-" . $new_user;
			if (!is_dir(RBAGENCY_casting_UPLOADPATH . $CastingGallery)) {
				mkdir(RBAGENCY_casting_UPLOADPATH . $CastingGallery, 0755);
				chmod(RBAGENCY_casting_UPLOADPATH . $CastingGallery, 0777);
			}
			if ($rb_agency_option_profilenaming == 0) {
				$CastingContactDisplay = $first_name . " ". $last_name;
			} elseif ($rb_agency_option_profilenaming == 1) {
				$CastingContactDisplay = $first_name . " ". substr($last_name, 0, 1);
			} elseif ($rb_agency_option_profilenaming == 2) {
				$error .= "<b><i>".LabelSingular. __(" must have a display name identified",RBAGENCY_casting_TEXTDOMAIN) . ".</i></b><br>";
				$have_error = true;
			} elseif ($rb_agency_option_profilenaming == 3) { // by firstname
				$CastingContactDisplay = "ID ". $ProfileID;
			} elseif ($rb_agency_option_profilenaming == 4) {
				$CastingContactDisplay = $first_name;
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
							CastingIsActive,CastingType)" .
						"VALUES (". $new_user .
						",'" . esc_sql($CastingGallery) . "','" .
								esc_sql($CastingContactDisplay) .
						"','" . esc_sql($first_name) . "','" .
								esc_sql($last_name) .
						"','" . esc_sql($user_email) . "','" .
								esc_sql($_POST['casting_company']) . "','" .
								esc_sql($_POST['casting_website']) . "','" .
								esc_sql($_POST['casting_address']) . "','" .
								esc_sql($_POST['casting_city']) . "','" .
								esc_sql($_POST['CastingState']) . "','" .
								esc_sql($_POST['casting_zip']) . "','" .
								esc_sql($_POST['CastingCountry']) . "'" .
								",now(), ".
								$CastingIsActive .",".$_POST["casting_type"].")";

				$results = $wpdb->query($insert);
				$CastingID = $wpdb->insert_id;

				// Log them in if no confirmation required.
				if ($rb_agencyinteract_option_registerconfirm == 1) {
					global $error;
					//$login = wp_login( $user_login, $user_pass );
					$login = wp_signon( array( 'user_login' => $user_login, 'user_password' => $user_pass, 'remember' => 1 ), false );
				}

				// Notify admin and user
				RBAgency_Casting::rb_casting_send_notification($new_user, $user_pass);
		}


		// Log them in if no confirmation required.
		if ($rb_agencyinteract_option_registerconfirm == 1) {

			/* echo 'xxxxxxxx'.$_default_registered;
			echo 'xxxxxxxx'.$_registerapproval ;
			exit; */
			if(isset($login)){
				//check the account status...
				if($_default_registered == 3){
					//pending for approval..
					header("Location: ". get_bloginfo("wpurl"). "/casting-pending/");
					
				}elseif($_default_registered == 1){
					if($_registerapproval == 1){
						//automatic
						//active
						header("Location: ". get_bloginfo("wpurl"). "/casting-dashboard/");
					}else{
						//pending for approval..
						header("Location: ". get_bloginfo("wpurl"). "/casting-pending/");
					}					
				}else{
					//inactive or archived
					header("Location: ". get_bloginfo("wpurl"). "/casting-inactive-archive/");
				}
			}
		}
	}


// *************************************************************************************************** //
// Prepare Page
	// add scripts
	//wp_deregister_script('jquery');
	//wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js');
	wp_enqueue_script('jquery');
	wp_enqueue_script( 'casting',  RBAGENCY_casting_BASEDIR . 'js/casting.js');

	echo $rb_header = RBAgency_Common::rb_header();

	echo "<div class=\"".(isset($column_class)?$column_class:0)." column rb-agency-interact rb-agency-interact-register\">\n";
	echo "  <div id=\"rbcontent\">\n";

		// ****************************************************************************************** //
		// Already logged in

		if ( is_user_logged_in() && !current_user_can( 'create_users' ) ) {

	echo "    <p class=\"log-in-out alert\">\n";
	echo "		". __("You are currently logged in as .",RBAGENCY_casting_TEXTDOMAIN) ." <a href=\"/casting-member/\" title=\"". $login->display_name ."\">". $login->display_name ."</a>\n";
				//printf( __("You are logged in as <a href="%1$s" title="%2$s">%2$s</a>.  You don\'t need another account.',RBAGENCY_casting_TEXTDOMAIN), get_author_posts_url( $curauth->ID ), $user_identity );
	echo "		<a href=\"". wp_logout_url( get_permalink() ) ."\" title=\"". __("Log out of this account",RBAGENCY_casting_TEXTDOMAIN) ."\">". __("Log out",RBAGENCY_casting_TEXTDOMAIN) ." &raquo;</a>\n";
	echo "    </p><!-- .alert -->\n";

		} elseif ( isset($new_user) ) {

	echo "    <p class=\"alert\">\n";
				if ( current_user_can( 'create_users' ) )
					printf( __("A user account for %1$s has been created.",RBAGENCY_casting_TEXTDOMAIN), $_POST['casting_user_name'] );
				else
					printf( __("Thank you for registering, %s.",RBAGENCY_casting_TEXTDOMAIN), $_POST['casting_user_name'] );
					echo "<br/>";
					printf( __("Please check your email address. That's where you'll receive your login password.<br/> (It might go into your spam folder)",RBAGENCY_casting_TEXTDOMAIN) );
	echo "    </p><!-- .alert -->\n";

		} else {

			if ( $error ) {
				echo "<p class=\"error\">". $error ."</p>\n";
			}

			// Show some admin loving.... (Admins can create)
			if ( current_user_can("create_users") && $registration ) {
	echo "    <p class=\"alert\">\n";
	echo "      ". __("Users can register themselves or you can manually create users here.",RBAGENCY_casting_TEXTDOMAIN);
	echo "    </p><!-- .alert -->\n";
			} elseif ( current_user_can("create_users")) {
	echo "    <p class=\"alert\">\n";
	echo "      ". __("Users cannot currently register themselves, but you can manually create users here.",RBAGENCY_casting_TEXTDOMAIN);
	echo "    </p><!-- .alert -->\n";
			}


			// Self Registration
			if ( $registration || current_user_can("create_users") ) {
	echo "  <header class=\"entry-header\">";
	echo "  	<h1 class=\"entry-title\">". __("Join Our Team",RBAGENCY_casting_TEXTDOMAIN) ."</h1>";
	echo "  </header>";
	echo "  <div id=\"client-register\" class=\"rbform\">";
	echo "		<p class=\"rbform-description\">". __("To Join Our Team please complete the application below.",RBAGENCY_casting_TEXTDOMAIN) ."</p>";
	if(!$shortcode_register){
		echo "    <form method=\"post\" action=\"". get_bloginfo('wpurl') ."/casting-register/\">\n";
	} else {
		echo "    <form method=\"post\" action=\"".get_page_link()."\">\n";
	}
	echo "       <div id=\"casting-username\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"casting_user_name\">". __("Username (required)",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_user_name\" type=\"text\" id=\"casting_user_name\" value=\""; if ( $error ) echo esc_html( $_POST['casting_user_name'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #rofile-username -->\n";

	if ($rb_agencyinteract_option_registerconfirm == 1) {
		echo "       <div id=\"casting-password\" class=\"rbfield rbpassword rbsingle\">\n";
		echo "   		<label for=\"casting_password\">". __("Password (required)",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "   		<div><input class=\"text-input\" name=\"casting_password\" type=\"password\" id=\"casting_password\" value=\""; if ( $error ) echo esc_html( $_POST['casting_password'], 1 ); echo "\" /></div>\n";
		echo "       </div><!-- #casting-password -->\n";
	}

	echo "       <div id=\"casting-first-name\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"casting_first_name\">". __("First Name",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_first_name\" type=\"text\" id=\"casting_first_name\" value=\""; if ( $error ) echo esc_html( $_POST['casting_first_name'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-first-name -->\n";

	echo "       <div id=\"casting-last-name\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"casting_last_name\">". __("Last Name",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_last_name\" type=\"text\" id=\"casting_last_name\" value=\""; if ( $error ) echo esc_html( $_POST['casting_last_name'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting_last_name -->\n";

	echo "       <div id=\"casting-email\" class=\"rbfield rbemail rbsingle\">\n";
	echo "   		<label for=\"email\">". __("E-mail (required)",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_email\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo esc_html( $_POST['casting_email'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-email -->\n";

	echo "       <div id=\"casting-company\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"company\">". __("Company (required)",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_company\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo esc_html( $_POST['casting_company'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-company -->\n";

	$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."agency_casting_types",ARRAY_A);
	echo "       <div id=\"casting-types\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"castingtype\">". __("Profile Type",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><select name=\"casting_type\">";
	echo "               <option>--</option>";
	foreach($results as $result){
				echo "<option value=\"".$result["CastingTypeID"]."\">".$result["CastingTypeTitle"]."</option>";
	}
	echo "</select></div>\n";
	echo "       </div><!-- #casting-types -->\n";


	echo "       <div id=\"casting-website\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"website\">". __("Website",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_website\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo esc_html( $_POST['casting_website'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-website -->\n";

	echo "       <div id=\"casting-street-address\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"street-address\">". __("Street Address",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_address\" type=\"text\" value=\""; if ( $error ) echo esc_html( $_POST['casting_address'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-street-address -->\n";

	echo "       <div id=\"casting-city\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"city\">". __("City",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_city\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo esc_html( $_POST['casting_city'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-city -->\n";

	echo "       <div id=\"casting-country\" class=\"rbfield rbtext rbsingle\">\n";
				echo "		<label>". __("Country",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
				echo "		<div>\n";

				$query_get ="SELECT * FROM `".table_agency_data_country."` ORDER BY CountryTitle ASC" ;
				$result_query_get = $wpdb->get_results($query_get);
				$location=site_url().'/club/';
				echo '<select name="CastingCountry" id="CastingCountry"  onchange="javascript:populateStatesCastingRegister(\'CastingCountry\',\'CastingState\');">';
				echo '<option value="">'. __("Select country", RBAGENCY_casting_TEXTDOMAIN) .'</option>';
					foreach($result_query_get as $r){
						echo '<option value='.$r->CountryID.' '.selected($_POST['CastingCountry'],$r->CountryID,false).' >'.$r->CountryTitle.'</option>';
					}
				echo '</select>';
	echo "       </div></div><!-- #casting-country -->\n";

	echo "       <div id=\"casting-state\" class=\"rbfield rbselect rbsingle\">\n";
				echo "		<label>". __("State", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
				echo "		<div>\n";

				$result_query_get = array();

				if(isset($_POST['CastingCountry']) && !empty($_POST['CastingCountry'])){
					$query_get ="SELECT * FROM ".table_agency_data_state." WHERE CountryID = " . $_POST['CastingCountry'] ;
					$result_query_get = $wpdb->get_results($query_get);
				}
				echo '<select name="CastingState" id="CastingState">';
				echo '<option value="">'. __("Select state", rbagency_TEXTDOMAIN) .'</option>';
					foreach($result_query_get as $r){
						echo '<option value='.$r->StateID.' '.selected($_POST['CastingState'],$r->StateID,false).' >'.$r->StateTitle.'</option>';
					}
				echo '</select>';

	echo "       </div></div><!-- #casting-state -->\n";

	echo "       <div id=\"casting-zip\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"zip\">". __("Zip",RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_zip\" type=\"text\" id=\"casting_email\" value=\""; if ( $error ) echo esc_html( $_POST['casting_zip'], 1 ); echo "\" /></div>\n";
	echo "       </div><!-- #casting-zip -->\n";

					//Custom fields
					rb_get_customfields_castingregister();
	echo "		<input type='hidden' value='".admin_url('admin-ajax.php')."' id='url'>";


	echo "       <div id=\"casting-argee\" class=\"rbfield rbcheckbox rbsingle\">\n";
					$casting_agree = get_the_author_meta("casting_agree", $current_user->ID );
	echo "   		<label></label>\n";
	echo "   		<div><input type=\"checkbox\" name=\"casting_agree\" value=\"yes\" /> ". sprintf(__("I agree to the %s terms of service",RBAGENCY_casting_TEXTDOMAIN), "<a href=\"".$rb_agency_option_casting_toc ."\" target=\"_blank\">") ."</a></div>\n";
	echo "       </div><!-- #casting-agree -->\n";

	echo "       <div id=\"casting-submit\" class=\"rbfield rbsubmit rbsingle\">\n";
	echo "   		<input name=\"adduser\" type=\"submit\" id=\"addusersub\" class=\"submit button\" value='".__('Register',RBAGENCY_casting_TEXTDOMAIN)."'/>";

					// if ( current_user_can("create_users") ) { _e("Add User",RBAGENCY_casting_TEXTDOMAIN); } else { _e("Register",RBAGENCY_casting_TEXTDOMAIN); }echo "\" />\n";

					wp_nonce_field("add-user");
					$fb_app_register_uri = "";

					if(isset($rb_agency_interact_option_fb_app_register_uri) && $rb_agency_interact_option_fb_app_register_uri == 1){
						$fb_app_register_uri = $rb_agency_interact_option_fb_app_register_uri;
					} else {
						$fb_app_register_uri = network_site_url("/")."casting-register/";
					}

					// Allow facebook login/registration
					if(isset($rb_agency_interact_option_fb_registerallow) && $rb_agency_interact_option_fb_registerallow ==1){
						echo "<div>\n";
						echo "<span>Or</span>\n";
						echo "<div id=\"fb_RegistrationForm\">\n";
						if ($rb_agencyinteract_option_registerconfirm == 1) { // With custom password fields
							echo "<iframe src=\"https://www.facebook.com/plugins/registration?client_id=".$rb_agency_interact_option_fb_app_id."&redirect_uri=".$fb_app_register_uri."&fields=[ {'name':'name'}, {'name':'email'}, {'name':'location'}, {'name':'gender'}, {'name':'birthday'}, {'name':'username',  'description':'Username',  'type':'text'},{'name':'password'},{'name':'tos','description':'I agree to the terms of service','type':'checkbox'}]\"
									scrolling=\"auto\"
									frameborder=\"no\"
									style=\"border:none\"
									allowTransparency=\"true\"
									width=\"100%\"
									height=\"330\">
							</iframe>";
						} else {
							echo "<iframe src=\"https://www.facebook.com/plugins/registration?client_id=".$rb_agency_interact_option_fb_app_id."&redirect_uri=".$fb_app_register_uri."&fields=[ {'name':'name'}, {'name':'email'}, {'name':'location'}, {'name':'gender'}, {'name':'birthday'}, {'name':'username',  'description':'Username',  'type':'text'},{'name':'password'},{'name':'tos','description':'I agree to the terms of service','type':'checkbox'}]\"
									scrolling=\"auto\"
									frameborder=\"no\"
									style=\"border:none\"
									allowTransparency=\"true\"
									width=\"100%\"
									height=\"330\">
							</iframe>";
						}

						echo "</div>\n";

					}

	echo "   		<input name=\"action\" type=\"hidden\" id=\"action\" value=\"".__("adduser",RBAGENCY_casting_TEXTDOMAIN)."\" />\n";
	echo "       </div><!-- #casting-submit -->\n";
	// Facebook connect
	?>

<?php
	echo "   </form>\n";
	echo "   </div><!-- .rbform -->\n";

			}

}

if(!$registration){echo "<p class='alert'>". __("The administrator currently disabled the registration.",RBAGENCY_casting_TEXTDOMAIN) ."<p>"; }

echo "  </div><!-- #content -->\n";
echo "</div><!-- #container -->\n";

// Get Sidebar
	$LayoutType = "";
	if (isset($rb_agency_interact_option_castingmanage_sidebar) && $rb_agency_interact_option_castingmanage_sidebar) {
		$LayoutType = "casting";
		get_sidebar();
	}

// Get Footer
echo $rb_footer = RBAgency_Common::rb_footer();
?>
