<?php
// *************************************************************************************************** //
// Respond to Login Request
if ( $_SERVER['REQUEST_METHOD'] == "POST" && !empty( $_POST['action'] ) && $_POST['action'] == 'log-in' ) {

	global $error;
	$login = wp_login( $_POST['user-name'], $_POST['password'] );
	$login = wp_signon( array( 'user_login' => $_POST['user-name'], 'user_password' => $_POST['password'], 'remember' => $_POST['remember-me'] ), false );
	
    get_currentuserinfo();
    
	if($login->ID) {
    	wp_set_current_user($login->ID);  // populate
	   	get_user_login_info();
	}
}

function get_user_login_info(){

    global $user_ID;
	$redirect = $_POST["lastviewed"];
	get_currentuserinfo();
	$user_info = get_userdata( $user_ID );

	if($user_ID){
		
		// If user_registered date/time is less than 48hrs from now
			
		if(!empty($redirect)){
			header("Location: ". get_bloginfo("wpurl"). "/profile/".$redirect);
		} else {

			// If Admin, redirect to plugin
			if( $user_info->user_level > 7) {
				header("Location: ". admin_url("admin.php?page=rb_agency_menu"));
			} else {
				header("Location: ". get_bloginfo("wpurl"). "/casting-dashboard/");
			}
	  	}
	} elseif(empty($_POST['user-name']) || empty($_POST['password']) ){
		header("Location: ". get_bloginfo("wpurl"));

	} else {
		// Reload
		header("Location: ". get_bloginfo("wpurl"). "/client-dashboard/");
	}
}

// ****************************************************************************************** //
// Already logged in 
	if (is_user_logged_in()) {

		global $user_ID; 
		$login = get_userdata( $user_ID );
		get_user_login_info();


// ****************************************************************************************** //
// Not logged in
	} else {

		// *************************************************************************************************** //
		// Prepare Page
		echo $rb_header = RBAgency_Common::rb_header();

		echo "<div id=\"rbcontent\" class=\"rb-interact rb-interact-login\">\n";

			// Show Login Form
			$hideregister = true;

				/* Load registration file. */
				require_once( ABSPATH . WPINC . '/registration.php' );
				
				/* Check if users can register. */
				$registration = get_option( 'rb_agencyinteract_options' );
				$rb_agency_interact_option_registerallow = $registration["rb_agencyinteract_option_registerallow"];
				$rb_agency_interact_option_fb_registerallow = $registration['rb_agencyinteract_option_fb_registerallow'];
				$rb_agency_interact_option_fb_app_id = $registration['rb_agencyinteract_option_fb_app_id'];
				$rb_agency_interact_option_fb_app_secret = $registration['rb_agencyinteract_option_fb_app_secret'];
				$rb_agency_interact_option_fb_app_uri = $registration['rb_agencyinteract_option_fb_app_uri'];
				$rb_agency_interact_option_registerallowAgentProducer = $registration['rb_agencyinteract_option_registerallowAgentProducer'];
				if (( current_user_can("create_users") || $rb_agency_interact_option_registerallow )) {
					$widthClass = "half";
				} else {
					$widthClass = "full";
				}

				// File Path: interact/theme/include-login.php
				// Site Url : /profile-login/

				echo "     <div id=\"rbsignin-register\" class=\"rbinteract\">\n";

				if ( $error ) {
				echo "<p class=\"error\">". $error ."</p>\n";
				}

				echo "        <div id=\"rbsign-in\" class=\"inline-block\">\n";
				echo "          <h1>". __("Members Sign in", rb_agency_interact_TEXTDOMAIN). "</h1>\n";
				echo "          <form name=\"loginform\" id=\"login\" action=\"". network_site_url("/"). "casting-login/\" method=\"post\">\n";
				echo "            <div class=\"field-row\">\n";
				echo "              <label for=\"user-name\">". __("Username", rb_agency_interact_TEXTDOMAIN). "</label><input type=\"text\" name=\"user-name\" value=\"". wp_specialchars( $_POST['user-name'], 1 ) ."\" id=\"user-name\" />\n";
				echo "            </div>\n";
				echo "            <div class=\"field-row\">\n";
				echo "              <label for=\"password\">". __("Password", rb_agency_interact_TEXTDOMAIN). "</label><input type=\"password\" name=\"password\" value=\"\" id=\"password\" /> <a href=\"". get_bloginfo('wpurl') ."/wp-login.php?action=lostpassword\">". __("forgot password", rb_agency_interact_TEXTDOMAIN). "?</a>\n";
				echo "            </div>\n";
				echo "            <div class=\"field-row\">\n";
				echo "              <label><input type=\"checkbox\" name=\"remember-me\" value=\"forever\" /> ". __("Keep me signed in", rb_agency_interact_TEXTDOMAIN). "</label>\n";
				echo "            </div>\n";
				echo "            <div class=\"field-row submit-row\">\n";
				echo "              <input type=\"hidden\" name=\"action\" value=\"log-in\" />\n";
				echo "              <input type=\"submit\" value=\"". __("Sign In", rb_agency_interact_TEXTDOMAIN). "\" /><br />\n";
					if($rb_agency_interact_option_fb_registerallow == 1){
							echo " <div class=\"fb-login-button\" scope=\"email\" data-show-faces=\"false\" data-width=\"200\" data-max-rows=\"1\"></div>";
									echo "  <div id=\"fb-root\"></div>

										<script>
										window.fbAsyncInit = function() {
											FB.init({
											appId      : '".$rb_agency_interact_option_fb_app_id."',  ";
									  if(empty($rb_agency_interact_option_fb_app_uri)){  // set default
										   echo "\n channelUrl : '".network_site_url("/")."profile-member/', \n";
									   }else{
										  echo "channelUrl : '".$rb_agency_interact_option_fb_app_uri."',\n"; 
									   }
									 echo "	status     : true, // check login status
											cookie     : true, // enable cookies to allow the server to access the session
											xfbml      : true  // parse XFBML
											});
										  };
										// Load the SDK Asynchronously
										(function(d, s, id) {
										  var js, fjs = d.getElementsByTagName(s)[0];
										  if (d.getElementById(id)) return;
										  js = d.createElement(s); js.id = id;
										  js.src = '//connect.facebook.net/en_US/all.js#xfbml=1&appId=".$rb_agency_interact_option_fb_app_id."'
										  fjs.parentNode.insertBefore(js, fjs);
										}(document, 'script', 'facebook-jssdk'));</script>";
					}
			echo "            </div>\n";
			echo "          </form>\n";
			echo "        </div> <!-- rbsign-in -->\n";

						if (( current_user_can("create_users") || $rb_agency_interact_option_registerallow == 1)) {

			echo "        <div id=\"rbsign-up\" class=\"inline-block\">\n";
			echo "          <div id=\"talent-register\" class=\"register\">\n";
			echo "            <h1>". __("Not a member", rb_agency_interact_TEXTDOMAIN). "?</h1>\n";
			echo "            <h3>". __("Client", rb_agency_interact_TEXTDOMAIN). " - ". __("Register here", rb_agency_interact_TEXTDOMAIN). "</h3>\n";
			echo "            <ul>\n";
			echo "              <li>". __("Create your free profile page", rb_agency_interact_TEXTDOMAIN). "</li>\n";
			echo "              <li><a href=\"". get_bloginfo("wpurl") ."/casting-register\" class=\"rb_button\">". __("Register as Casting Agent", rb_agency_interact_TEXTDOMAIN). "</a></li>\n";
			echo "            </ul>\n";
			echo "          </div> <!-- talent-register -->\n";
			echo "          <div class=\"clear line\"></div>\n";

							/*
							 * Casting Integratino
							 */
							if (is_plugin_active('rb-agency-casting/rb-agency-casting.php')) {
			echo "          <div id=\"agent-register\" class=\"register\">\n";
			echo "            <h3>". __("Casting Agents & Producers", rb_agency_interact_TEXTDOMAIN). "</h3>\n";
			echo "            <ul>\n";
			echo "              <li>". __("List Auditions & Jobs free", rb_agency_interact_TEXTDOMAIN). "</li>\n";
			echo "              <li>". __("Contact People in the Talent Directory", rb_agency_interact_TEXTDOMAIN). "</li>\n";
			echo "              <li><a href=\"". get_bloginfo("wpurl") ."/profile-register/client\" class=\"rb_button\">". __("Register as Agent / Producer", rb_agency_interact_TEXTDOMAIN). "</a></li>\n";
			echo "            </ul>\n";
			echo "          </div> <!-- talent-register -->\n";
							}
			echo "        </div> <!-- rbsign-up -->\n";
						}

			echo "      <div class=\"clear line\"></div>\n";
			echo "      </div>\n";


		echo "</div><!-- #rbcontent -->\n";

	// Get Footer
	echo $rb_footer = RBAgency_Common::rb_footer();

	} // Done

?>