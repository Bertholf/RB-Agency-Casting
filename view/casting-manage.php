<?php
// *************************************************************************************************** //
	global $wpdb;
	global $current_user, $wp_roles;
	get_currentuserinfo();

	// Get Settings
	$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_profilenaming  = isset($rb_agency_options_arr['rb_agency_option_profilenaming']) ? (int)$rb_agency_options_arr['rb_agency_option_profilenaming']:0;
	$rb_agencyinteract_option_registerconfirm = isset($rb_agency_casting_options_arr['rb_agencyinteract_option_registerconfirm']) ? (int)$rb_agency_casting_options_arr['rb_agencyinteract_option_registerconfirm']:0;

	/* Check if users can register. */
	$registration = get_option( 'users_can_register' );

	function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}

	//fetch data from database
	$data_r = $wpdb->get_row("SELECT * FROM ". table_agency_casting . " WHERE CastingUserLinked = " . $current_user->ID);

	/* If user registered, input info. */
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'updatecasting' ) {

		// Error checking
		$error = "";
		$have_error = false;
		
		if ( empty($_POST['casting_first_name'])) {
			$error .= __("First Name is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_POST['casting_last_name'])) {
			$error .= __("Last Name is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( !is_email($_POST['casting_email'])) {
			$error .= __("You must enter a valid email address.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		
		if ( empty($_POST['casting_company'])) {
			$error .= __("Company is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['casting_company'])) {
			$error .= __("Company is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_POST['casting_website'])) {
			$error .= __("website is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
	
		if ( empty($_POST['casting_address'])) {
			$error .= __("Address is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['casting_city'])) {
			$error .= __("City is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['CastingState'])) {
			$error .= __("State is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['casting_zip'])) {
			$error .= __("Zip is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['CastingCountry'])) {
			$error .= __("Country is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
	
		// Bug Free!
		if($have_error == false){

			// Update Record
			$update = "UPDATE " . table_agency_casting . " SET ";

			$update .= "CastingContactNameFirst = '".$_POST['casting_first_name']."',
						CastingContactNameLast = '".$_POST['casting_last_name']."',
						CastingContactEmail = '".$_POST['casting_email']."',
						CastingContactCompany = '".$_POST['casting_company']."',
						CastingContactWebsite = '".$_POST['casting_website']."',
						CastingContactPhoneHome = '".$_POST['CastingContactPhoneHome']."',
						CastingContactPhoneCell = '".$_POST['CastingContactPhoneCell']."',
						CastingContactPhoneWork = '".$_POST['CastingContactPhoneWork']."',
						CastingContactLinkTwitter = '".$_POST['CastingContactLinkTwitter']."',
						CastingContactLinkFacebook = '".$_POST['CastingContactLinkFacebook']."',
						CastingContactLinkYoutube = '".$_POST['CastingContactLinkYouTube']."',
						CastingContactLinkFlickr = '".$_POST['CastingContactLinkFlickr']."',
						CastingLocationStreet = '".$_POST['casting_address']."',
						CastingLocationCity = '".$_POST['casting_city']."',
						CastingLocationState = '".$_POST['CastingState']."',
						CastingLocationZip = '".$_POST['casting_zip']."',
						CastingLocationCountry = '".$_POST['CastingCountry']."', ";
			$update .= "CastingDateUpdated = now() WHERE CastingUserLinked = " . $current_user->ID ;
			
			$result = $wpdb->query($update);        
			
			$error = "Successfully Updated!";

			$data_r = $wpdb->get_row("SELECT * FROM ". table_agency_casting . " WHERE CastingUserLinked = " . $current_user->ID);
			
			//header("Location: ". get_bloginfo("wpurl"). "/casting-dashboard/");			

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
	
	echo "<div id=\"primary\" class=\"rbcol-12 rbcolumn rbagency-casting manage\">\n";
	echo "  <div id=\"content\">\n";

   
		// ****************************************************************************************** //
		// Already logged in 

	if ( $error ) {
		echo "<p class=\"error\">". $error ."</p>\n";
	}

			// Self Registration
	if ( $registration || current_user_can("create_users") ) {
	echo "  <header class=\"entry-header\">";
		echo "<h1>Welcome ". $current_user->user_firstname ."</h1>\n";
		echo "<h1>We have registered you as Agent/Producer.</h1>\n";
	echo "  </header>";
	echo "  <div id=\"client-register\" class=\"rbform\">";
	echo "	<h3>". __("Account Information", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";	
	echo "    <form method=\"post\" action=\"". get_bloginfo('wpurl') ."/casting-manage/\">\n";

	echo "       <div id=\"casting-first-name\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"casting_first_name\">". __("First Name", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_first_name\" type=\"text\" id=\"casting_first_name\" value='".(isset($data_r->CastingContactNameFirst)?$data_r->CastingContactNameFirst:"")."' /></div>\n";
	echo "       </div><!-- #casting-first-name -->\n";

	echo "       <div id=\"casting-last-name\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"casting_last_name\">". __("Last Name", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_last_name\" type=\"text\" id=\"casting_last_name\" value='".(isset($data_r->CastingContactNameLast)?$data_r->CastingContactNameLast:"")."' /></div>\n";
	echo "       </div><!-- #casting_last_name -->\n";

	echo "       <div id=\"casting-email\" class=\"rbfield rbemail rbsingle\">\n";
	echo "       	<label for=\"email\">". __("E-mail (required)", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_email\" type=\"text\" id=\"casting_email\" value='".(isset($data_r->CastingContactEmail)?$data_r->CastingContactEmail:"")."' /></div>\n";
	echo "       </div><!-- #casting-email -->\n";

	echo "       <div id=\"casting-company\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"company\">". __("Company", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_company\" type=\"text\" value='".(isset($data_r->CastingContactCompany) ? $data_r->CastingContactCompany:"")."' /></div>\n";
	echo "       </div><!-- #casting-company -->\n";

	echo "       <div id=\"casting-website\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"website\">". __("Website", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_website\" type=\"text\" id=\"casting_email\" value='".(isset($data_r->CastingContactWebsite)?$data_r->CastingContactWebsite:"")."' /></div>\n";
	echo "       </div><!-- #casting-website -->\n";

	echo "       <div id=\"casting-street-address\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"street-address\">". __("Street Address", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_address\" type=\"text\" value='".(isset($data_r->CastingLocationStreet) ?$data_r->CastingLocationStreet:"")."' /></div>\n";
	echo "       </div><!-- #casting-street-address -->\n";

	echo "       <div id=\"casting-city\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"city\">". __("City", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_city\" type=\"text\" id=\"casting_email\" value='".(isset($data_r->CastingLocationCity)?$data_r->CastingLocationCity:"")."' /></div>\n";
	echo "       </div><!-- #casting-city -->\n";	

	echo "<input type='hidden' value='".admin_url('admin-ajax.php')."' id='url'>";
	echo "       <div id=\"casting-country\" class=\"rbfield rbtext rbsingle\">\n";
				echo "		<label>". __("Country", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
				echo "		<div>\n";
				$query_get ="SELECT * FROM `".table_agency_data_country."` ORDER BY CountryTitle ASC" ;
				$result_query_get = $wpdb->get_results($query_get);
				echo '<select name="CastingCountry" id="CastingCountry"  onchange="javascript:populateStates();">';
				echo '<option value="">'. __("Select country", _TEXTDOMAIN) .'</option>';
					foreach($result_query_get as $r){
						echo '<option value='.$r->CountryID.' '.selected(isset($data_r->CastingLocationCountry)?$data_r->CastingLocationCountry:0,$r->CountryID,false).' >'.$r->CountryTitle.'</option>';
					}
				echo '</select>';
	echo "       </div></div><!-- #casting-country -->\n";

	echo "       <div id=\"casting-state\" class=\"rbfield rbselect rbsingle\">\n";
				echo "		<label>". __("State", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
				echo "		<div>\n";

				if(isset($_POST['CastingCountry']) && !empty($_POST['CastingCountry']) || isset( $data_r->CastingLocationCountry ) && $data_r->CastingLocationCountry != ""){
						$query_get ="SELECT * FROM ".table_agency_data_state." WHERE CountryID = " .$data_r->CastingLocationCountry ;
				} else {
						$query_get ="SELECT * FROM `".table_agency_data_state."`" ;
				}
				$result_query_get = $wpdb->get_results($query_get);
				echo '<select name="CastingState" id="CastingState">';
				echo '<option value="">'. __("Select state", RBAGENCY_casting_TEXTDOMAIN) .'</option>';
					foreach($result_query_get as $r){

						echo '<option value='.$r->StateID.' '.selected($data_r->CastingLocationState,$r->StateID,false).' >'.$r->StateTitle.'</option>';
					}
				echo '</select>';

	echo "       </div></div><!-- #casting-state -->\n";

	echo "       <div id=\"casting-zip\" class=\"rbfield rbtext rbsingle\">\n";
	echo "       	<label for=\"zip\">". __("Zip", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "       	<div><input class=\"text-input\" name=\"casting_zip\" type=\"text\" id=\"casting_email\" value='".(isset($data_r->CastingLocationZip)?$data_r->CastingLocationZip:"")."' /></div>\n";
	echo "       </div><!-- #casting-zip -->\n";

		echo "	<h3>". __("Contact Phone", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
		echo "	<div id=\"profile-facebook\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Home", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" name=\"CastingContactPhoneHome\" value=\"". (isset($data_r->CastingContactPhoneHome)?$data_r->CastingContactPhoneHome:"") ."\" />\n";
		echo "	</div></div>\n";
		echo "	<div id=\"profile-twitter\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Cell", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" name=\"CastingContactPhoneCell\" value=\"".(isset($data_r->CastingContactPhoneCell)?$data_r->CastingContactPhoneCell:"") ."\" />\n";
		echo "	</div></div>\n";
		echo "	<div id=\"profile-youtube\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Work", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" name=\"CastingContactPhoneWork\" value=\"". (isset($data_r->CastingContactPhoneWork)?$data_r->CastingContactPhoneWork:"") ."\" />\n";
		echo "  </div></div>\n";

		// Show Social Media Links
		echo "	<h3>". __("Social Media Castings", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
		echo "	<div id=\"profile-facebook\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Facebook", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" id=\"CastingContactLinkFacebook\" name=\"CastingContactLinkFacebook\" value=\"".(isset($data_r->CastingContactLinkFacebook) ?$data_r->CastingContactLinkFacebook:"") ."\" />\n";
		echo "	</div></div>\n";
		echo "	<div id=\"profile-twitter\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Twitter", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" id=\"CastingContactLinkTwitter\" name=\"CastingContactLinkTwitter\" value=\"". (isset($data_r->CastingContactLinkTwitter)?$data_r->CastingContactLinkTwitter:"") ."\" />\n";
		echo "	</div></div>\n";
		echo "	<div id=\"profile-youtube\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("YouTube", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" id=\"CastingContactLinkYouTube\" name=\"CastingContactLinkYouTube\" value=\"". (isset($data_r->CastingContactLinkYoutube)?$data_r->CastingContactLinkYoutube:"") ."\" />\n";
		echo "  </div></div>\n";
		echo "	<div id=\"profile-flickr\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Flickr", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" id=\"CastingContactLinkFlickr\" name=\"CastingContactLinkFlickr\" value=\"". (isset($data_r->CastingContactLinkFlickr)?$data_r->CastingContactLinkFlickr:"") ."\" />\n";
		echo "	</div></div>\n";

		if (isset($rb_agencyinteract_option_registerallow) && $rb_agencyinteract_option_registerallow  == 1) {
			echo "	<div id=\"profile-username\" class=\"rbfield rbtext rbsingle\">\n";
			echo "		<label>". __("Username", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
			echo "		<div>\n";
			if(isset($current_user->user_login)){
			echo "			<input type=\"text\" id=\"CastingUsername\"  name=\"CastingUsername\" disabled=\"disabled\" value=\"".$current_user->user_login."\" />\n";
			} else {
			echo "			<input type=\"text\" id=\"CastingUsername\"  name=\"CastingUsername\" value=\"\" />\n";	
			}
			echo "			<small class=\"rbfield-note\">Cannot be changed</small>";
			echo "		</div>\n";
			echo "  </div>\n";
		}
		echo "	<h3>". __("Login Settings", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";		
		echo "	<div id=\"rbprofile-password\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Password", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div>";
		echo "			<input type=\"password\" id=\"CastingPassword\" name=\"CastingPassword\" />\n";
		echo "			<small class=\"rbfield-note\">Leave blank to keep same password</small>";	
		echo "	 	</div>\n";
		echo "	</div>\n";
		echo "	<div id=\"rbprofile-retype-password\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Retype Password", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div>";
		echo "			<input type=\"password\" id=\"CastingPasswordConfirm\" name=\"CastingPasswordConfirm\" />";
		echo "			<small class=\"rbfield-note\">Retype to Confirm</small>";	
		echo "		</div>\n";
		echo "	</div>\n";

	echo "       <div id=\"casting-submit\" class=\"rbfield rbsubmit rbsingle\">\n";
	echo "       	<input name=\"adduser\" type=\"submit\" id=\"addusersub\" class=\"submit button\" value='Update Information'/>";

					// if ( current_user_can("create_users") ) {  _e("Add User", RBAGENCY_casting_TEXTDOMAIN); } else {  _e("Register", RBAGENCY_casting_TEXTDOMAIN); } echo "\" />\n";
					
					wp_nonce_field("add-user");

	echo "       	<input name=\"action\" type=\"hidden\" id=\"action\" value=\"updatecasting\" />\n";
	echo "       </div><!-- #casting-submit -->\n";
	// Facebook connect
	?>

<?php
	echo "   </form>\n";
	echo "   </div><!-- .rbform -->\n";

}

if(!$registration){ echo "<p class='alert'>The administrator currently disabled the registration.<p>"; }

echo "  </div><!-- #content -->\n";
echo "</div><!-- #container -->\n";

// Get Sidebar 
	$LayoutType = "";
	if (isset($rb_agencyinteract_option_castingmanage_sidebar) && $rb_agencyinteract_option_castingmanage_sidebar) {
		$LayoutType = "casting";
		get_sidebar(); 
	}

// Get Footer
echo $rb_footer = RBAgency_Common::rb_footer();
?>
