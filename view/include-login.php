<?php

	/* Load registration file. */
	require_once( ABSPATH . WPINC . '/registration.php' );
	
	/* Check if users can register. */
	$registration = get_option( 'rb_agencyinteract_options' );
	$rb_agency_interact_option_registerallow = $registration["rb_agencyinteract_option_registerallow"];
	// Facebook Login Integration
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
	
	
				echo "        </div> <!-- rbsign-up -->\n";
	}
				
	echo "      <div class=\"clear line\"></div>\n";
	echo "      </div>\n";
?>