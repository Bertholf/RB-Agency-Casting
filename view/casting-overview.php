<?php

global $current_user;
get_currentuserinfo();
$curauth = get_user_by('id', $current_user->ID);

// Profile Class
include(rb_agency_BASEREL ."app/profile.class.php");

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

wp_deregister_script('jquery'); 
wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
wp_enqueue_script('jquery_latest');

echo $rb_header = RBAgency_Common::rb_header();

if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) || current_user_can( 'manage_options' )){

	// add advanced search
	?>
	<script type='text/javascript'>
			jQuery(document).ready(function(){
				jQuery("body").on('click','#asearch', function(){
					window.location.href='<?php echo get_bloginfo('wpurl'); ?>/search-advanced/'; 
				});
				var htm = '<input class="button-primary" id="asearch" type="button" value="Advance Search">';
				jQuery('.rbsubmit').append(htm);
			});
    </script>
	<?php	
	echo "<div id=\"rbdashboard\">\n";
	echo "<h1>Welcome ". $curauth->user_login ."</h1>\n";
	if (current_user_can( 'manage_options' )){
		echo "<h1>You are logged in as Administrator.</h1>\n";
	} else {
		echo "<h1>We have registered you as Agent/Producer.</h1>\n";
	}
	
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

	$data_r = $wpdb->get_row("SELECT * FROM ". table_agency_casting . " WHERE CastingUserLinked = " . $current_user->ID);
	$user_data=get_user_meta($current_user->ID,'rb_agency_interact_clientdata',true);
	$user_company=$user_data['company'];

	echo "  <div id=\"profile-info\">\n";
	echo "		<h3>Casting</h3>\n";
	echo "		<ul>\n";

	echo "		<li>Username: <strong>" . $curauth->user_login . "</strong></li>\n";

	if($data_r->CastingContactNameFirst != ""){
		echo "		<li>First Name: <strong>" . $data_r->CastingContactNameFirst . "</strong></li>\n";
	}

	if($data_r->CastingConactNameLast != ""){
		echo "		<li>Last Name: <strong>" . $data_r->CastingConactNameLast . "</strong></li>\n";
	}

	if($data_r->CastingConactEmail != ""){	
		echo "		<li>User Email: <strong>" . $data_r->CastingContactEmail . "</strong></li>\n";
	}

	if($data_r->CastingConactCompany != ""){	
		echo "		<li>Company: <strong>" . $data_r->CastingContactCompany . "</strong></li>\n";
	}

	if($data_r->CastingContactWebsite != ""){	
		echo "		<li>Website: <strong>" . $data_r->CastingContactWebsite . "</strong></li>\n";
	}
	
	if($data_r->CastingLocationStreet != ""){		
		echo "		<li>Street: <strong>" . $data_r->CastingLocationStreet . "</strong></li>\n";
	}
	
	if($data_r->CastingLocationCity!=""){
		echo "		<li>City: <strong>" . $data_r->CastingLocationCity . "</strong></li>\n";	
	}

	if($data_r->CastingLocationCountry!=""){	
		echo "		<li>Country: <strong>" . $data_r->CastingLocationCountry . "</strong></li>\n";	
	}

	if($data_r->CastingLocationState!=""){	
		echo "		<li>State: <strong>" . $data_r->CastingLocationState . "</strong></li>\n";	
	}

	if($data_r->CastingLocationZip!=""){	
		echo "		<li>Zip: <strong>" . $data_r->CastingLocationZip . "</strong></li>\n";	
	}

	if($data_r->CastingContactPhoneHome!=""){	
		echo "		<li>Home Phone: <strong>" . $data_r->CastingContactPhoneHome . "</strong></li>\n";
	}

	if($data_r->CastingContactPhoneWork!=""){	
		echo "		<li>Work Phone: <strong>" . $data_r->CastingContactPhoneWork . "</strong></li>\n";
	}

	if($data_r->CastingContactPhoneCell!=""){	
		echo "		<li>Cell Phone: <strong>" . $data_r->CastingContactPhoneCell . "</strong></li>\n";
	}

	if($data_r->CastingContactLinkFacebook!=""){	
		echo "		<li>Facebook: <strong>" . $data_r->CastingContactLinkFacebook . "</strong></li>\n";	
	}
	
	if($data_r->CastingContactLinkTwitter!=""){	
		echo "		<li>Twitter: <strong>" . $data_r->CastingContactLinkTwitter . "</strong></li>\n";	
	}
	
	if($data_r->CastingContactLinkYoutube!=""){	
		echo "		<li>Youtube: <strong>" . $data_r->CastingContactLinkYoutube . "</strong></li>\n";	
	}

	if($data_r->CastingContactLinkTwitter!=""){		
		echo "		<li>Flickr: <strong>" . $data_r->CastingContactLinkTwitter . "</strong></li>\n";	
	}
	
	echo "		</ul>\n";
	echo "		<h4><a href=\"". get_bloginfo("url") ."/casting-manage\" class=\"rb_button\">Edit Information</a></h4>\n";
	echo "		<h4><a href=\"". get_bloginfo("url") ."/casting-postjob\" class=\"rb_button\">Post a New Job</a></h4>\n";
	
	if (current_user_can( 'manage_options' )){
		echo "		<h4><a href=\"". get_bloginfo("url") ."/browse-jobs\" class=\"rb_button\">View All Job Postings</a></h4>\n";
		echo "		<h4><a href=\"". get_bloginfo("url") ."/view-applicants\" class=\"rb_button\">View All Applicants</a></h4>\n";
	} else {
		echo "		<h4><a href=\"". get_bloginfo("url") ."/browse-jobs\" class=\"rb_button\">View Your Job Postings</a></h4>\n";
		echo "		<h4><a href=\"". get_bloginfo("url") ."/view-applicants\" class=\"rb_button\">View Your Applicants</a></h4>\n";
	}
	
	echo "		<h4><a href=\"" . wp_logout_url(get_permalink()) . "\" class=\"rb_button\">Logout</a></h4>\n";
	echo "  </div>\n";
	
	echo "  <div id=\"search\">\n";
	echo "    <h2>Search Database</h2>\n";
			
			//set to simple layout
			$profilesearch_layout == 'condensed';
	
			echo RBAgency_Profile::search_form("", "", 0);

	echo "  </div>\n";

	echo "</div>\n";

} else {
	include ("include-login.php");
}
	
//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 
?>
