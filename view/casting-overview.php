<?php

// Profile Class
include(rb_agency_BASEREL ."app/profile.class.php");

echo $rb_header = RBAgency_Common::rb_header();

if (is_user_logged_in()) { 
	global $current_user;
	get_currentuserinfo();
	$curauth = get_user_by('id', $current_user->ID);

	echo "<div id=\"rbdashboard\">\n";
	echo "<h1>Welcome ". $current_user->user_firstname ."</h1>\n";
	echo "<h1>We have registered you as Agent/Producer.</h1>\n";

  // Return them where we found them 
  if (isset($_SESSION['ProfileLastViewed']) && ($_SESSION['ProfileLastViewed'])) {
	
	// What do we call them?
	$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_profilenaming = $rb_agency_options_arr['rb_agency_option_profilenaming'];
	  
	$query = "SELECT * FROM " . table_agency_profile . " WHERE ProfileGallery='". $_SESSION['ProfileLastViewed'] ."'";
	$results = mysql_query($query) or die ( __("Error, query failed", rb_agency_casting_TEXTDOMAIN ));
	$count = mysql_num_rows($results);
	while ($data = mysql_fetch_array($results)) {
		$ProfileGallery			=stripslashes($data['ProfileGallery']);
		$ProfileContactNameFirst=stripslashes($data['ProfileContactNameFirst']);
		$ProfileContactNameLast	=stripslashes($data['ProfileContactNameLast']);
		$ProfileContactDisplay	=stripslashes($data['ProfileContactDisplay']);
		
		// How does it display?

		if ($rb_agency_option_profilenaming == 0) {
			$ProfileContactDisplay = stripslashes($data['ProfileContactNameFirst']) . "". stripslashes($data['ProfileContactNameLast']);
		} elseif ($rb_agency_option_profilenaming == 1) {
			$ProfileContactDisplay = stripslashes($data['ProfileContactNameFirst']) . "". substr(stripslashes($data['ProfileContactNameLast']), 1);
		} elseif ($rb_agency_option_profilenaming == 2) {
			$ProfileContactDisplay = stripslashes($data['ProfileContactDisplay']);
		}

		echo "<div class=\"event\">\n";
		echo "<h3>You have successfully logged in!</h3>\n";
		echo "You may now access the profile data.  You may now return to <strong><a href=\"". rb_agency_PROFILEDIR ."". $ProfileGallery ."\">". $ProfileContactDisplay ."'s</strong></a> profile.\n";
		echo "</div>\n";
		$_SESSION['ProfileLastViewed'] = "";
	}
  } 

if (isset($curauth->user_login)) {
	
	$data_r = $wpdb->get_row("SELECT * FROM ". table_agency_casting . " WHERE CastingUserLinked = " . $current_user->ID);
	$user_data=get_user_meta($current_user->ID,'rb_agency_interact_clientdata',true);
	$user_company=$user_data['company'];

	echo "  <div id=\"profile-info\">\n";
	echo "		<h3>Casting</h3>\n";
	echo "		<ul>\n";
	echo "		<li>Username: <strong>" . $curauth->user_login . "</strong></li>\n";
	echo "		<li>Company: <strong>" . $data_r->CastingContactCompany . "</strong></li>\n";
	echo "		<li>First Name: <strong>" . $data_r->CastingContactNameFirst . "</strong></li>\n";
	echo "		<li>Last Name: <strong>" . $data_r->CastingConactNameLast . "</strong></li>\n";
	echo "		<li>User Email: <strong>" . $data_r->CastingContactEmail . "</strong></li>\n";
	echo "		<li>Work Phone: <strong>" . $data_r->CastingContactPhoneWork . "</strong></li>\n";
	echo "		<li>Cell Phone: <strong>" . $data_r->CastingContactPhoneCell . "</strong></li>\n";
	echo "		</ul>\n";
	echo "		<h4><a href=\"". get_bloginfo("url") ."/casting-manage\" class=\"rb_button\">Edit Information</a></h4>\n";
	echo "		<h4><a href=\"" . wp_logout_url(get_permalink()) . "\" class=\"rb_button\">Logout</a></h4>\n";
	echo "  </div>\n";
	
	echo "  <div id=\"search\">\n";
	echo "    <h2>Search Database</h2>\n";
			
			//set to simple layout
			$profilesearch_layout == 'condensed';
	
			echo RBAgency_Profile::search_form("", "", 0);

	echo "  </div>\n";
}
	/* GET ROLE
	echo rb_agency_get_userrole();
	*/
	echo "</div>\n";

} else {
	include ("include-login.php");
}
	
//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 
?>
