<?php
global $current_user;
get_currentuserinfo();
$curauth = get_user_by('id', $current_user->ID);

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID,'CastingIsActive') == 3){
	header("Location:".get_bloginfo("wpurl").'/casting-pending?status=pending');
	exit();
}

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
	echo "<div id=\"rbcontent\">\n";
	echo "<h1>Welcome ". $curauth->user_login ."</h1>\n";
	if (current_user_can( 'edit_posts' )){
		echo "<h1>".__("You are logged in as Administrator.",RBAGENCY_casting_TEXTDOMAIN)."</h1>\n";
	} else {
		echo "<h1>".__("We have registered you as Agent/Producer.",RBAGENCY_casting_TEXTDOMAIN)."</h1>\n";
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
		echo "<h3>".__("You have successfully logged in!",RBAGENCY_casting_TEXTDOMAIN)."</h3>\n";
		echo __("You may now access the profile data.  You may now return to ",RBAGENCY_casting_TEXTDOMAIN)."<strong><a href=\"". RBAGENCY_PROFILEDIR ."". $ProfileGallery ."\">". $ProfileContactDisplay ."'s</strong></a> profile.\n";
		echo "</div>\n";
		$_SESSION['ProfileLastViewed'] = "";
	}
  }

	$data_r = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". table_agency_casting . " WHERE CastingUserLinked = %d ",$current_user->ID),OBJECT,0);
	$user_data = get_user_meta($current_user->ID,'rb_agency_interact_clientdata',true);
	$user_company = isset($user_data['CastingContactCompany'])?$user_data['CastingContactCompany']:"";

	echo "  <div id=\"profile-info\">\n";
	
	echo "		<ul class=\"links\">\n";
	echo "			<li><a href=\"". get_bloginfo("url") ."/casting-account\" class=\"pure-button button-small\">".__("Overview",RBAGENCY_casting_TEXTDOMAIN)."</a></li>\n";	
	
	$is_active = rb_check_casting_status();
	if($is_active == true){
		if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_postnewjob'])){
			echo "		<li><a href=\"". get_bloginfo("url") ."/casting-postjob\" class=\"pure-button button-small\">".__("Post a New Job",RBAGENCY_casting_TEXTDOMAIN)."</a></li>\n";
		}
	}
	if (current_user_can( 'edit_posts' )){
		if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_viewjobposting'])){
			echo "	<li><a href=\"". get_bloginfo("url") ."/browse-jobs\" class=\"pure-button button-small\">".__("All Job Postings",RBAGENCY_casting_TEXTDOMAIN)."</a></li>\n";
		}
		
		if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_viewapplicants'])){
			echo "	<li><a href=\"". get_bloginfo("url") ."/view-applicants\" class=\"pure-button button-small\">".__("All Applicants",RBAGENCY_casting_TEXTDOMAIN)."</a></li>\n";
		}
	} else {
		
		if($is_active == true){
			if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_viewjobposting'])){
				echo "	<li><a href=\"". get_bloginfo("url") ."/browse-jobs\" class=\"pure-button button-small\">".__("My Job Postings",RBAGENCY_casting_TEXTDOMAIN)."</a></li>\n";
			}
			if(isset($rb_agency_options_arr['rb_agency_option_castingbutton_viewapplicants'])){
				echo "	<li><a href=\"". get_bloginfo("url") ."/view-applicants\" class=\"pure-button button-small\">".__("My Applicants",RBAGENCY_casting_TEXTDOMAIN)."</a></li>\n";
			}
		}
		echo "		<li><a href=\"". get_bloginfo("url") ."/profile-casting\" class=\"pure-button button-small\">".__("My Casting Cart",RBAGENCY_casting_TEXTDOMAIN)."</a></li>\n";
	}

	echo "			<li><a href=\"". get_bloginfo("url") ."/casting-manage\" class=\"pure-button button-small\">".__("Edit Information",RBAGENCY_casting_TEXTDOMAIN)."</a></li>\n";
	echo "			<li><a href=\"" . wp_logout_url( get_bloginfo("url")."/casting-login/") . "\" class=\"pure-button button-small\">".__("Log out",RBAGENCY_casting_TEXTDOMAIN)."</a></li>\n";
	echo "  	</ul><!-- .links -->\n";


	echo "  </div>\n";

	echo "  <div id=\"search\">\n";
	echo "		<ul>\n";

	echo "		<li>".__("Username:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $curauth->user_login . "</strong></li>\n";

	if(isset($data_r->CastingContactNameFirst) && $data_r->CastingContactNameFirst != ""){
		echo "		<li>".__("First Name:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingContactNameFirst . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactNameLast) && $data_r->CastingContactNameLast != ""){
		echo "		<li>".__("Last Name:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingContactNameLast . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactEmail) && $data_r->CastingContactEmail != ""){
		echo "		<li>".__("User Email:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingContactEmail . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactCompany) && $data_r->CastingContactCompany != ""){
		echo "		<li>".__("Company:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingContactCompany . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactWebsite) && $data_r->CastingContactWebsite != ""){
		echo "		<li>".__("Website:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingContactWebsite . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationStreet) && $data_r->CastingLocationStreet != ""){
		echo "		<li>".__("Street:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingLocationStreet . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationCity) && $data_r->CastingLocationCity !=""){
		echo "		<li>".__("City:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingLocationCity . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationCountry) && $data_r->CastingLocationCountry!=""){
		echo "		<li>".__("Country:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . rb_agency_getCountryTitle($data_r->CastingLocationCountry) . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationState) && $data_r->CastingLocationState!=""){
		echo "		<li>".__("State:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . rb_agency_getStateTitle($data_r->CastingLocationState) . "</strong></li>\n";
	}

	if(isset($data_r->CastingLocationZip) && $data_r->CastingLocationZip!=""){
		echo "		<li>".__("Zip:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingLocationZip . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactPhoneHome) && $data_r->CastingContactPhoneHome!=""){
		echo "		<li>".__("Home Phone:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingContactPhoneHome . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactPhoneWork) && $data_r->CastingContactPhoneWork!=""){
		echo "		<li>".__("Work Phone:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingContactPhoneWork . "</strong></li>\n";
	}

	if(isset($data_r->CastingContactPhoneCell) && $data_r->CastingContactPhoneCell!=""){
		echo "		<li>".__("Cell Phone:",RBAGENCY_casting_TEXTDOMAIN)." <strong>" . $data_r->CastingContactPhoneCell . "</strong></li>\n";
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
	echo "  </div>\n";

	echo "</div><!-- #rbcontent -->\n";
	echo "</div><!-- #rbdashboard -->\n";

} else {
	include ("include-login.php");
}

//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 
?>
