<?php
global $current_user;
get_currentuserinfo();
$curauth = get_user_by('id', $current_user->ID);

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

echo $rb_header = RBAgency_Common::rb_header();

if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) || current_user_can( 'edit_posts' )){

	// add advanced search
	?>
	<script type='text/javascript'>
		jQuery(document).ready(function(){
			jQuery("body").on('click','#asearch', function(){
				window.location.href='<?php echo get_bloginfo('wpurl'); ?>/search-advanced/'; 
			});
			var htm = '<input class="button-primary" id="asearch" type="button" value="Advanced Search">';
			jQuery('.rbsubmit').append(htm);
		});
    </script>
	<?php
	echo "<div id=\"rbdashboard\">\n";
	echo "<h1>Welcome ". $curauth->user_login ."</h1>\n";
	if (current_user_can( 'edit_posts' )){
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
	$results = $wpdb->get_results($query,ARRAY_A);
	$count = $wpdb->num_rows;
	foreach($results as $data) {
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
		echo "You may now access the profile data.  You may now return to <strong><a href=\"". RBAGENCY_PROFILEDIR ."". $ProfileGallery ."\">". $ProfileContactDisplay ."'s</strong></a> profile.\n";
		echo "</div>\n";
		$_SESSION['ProfileLastViewed'] = "";
	}
  }

	$data_r = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". table_agency_casting . " WHERE CastingUserLinked = %d ",$current_user->ID),OBJECT,0);
	$user_data = get_user_meta($current_user->ID,'rb_agency_interact_clientdata',true);
	$user_company = isset($user_data['CastingContactCompany'])?$user_data['CastingContactCompany']:"";

	echo "  <div id=\"profile-info\">\n";
	echo "		<h3>Casting</h3>\n";
	echo "		<ul>\n";

	echo "		<li>Username: <strong>" . $curauth->user_login . "</strong></li>\n";

	if(isset($data_r->CastingContactNameFirst) && $data_r->CastingContactNameFirst != ""){
		echo "		<li>First Name: <strong>" . $data_r->CastingContactNameFirst . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactNameLast) && $data_r->CastingContactNameLast != ""){
		echo "		<li>Last Name: <strong>" . $data_r->CastingContactNameLast . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactEmail) && $data_r->CastingContactEmail != ""){
		echo "		<li>User Email: <strong>" . $data_r->CastingContactEmail . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactCompany) && $data_r->CastingContactCompany != ""){
		echo "		<li>Company: <strong>" . $data_r->CastingContactCompany . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactWebsite) && $data_r->CastingContactWebsite != ""){
		echo "		<li>Website: <strong>" . $data_r->CastingContactWebsite . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationStreet) && $data_r->CastingLocationStreet != ""){
		echo "		<li>Street: <strong>" . $data_r->CastingLocationStreet . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationCity) && $data_r->CastingLocationCity !=""){
		echo "		<li>City: <strong>" . $data_r->CastingLocationCity . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationCountry) && $data_r->CastingLocationCountry!=""){
		echo "		<li>Country: <strong>" . rb_agency_getCountryTitle($data_r->CastingLocationCountry) . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationState) && $data_r->CastingLocationState!=""){
		echo "		<li>State: <strong>" . rb_agency_getStateTitle($data_r->CastingLocationState) . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationZip) && $data_r->CastingLocationZip!=""){
		echo "		<li>Zip: <strong>" . $data_r->CastingLocationZip . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactPhoneHome) && $data_r->CastingContactPhoneHome!=""){
		echo "		<li>Home Phone: <strong>" . $data_r->CastingContactPhoneHome . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactPhoneWork) && $data_r->CastingContactPhoneWork!=""){
		echo "		<li>Work Phone: <strong>" . $data_r->CastingContactPhoneWork . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactPhoneCell) && $data_r->CastingContactPhoneCell!=""){
		echo "		<li>Cell Phone: <strong>" . $data_r->CastingContactPhoneCell . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactLinkFacebook) && $data_r->CastingContactLinkFacebook!=""){
		echo "		<li>Facebook: <strong>" . $data_r->CastingContactLinkFacebook . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactLinkTwitter) && $data_r->CastingContactLinkTwitter!=""){
		echo "		<li>Twitter: <strong>" . $data_r->CastingContactLinkTwitter . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactLinkYoutube) && $data_r->CastingContactLinkYoutube!=""){
		echo "		<li>Youtube: <strong>" . $data_r->CastingContactLinkYoutube . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactLinkTwitter) && $data_r->CastingContactLinkTwitter!=""){
		echo "		<li>Flickr: <strong>" . $data_r->CastingContactLinkTwitter . "</strong></li>\n";
	}

	//Custom Fields
	rb_agency_get_casting_dashboard_customfields();
	//END Custom fields

	echo "		</ul>\n";
	echo "		<ul class=\"links\">\n";
	echo "			<li><a href=\"". get_bloginfo("url") ."/casting-manage\">Edit Information</a></li>\n";
	
	if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_postnewjob'])){
		echo "		<li><a href=\"". get_bloginfo("url") ."/casting-postjob\">Post a New Job</a></li>\n";
	}
	
	if (current_user_can( 'edit_posts' )){
		if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_viewjobposting'])){
			echo "	<li><a href=\"". get_bloginfo("url") ."/browse-jobs\">View All Job Postings</a></li>\n";
		}
		
		if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_viewapplicants'])){
			echo "	<li><a href=\"". get_bloginfo("url") ."/view-applicants\">View All Applicants</a></li>\n";
		}
	} else {
		if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_viewjobposting'])){
			echo "	<li><a href=\"". get_bloginfo("url") ."/browse-jobs\">View Your Job Postings</a></li>\n";
		}
		if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_viewapplicants'])){
			echo "	<li><a href=\"". get_bloginfo("url") ."/view-applicants\">View Your Applicants</a></li>\n";
		}
		
		echo "		<li><a href=\"". get_bloginfo("url") ."/profile-casting\">View Your Casting Cart</a></li>\n";
	}


	echo "			<li><a href=\"" . wp_logout_url( get_bloginfo("url")."/casting-login/") . "\">Log out</a></li>\n";
	echo "  	</ul><!-- .links -->\n";


	echo "  </div>\n";

	echo "  <div id=\"search\">\n";
	echo "    <h2>Search Database</h2>\n";

			echo RBAgency_Profile::search_form("", "", 0, 0);

	echo "  </div>\n";

	echo "</div>\n";

} else {
	include ("include-login.php");
}

//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 
?>
