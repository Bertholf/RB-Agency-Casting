<?php
	
	/* Check if users can register. */
	$registration = get_option( 'rb_agencyinteract_options' );
	$rb_agencyinteract_option_registerallow = isset($registration["rb_agencyinteract_option_registerallow"]) ? $registration["rb_agencyinteract_option_registerallow"]:0;
	$rb_agencyinteract_option_registerallowAgentProducer = isset($registration['rb_agencyinteract_option_registerallowAgentProducer'])?$registration['rb_agencyinteract_option_registerallowAgentProducer']:0;
	if (( current_user_can("create_users") || $rb_agencyinteract_option_registerallow )) {
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
	echo "              <label for=\"user-name\">". __("Username", rb_agency_interact_TEXTDOMAIN). "</label><input type=\"text\" name=\"user-name\" value=\"". esc_attr( isset($_POST['user-name'])?$_POST['user-name']:"", 1 ) ."\" id=\"user-name\" />\n";
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
	echo "            </div>\n";
	echo "          </form>\n";
	echo "        </div> <!-- rbsign-in -->\n";

	if (( current_user_can("create_users") || $rb_agencyinteract_option_registerallow == 1)) {
	
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