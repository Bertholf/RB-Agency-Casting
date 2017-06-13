<?php
global $wpdb;
define("LabelPlural", "Pending Clients");
define("LabelSingular", "Pending Client");
$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_unittype  			= $rb_agency_options_arr['rb_agency_option_unittype'];
	$rb_agency_option_showsocial 			= $rb_agency_options_arr['rb_agency_option_showsocial'];
	$rb_agency_option_agencyimagemaxheight 	= $rb_agency_options_arr['rb_agency_option_agencyimagemaxheight'];
		if (empty($rb_agency_option_agencyimagemaxheight) || $rb_agency_option_agencyimagemaxheight < 500) {$rb_agency_option_agencyimagemaxheight = 800; }
	$rb_agency_option_profilenaming 		= (int)$rb_agency_options_arr['rb_agency_option_profilenaming'];
	$rb_agency_option_locationtimezone 		= (int)$rb_agency_options_arr['rb_agency_option_locationtimezone'];

// *************************************************************************************************** //
// Handle Post Actions
if (isset($_POST['action']) && $_POST["action"] ==  'deleteRecord' ) {
	// Get Post State
	foreach($_POST as $CastingID) {
			// Verify Record

			$queryDelete = "SELECT * FROM ". table_agency_casting ." WHERE CastingID =  ". $CastingID;
			$resultsDelete = $wpdb->get_results($queryDelete,ARRAY_A);
			foreach ($resultsDelete as $dataDelete) {
				$CastingGallery = $dataDelete['CastingGallery'];

				// Remove Profile
				$delete = "DELETE FROM " . table_agency_casting . " WHERE CastingID = ". $CastingID;
				$results = $wpdb->query($delete);
				//remove the account @ wp-users table.

				wp_delete_user( (int)$dataDelete['CastingUserLinked']);
				//bulk acton

				if (isset($CastingGallery)) {
					// Remove Folder
					$dir = RBAGENCY_UPLOADPATH . $CastingGallery ."/";
					$mydir = opendir($dir);

					if($mydir){
						while(false !== ($file = readdir($mydir))) {
							if($file != "." && $file != ".." && !empty($file)){
								unlink($dir.$file) or DIE("couldn't delete file '$file'<br />");
							}
						}
						closedir($mydir);
					}
					// remove dir
					if(is_dir($dir)) {
						@rmdir($dir) or DIE("couldn't delete $dir$file<br />");
					}
				} else {
					echo __("No valid record found.", RBAGENCY_casting_TEXTDOMAIN);
				}

			echo ('<div id="message" class="updated"><p>'. __("Client deleted successfully!", RBAGENCY_casting_TEXTDOMAIN) .'</p></div>');
			}// is there record?

	}

} elseif(isset($_GET["action"]) && $_GET["action"] =="deleteRecord"){
	$CastingID = $_GET["CastingID"];
	$queryDelete = "SELECT * FROM ". table_agency_casting ." WHERE CastingID =  ". $CastingID;
			$resultsDelete = $wpdb->get_results($queryDelete,ARRAY_A);
			foreach ($resultsDelete as $dataDelete) {
				$CastingGallery = $dataDelete['CastingGallery'];

				// Remove Profile
				$delete = "DELETE FROM " . table_agency_casting . " WHERE CastingID = ". $CastingID;
				$results = $wpdb->query($delete);

				// Delete casting jobs
				$deleteCastingJobs = "DELETE FROM ".table_agency_casting_job." WHERE Job_UserLinked = ". $dataDelete['CastingUserLinked'];
				$wpdb->query($deleteCastingJobs);

				//remove the account @ wp-users table.
				wp_delete_user( (int)$dataDelete['CastingUserLinked']);

				flush();
				if (isset($CastingGallery)) {
					// Remove Folder
					$dir = RBAGENCY_UPLOADPATH . $CastingGallery ."/";
					$mydir = opendir($dir);
					if($mydir){
						while(false !== ($file = readdir($mydir))) {
							if($file != "." && $file != ".." && !empty($file)){
								unlink($dir.$file) or DIE("couldn't delete file '$file'<br />");
							}
						}
						closedir($mydir);
					}
					// remove dir
					if(is_dir($dir)) {
						@rmdir($dir) or DIE("couldn't delete $dir$file<br />");
					}

				} else {
					echo __("No valid record found.", RBAGENCY_casting_TEXTDOMAIN);
				}

			echo ('<div id="message" class="updated"><p>'. __("Client deleted successfully!", RBAGENCY_casting_TEXTDOMAIN) .'</p></div>');
			}// is there record?

			rb_display_list();
}
elseif(!isset($_GET["action"]) || isset($_GET["action"]) && $_GET["action"] =="approveRecord"){
// *************************************************************************************************** //
	// Show List
	rb_display_list();
}
elseif(isset($_GET["action"]) && $_GET["action"] !="approveRecord") {

	rb_manage_client($_GET["CastingID"]);

}
// *************************************************************************************************** //
// Edit Record

function rb_manage_client($CastingID) {
  global $wpdb;
  $rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_locationtimezone 		= (int)$rb_agency_options_arr['rb_agency_option_locationtimezone'];
  echo "<div class=\"wrap\">\n";
  echo "  <div id=\"rb-overview-icon\" class=\"icon32\"></div>\n";
  echo "  <h2>". __("List", RBAGENCY_casting_TEXTDOMAIN) ." ". LabelPlural ."</h2>\n";

  echo "  <h3 class=\"title\">". __("Edit Record", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
   echo "<a class=\"button-primary\" href=\"".admin_url("admin.php?page=rb_agency_casting_approveclients")."\">Back to Client List</a>";
    $rb_agency_option_profilenaming  = isset($rb_agency_options_arr['rb_agency_option_profilenaming'])?(int)$rb_agency_options_arr['rb_agency_option_profilenaming']:0;
	$rb_agencyinteract_option_registerconfirm = isset($rb_agency_interact_options_arr['rb_agencyinteract_option_registerconfirm'])?(int)$rb_agency_interact_options_arr['rb_agencyinteract_option_registerconfirm']:0;

	/* Check if users can register. */
	$registration = get_option( 'users_can_register' );

	function base64_url_decode($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}

	$have_error = false;
	$error = "";
	if(isset($_POST["action"]) && $_POST["action"] == 'updatecasting'){

		if ( empty($_POST['casting_first_name'])) {
			//$error .= __("First Name is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}

		if ( empty($_POST['casting_last_name'])) {
			//$error .= __("Last Name is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}

		if ( !email_exists($_POST['casting_email'])) {
			$error .= __("You must enter a valid email address.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_POST['casting_company'])) {
			$error .= __("Company is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['CastingPassword'])) {
			//$error .= __("Password is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}

		if ( empty($_POST['casting_website'])) {
			//$error .= __("website is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}

		if ( empty($_POST['casting_address'])) {
			//$error .= __("Address is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}
		if ( empty($_POST['casting_city'])) {
			//$error .= __("City is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}
		if ( empty($_POST['CastingState'])) {
			//$error .= __("State is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}
		if ( empty($_POST['casting_zip'])) {
			//$error .= __("Zip is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
		}
		if ( empty($_POST['CastingCountry'])) {
			//$error .= __("Country is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			//$have_error = true;
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
			$update .= "CastingDateUpdated = now(), CastingIsActive = '".$_POST["CastingIsActive"]."' WHERE CastingID = " . $_POST["CastingID"] ;

			$result = $wpdb->query($update);

			$error = '<div id="message" class="updated"><p>'. __("Client updated successfully!", RBAGENCY_casting_TEXTDOMAIN) .'</p></div>';



		}

	}
	//fetch data from database
	$data_r = $wpdb->get_row("SELECT * FROM ". table_agency_casting . " WHERE CastingID = " .$CastingID);



// *************************************************************************************************** //
// Prepare Page
	// add scripts
	if(!is_admin()){
		wp_deregister_script('jquery');
	}
	wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js');
	wp_enqueue_script('jquery_latest');
	wp_enqueue_script( 'casting',  RBAGENCY_casting_BASEDIR . 'js/casting.js');


	echo "<div id=\"primary\" class=\"".(isset($column_class)?$column_class:0)." column rb-agency-interact rb-agency-interact-register\">\n";
	echo "  <div id=\"content\">\n";


		// ****************************************************************************************** //
		// Already logged in

	if (!empty( $error)) {
		echo "<p class=\"error\">". $error ."</p>\n";
	}
	echo "  <header class=\"entry-header\">";
	echo "  </header>";
	echo "  <div id=\"client-register\" class=\"rbform\">";
	echo "	<h3>". __("Account Information", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
	echo "    <form method=\"post\" action=\"". admin_url("admin.php?page=rb_agency_casting_approveclients&action=editRecord&CastingID=".$_GET["CastingID"]) ."\">\n";

	echo "       <div id=\"casting-first-name\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"casting_first_name\">". __("First Name", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_first_name\" type=\"text\" id=\"casting_first_name\" value='".$data_r->CastingContactNameFirst."' /></div>\n";
	echo "       </div><!-- #casting-first-name -->\n";

	echo "       <div id=\"casting-last-name\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"casting_last_name\">". __("Last Name", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_last_name\" type=\"text\" id=\"casting_last_name\" value='".$data_r->CastingContactNameLast."' /></div>\n";
	echo "       </div><!-- #casting_last_name -->\n";

	echo "       <div id=\"casting-email\" class=\"rbfield rbemail rbsingle\">\n";
	echo "   		<label for=\"email\">". __("E-mail (required)", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_email\" type=\"text\" id=\"casting_email\" value='".$data_r->CastingContactEmail."' /></div>\n";
	echo "       </div><!-- #casting-email -->\n";

	echo "       <div id=\"casting-company\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"company\">". __("Company (required)", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_company\" type=\"text\" value='".$data_r->CastingContactCompany."' /></div>\n";
	echo "       </div><!-- #casting-company -->\n";

	echo "       <div id=\"casting-website\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"website\">". __("Website", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_website\" type=\"text\" id=\"casting_email\" value='".$data_r->CastingContactWebsite."' /></div>\n";
	echo "       </div><!-- #casting-website -->\n";

	echo "       <div id=\"casting-street-address\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"street-address\">". __("Street Address", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_address\" type=\"text\" value='".$data_r->CastingLocationStreet."' /></div>\n";
	echo "       </div><!-- #casting-street-address -->\n";

	echo "       <div id=\"casting-city\" class=\"rbfield rbtext rbsingle\">\n";
	echo "   		<label for=\"city\">". __("City", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_city\" type=\"text\" id=\"casting_email\" value='".$data_r->CastingLocationCity."' /></div>\n";
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
						echo '<option value='.$r->CountryID.' '.selected($data_r->CastingLocationCountry,$r->CountryID,false).' >'.$r->CountryTitle.'</option>';
					}
				echo '</select>';
	echo "       </div></div><!-- #casting-country -->\n";

	echo "       <div id=\"casting-state\" class=\"rbfield rbselect rbsingle\">\n";
				echo "		<label>". __("State", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
				echo "		<div>\n";

				if(isset($_POST['CastingCountry']) && !empty($_POST['CastingCountry']) || $data_r->CastingLocationCountry != ""){
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
	echo "   		<label for=\"zip\">". __("Zip", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
	echo "   		<div><input class=\"text-input\" name=\"casting_zip\" type=\"text\" id=\"casting_email\" value='".$data_r->CastingLocationZip."' /></div>\n";
	echo "       </div><!-- #casting-zip -->\n";

		echo "	<h3>". __("Contact Phone", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
		echo "	<div id=\"profile-facebook\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Home", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" name=\"CastingContactPhoneHome\" value=\"". $data_r->CastingContactPhoneHome ."\" />\n";
		echo "	</div></div>\n";
		echo "	<div id=\"profile-twitter\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Cell", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" name=\"CastingContactPhoneCell\" value=\"".$data_r->CastingContactPhoneCell  ."\" />\n";
		echo "	</div></div>\n";
		echo "	<div id=\"profile-youtube\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Work", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" name=\"CastingContactPhoneWork\" value=\"". $data_r->CastingContactPhoneWork  ."\" />\n";
		echo "  </div></div>\n";

		// Show Social Media Links
		echo "	<h3>". __("Social Media Castings", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
		echo "	<div id=\"profile-facebook\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Facebook", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" id=\"CastingContactLinkFacebook\" name=\"CastingContactLinkFacebook\" value=\"".$data_r->CastingContactLinkFacebook ."\" />\n";
		echo "	</div></div>\n";
		echo "	<div id=\"profile-twitter\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Twitter", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" id=\"CastingContactLinkTwitter\" name=\"CastingContactLinkTwitter\" value=\"". $data_r->CastingContactLinkTwitter ."\" />\n";
		echo "	</div></div>\n";
		echo "	<div id=\"profile-youtube\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("YouTube", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" id=\"CastingContactLinkYouTube\" name=\"CastingContactLinkYouTube\" value=\"". $data_r->CastingContactLinkYoutube ."\" />\n";
		echo "  </div></div>\n";
		echo "	<div id=\"profile-flickr\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Flickr", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div><input type=\"text\" class=\"text-input\" id=\"CastingContactLinkFlickr\" name=\"CastingContactLinkFlickr\" value=\"". $data_r->CastingContactLinkFlickr ."\" />\n";
		echo "	</div></div>\n";

		echo "	<h3>". __("Status", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
				echo "	<div id=\"profile-status\" class=\"rbfield rbtext rbsingle\">\n";
				echo "		<label>". __("Status", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
				echo "		<div><select name=\"CastingIsActive\">";
				$status = array('Inactive','Active','Achive','Pending for Approval');
				foreach($status as $k=>$v){
					$selected = $data_r->CastingIsActive == $k ? "selected" : "";
					$textValue = $data_r->CastingIsActive == $k ? $v : $v;
					echo "<option value=\"".$k."\" $selected>".$textValue."</option>";
				}
				
				echo "</select>";
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
			echo "			<small class=\"rbfield-note\">". __("Cannot be changed", RBAGENCY_casting_TEXTDOMAIN) ."</small>";
			echo "		</div>\n";
			echo "  </div>\n";
		}
		echo "	<h3>". __("Login Settings", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
		echo "	<div id=\"rbprofile-password\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Password", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div>";
		echo "			<input type=\"password\" id=\"CastingPassword\" name=\"CastingPassword\" />\n";
		echo "			<small class=\"rbfield-note\">". __("Leave blank to keep same password", RBAGENCY_casting_TEXTDOMAIN) ."</small>";
		echo "		</div>\n";
		echo "	</div>\n";
		echo "	<div id=\"rbprofile-retype-password\" class=\"rbfield rbtext rbsingle\">\n";
		echo "		<label>". __("Retype Password", RBAGENCY_casting_TEXTDOMAIN) ."</label>\n";
		echo "		<div>";
		echo "			<input type=\"password\" id=\"CastingPasswordConfirm\" name=\"CastingPasswordConfirm\" />";
		echo "			<small class=\"rbfield-note\">". __("Retype to Confirm", RBAGENCY_casting_TEXTDOMAIN) ."</small>";
		echo "		</div>\n";
		echo "	</div>\n";

	echo "       <div id=\"casting-submit\" class=\"rbfield rbsubmit rbsingle\">\n";
	echo "   		<input name=\"adduser\" type=\"submit\" id=\"addusersub\" class=\"submit button\" value='". __("Update Information", RBAGENCY_casting_TEXTDOMAIN) ."'/>";

					// if ( current_user_can("create_users") ) { _e("Add User", RBAGENCY_casting_TEXTDOMAIN); } else { _e("Register", RBAGENCY_casting_TEXTDOMAIN); }echo "\" />\n";

					wp_nonce_field("add-user");

	echo "   		<input name=\"action\" type=\"hidden\" id=\"action\" value=\"updatecasting\" />\n";
	echo "   		<input name=\"CastingID\" type=\"hidden\" id=\"action\" value=\"".$_GET["CastingID"]."\" />\n";
	echo "       </div><!-- #casting-submit -->\n";
	// Facebook connect
	?>

<?php
	echo "   </form>\n";
	echo "   </div><!-- .rbform -->\n";


echo "  </div><!-- #content -->\n";
echo "</div><!-- #container -->\n";


  echo "</div>";
}

// *************************************************************************************************** //
// Manage Record
function rb_display_list() {
  global $wpdb;
  $rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_locationtimezone 		= (int)$rb_agency_options_arr['rb_agency_option_locationtimezone'];
  echo "<div class=\"wrap\">\n";
  echo "  <div id=\"rb-overview-icon\" class=\"icon32\"></div>\n";
  echo "  <h2>". __("List", RBAGENCY_casting_TEXTDOMAIN) ." ". LabelPlural ."</h2>\n";

  echo "  <h3 class=\"title\">". __("All Records", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";

		// Sort By
        $sort = "";
        if (isset($_GET['sort']) && !empty($_GET['sort'])){
            $sort = $_GET['sort'];
        }
        else {
            $sort = "client.CastingContactNameFirst";
        }

		// Sort Order
        $dir = "";
        if (isset($_GET['dir']) && !empty($_GET['dir'])){
            $dir = $_GET['dir'];
            if ($dir == "desc" || !isset($dir) || empty($dir)){
               $sortDirection = "asc";
               } else {
               $sortDirection = "desc";
            }
		} else {
				$sortDirection = "desc";
				$dir = "asc";
		}

		// Filter
		$filter = "WHERE 1=1 ";// "WHERE client.CastingIsActive = 3 ";
        if ((isset($_GET['CastingContactNameFirst']) && !empty($_GET['CastingContactNameFirst'])) || isset($_GET['CastingContactNameLast']) && !empty($_GET['CastingContactNameLast'])){
    		if (isset($_GET['CastingContactNameFirst']) && !empty($_GET['CastingContactNameFirst'])){
			$selectedNameFirst = $_GET['CastingContactNameFirst'];
			$query .= "&CastingContactNameFirst=". $selectedNameFirst ."";
			$filter .= " AND client.CastingContactNameFirst LIKE '". $selectedNameFirst ."%'";
			  }
    		if (isset($_GET['CastingContactNameLast']) && !empty($_GET['CastingContactNameLast'])){
			$selectedNameLast = $_GET['CastingContactNameLast'];
			$query .= "&CastingContactNameLast=". $selectedNameLast ."";
			$filter .= " AND client.CastingContactNameLast LIKE '". $selectedNameLast ."%'";
			  }
		}
		if (isset($_GET['CastingLocationCity']) && !empty($_GET['CastingLocationCity'])){
			$selectedCity = $_GET['CastingLocationCity'];
			$query .= "&CastingLocationCity=". $selectedCity ."";
			$filter .= " AND client.CastingLocationCity='". $selectedCity ."'";
		}
		if (isset($_GET['CastingContactEmail']) && !empty($_GET['CastingContactEmail'])){
			$selectedContactEmail = $_GET['CastingContactEmail'];
			$query .= "&CastingContactEmail=". $selectedContactEmail ."";
			$filter .= " AND client.CastingContactEmail LIKE '". $selectedContactEmail ."%'";

		}

		// Bulk Action

		if(isset($_POST['BulkAction_ProfileApproval']) || isset($_POST['BulkAction_ProfileApproval2'])){

			//**** BULK DELETE
			if($_POST['BulkAction_ProfileApproval']=="Delete" || $_POST['BulkAction_ProfileApproval2']=="Delete"){

				if(isset($_POST['castingID'])){
					foreach($_POST['castingID'] as $key){

									$CastingID = $key;
									// Verify Record
									$queryDelete = "SELECT * FROM ". table_agency_casting ." WHERE CastingID =  ". $CastingID;
									$resultsDelete = $wpdb->get_results($queryDelete,ARRAY_A);
									foreach($resultsDelete as $dataDelete) {
										$CastingGallery = $dataDelete['CastingGallery'];

										// Remove Profile
										$delete = "DELETE FROM " . table_agency_casting . " WHERE CastingID = ". $CastingID;
										$results = $wpdb->query($delete);
										//remove the account @ wp-users table.

										wp_delete_user( (int)$dataDelete['CastingUserLinked']);
										//bulk acton

										if (isset($CastingGallery)) {
											// Remove Folder
											$dir = RBAGENCY_UPLOADPATH . $CastingGallery ."/";
											$mydir = opendir($dir);

											if($mydir){
												while(false !== ($file = readdir($mydir))) {
													if($file != "." && $file != ".." && !empty($file)){
														unlink($dir.$file) or DIE("couldn't delete file '$file'<br />");
													}
												}
												closedir($mydir);
											}
											// remove dir
											if(is_dir($dir)) {
												@rmdir($dir) or DIE("couldn't delete $dir$file<br />");
											}
										} else {
											echo __("No valid record found.", RBAGENCY_casting_TEXTDOMAIN);
										}
									echo ('<div id="message" class="updated"><p>'. __("Client deleted successfully!", RBAGENCY_casting_TEXTDOMAIN) .'</p></div>');
									}// is there record?


					}

				}

			}
			// Bulk Approve
			else if($_POST['BulkAction_ProfileApproval']=="Approve" || $_POST['BulkAction_ProfileApproval2']=="Approve"){

					if(isset($_POST['castingID'])){
						$countProfile = 0;
						foreach($_POST['castingID'] as $key){

							$countProfile++;
							$CastingID = $key;
							// Verify Record
							$queryApprove = "UPDATE ". table_agency_casting ." SET CastingIsActive = 1 WHERE CastingID =  ". $CastingID;
							$resultsApprove = $wpdb->query($queryApprove);


						}

						$profileLabel = '';
						$countProfile > 1 ? $profileLabel = "$countProfile Clients" : $profileLabel = "Profile" ;
					echo ('<div id="message" class="updated"><p>'. __("$profileLabel Approved successfully!", RBAGENCY_casting_TEXTDOMAIN) .'</p></div>');


					}

			}
		}

		if(isset($_GET["action"]) && $_GET["action"] =="approveRecord"){
			$CastingID = $_GET["CastingID"];
			

				
			$queryApprove = "UPDATE ". table_agency_casting ." SET CastingIsActive = 1 WHERE CastingID =  %d";
			$resultsApprove = $wpdb->query($wpdb->prepare($queryApprove,$CastingID));

			//get casting userlinked id
			$casting_userLinked = "";
			$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."agency_casting WHERE CastingID = ".$CastingID);
			
			foreach($q as $v){
				$casting_userLinked = $v->CastingUserLinked;
			}
			//notify agent
			wp_new_user_notification_approve($casting_userLinked);

			if(isset($resultsApprove)){
				echo ('<div id="message" class="updated"><p>'. __("".(isset($profileLabel)?$profileLabel:"")." Approved successfully!", RBAGENCY_casting_TEXTDOMAIN) .'</p></div>');
			}
		}

		$wpdb->get_results("SELECT * FROM ". table_agency_casting ." client LEFT JOIN ". table_agency_data_type ." castingtype ON client.CastingType = castingtype.DataTypeID ". $filter  ."",ARRAY_A);

		//Paginate
		$items =$wpdb->num_rows; // number of total rows in the database
		if($items > 0) {
			$p = new RBAgency_Pagination;
			$p->items($items);
			$p->limit(50); // Limit entries per page
			$p->target("admin.php?page=". $_GET['page'] .$query);
			$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
			$p->calculate(); // Calculates what to show
			$p->parameterName('paging');
			$p->adjacents(1); //No. of page away from the current page

			if(!isset($_GET['paging'])) {
				$p->page = 1;
			} else {
				$p->page = $_GET['paging'];
			}

			//Query for limit paging
			$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
		} else {
			$limit = "";
		}

		if($items >= 50) {
        echo "<div class=\"tablenav\">\n";
 		echo "  <div class=\"tablenav-pages\">\n";
		echo $p->show();// Echo out the list of paging.
		echo "  </div>\n";
        echo "</div>\n";
		}
		echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
		echo "  <thead>\n";
		echo "    <tr>\n";
		echo "        <td style=\"width: 90%;\" nowrap=\"nowrap\">    \n";



		echo "    		<form method=\"GET\" action=\"". admin_url("admin.php?page=". $_GET['page']) ."\">\n";
		echo "    			<input type=\"hidden\" name=\"page_index\" id=\"page_index\" value=\"". (isset($_GET['page_index']) && !empty($_GET['page_index'])?$_GET['page_index']:"" )."\" />  \n";
		echo "    			<input type=\"hidden\" name=\"page\" id=\"page\" value=\"". $_GET['page'] ."\" />\n";
		echo "    			<input type=\"hidden\" name=\"type\" value=\"name\" />\n";
		echo "    			". __("Search By", RBAGENCY_casting_TEXTDOMAIN) .": \n";
		echo "    			". __("First Name", RBAGENCY_casting_TEXTDOMAIN) .": <input type=\"text\" name=\"CastingContactNameFirst\" value=\"". (isset($selectedNameFirst) && !empty($selectedNameFirst) ?$selectedNameFirst:"") ."\" style=\"width: 100px;\" />\n";
		echo "    			". __("Last Name", RBAGENCY_casting_TEXTDOMAIN) .": <input type=\"text\" name=\"CastingContactNameLast\" value=\"". (isset($selectedNameLast) && !empty($selectedNameLast)?$selectedNameLast:"" )."\" style=\"width: 100px;\" />\n";
		echo "    			". __("Location", RBAGENCY_casting_TEXTDOMAIN) .": \n";
		echo "    			<select name=\"CastingLocationCity\">\n";
		echo "					<option value=\"\">". __("Any Location", RBAGENCY_casting_TEXTDOMAIN) ."</option>";
								$query = "SELECT DISTINCT CastingLocationCity, CastingLocationState FROM ". table_agency_casting ." ORDER BY CastingLocationState, CastingLocationCity ASC";
								$results = $wpdb->get_results($query,ARRAY_A);
								$count = $wpdb->num_rows;
								foreach ($results as $data) {
									if (isset($data['CastingLocationCity']) && !empty($data['CastingLocationCity'])) {
									echo "<option value=\"". $data['CastingLocationCity'] ."\" ". selected(isset($selectedCity)?$selectedCity:"", $data["CastingLocationCity"]) ."\">". $data['CastingLocationCity'] .", ". strtoupper($data["CastingLocationState"]) ."</option>\n";
									}
								}
		echo "    			</select>\n";
		echo "    			<input type=\"submit\" value=\"". __("Filter", RBAGENCY_casting_TEXTDOMAIN) ."\" class=\"button-primary\" />\n";
		echo "          </form>\n";
		echo "        </td>\n";
		echo "        <td style=\"width: 10%;\" nowrap=\"nowrap\">\n";
		echo "    		<form method=\"GET\" action=\"". admin_url("admin.php?page=". $_GET['page']) ."\">\n";
		echo "    			<input type=\"hidden\" name=\"page_index\" id=\"page_index\" value=\"". (isset($_GET['page_index'])?$_GET['page_index']:"") ."\" />  \n";
		echo "    			<input type=\"hidden\" name=\"page\" id=\"page\" value=\"". $_GET['page'] ."\" />\n";
		echo "    			<input type=\"submit\" value=\"". __("Clear Filters", RBAGENCY_casting_TEXTDOMAIN) ."\" class=\"button-secondary\" />\n";
		echo "    		</form>\n";
		echo "        </td>\n";
		echo "        <td>&nbsp;</td>\n";

		echo "    </tr>\n";
		echo "  </thead>\n";
		echo "</table>\n";

		echo "<form method=\"post\" action=\"". admin_url("admin.php?page=". $_GET['page']) ."\" id=\"formMainBulk\">\n";
		echo "    			<select name=\"BulkAction_ProfileApproval\">\n";
		echo "              <option value=\"\"> ". __("Bulk Action", RBAGENCY_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              <option value=\"Approve\"> ". __("Approve", RBAGENCY_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              <option value=\"Delete\"> ". __("Delete", RBAGENCY_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              </select>";
		echo "    <input type=\"submit\" value=\"". __("Apply", RBAGENCY_casting_TEXTDOMAIN) ."\" name=\"ProfileBulkAction\" class=\"button-secondary\"  />\n";
		echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
		echo " <thead>\n";
		echo "    <tr class=\"thead\">\n";
		echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
		echo "        <th class=\"column-ProfileID\" id=\"ProfileID\" scope=\"col\" style=\"width:50px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=ProfileID&dir=". $sortDirection) ."\">". __("ID", RBAGENCY_casting_TEXTDOMAIN) ."</a></th>\n";
		echo "        <th class=\"column-CastingContactNameFirst\" id=\"CastingContactNameFirst\" scope=\"col\" style=\"width:130px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingContactNameFirst&dir=". $sortDirection) ."\">". __("First Name", RBAGENCY_casting_TEXTDOMAIN) ."</a></th>\n";
		echo "        <th class=\"column-CastingContactNameLast\" id=\"CastingContactNameLast\" scope=\"col\" style=\"width:130px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingContactNameLast&dir=". $sortDirection) ."\">". __("Last Name", RBAGENCY_casting_TEXTDOMAIN) ."</a></th>\n";
		echo "        <th class=\"column-CastingContactEmail\" id=\"CastingContactEmail\" scope=\"col\" style=\"width:165px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingContactEmail&dir=". $sortDirection) ."\">". __("Email Address", RBAGENCY_casting_TEXTDOMAIN) ."</a></th>\n";
		//echo "        <th class=\"column-ProfilesProfileDate\" id=\"ProfilesProfileDate\" scope=\"col\" style=\"width:50px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=ProfileDateBirth&dir=". $sortDirection) ."\">". __("Age", RBAGENCY_casting_TEXTDOMAIN) ."</a></th>\n";
		echo "        <th class=\"column-CastingLocationCity\" id=\"CastingLocationCity\" scope=\"col\" style=\"width:100px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingLocationCity&dir=". $sortDirection) ."\">". __("City", RBAGENCY_casting_TEXTDOMAIN) ."</a></th>\n";
		echo "        <th class=\"column-CastingLocationState\" id=\"CastingLocationState\" scope=\"col\" style=\"width:50px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingLocationState&dir=". $sortDirection) ."\">". __("State", RBAGENCY_casting_TEXTDOMAIN) ."</a></th>\n";
		echo "        <th class=\"column-ProfileDateViewLast\" id=\"ProfileDateViewLast\" scope=\"col\">Date Created</th>\n";
		echo "    </tr>\n";
		echo " </thead>\n";
		echo " <tfoot>\n";
		echo "    <tr class=\"thead\">\n";
		echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
		echo "        <th class=\"column\" scope=\"col\">". __("ID", RBAGENCY_casting_TEXTDOMAIN) ."</th>\n";
		echo "        <th class=\"column\" scope=\"col\">". __("First Name", RBAGENCY_casting_TEXTDOMAIN) ."</th>\n";
		echo "        <th class=\"column\" scope=\"col\">". __("Last Name", RBAGENCY_casting_TEXTDOMAIN) ."</th>\n";
		echo "        <th class=\"column\" scope=\"col\">". __("Email Address", RBAGENCY_casting_TEXTDOMAIN) ."</th>\n";
		//echo "        <th class=\"column\" scope=\"col\">". __("Age</th>\n";
		echo "        <th class=\"column\" scope=\"col\">". __("City", RBAGENCY_casting_TEXTDOMAIN) ."</th>\n";
		echo "        <th class=\"column\" scope=\"col\">". __("State", RBAGENCY_casting_TEXTDOMAIN) ."</th>\n";
		echo "        <th class=\"column\" scope=\"col\">". __("Date Created", RBAGENCY_casting_TEXTDOMAIN) ."</th>\n";
		echo "    </tr>\n";
		echo " </tfoot>\n";
		echo " <tbody>\n";
        $query = "SELECT * FROM ". table_agency_casting ." client LEFT JOIN ". table_agency_data_type ." castingtype ON client.CastingType = castingtype.DataTypeID ". $filter  ." ORDER BY $sort $limit";
        $results2 =  $wpdb->get_results($query,ARRAY_A);
        $count_clients =  $wpdb->num_rows; 
        foreach($results2 as $data) {

            $CastingID = $data['CastingID'];
            $CastingGallery = stripslashes($data['CastingGallery']);
            $CastingContactNameFirst = stripslashes($data['CastingContactNameFirst']);
            $CastingContactNameLast = stripslashes($data['CastingContactNameLast']);
            $CastingLocationCity = RBAgency_Common::format_propercase(stripslashes($data['CastingLocationCity']));
            $CastingLocationState = stripslashes($data['CastingLocationState']);
            $CastingContactEmail = stripslashes($data['CastingContactEmail']);
           // $CastingDateBirth = stripslashes($data['CastingDateBirth']);
            $CastingStatHits = stripslashes($data['CastingStatHits']);
            $CastingDateCreated = stripslashes($data['CastingDateCreated']);
            $CastingIsActive = stripslashes($data["CastingIsActive"]);

			$DataTypeTitle = stripslashes($data['CastingType']);

			if(strpos($data['CastingType'], ",") > 0){
            $title = explode(",",$data['CastingType']);
            $new_title = "";
            foreach($title as $t){
                $id = (int)$t;
                $get_title = "SELECT DataTypeTitle FROM " . table_agency_data_type .
                             " WHERE DataTypeID = " . $id;
                $resource = $wpdb->get_row($get_title,ARRAY_A);
                $get = $resource;
                $count = $wpdb->num_rows;
                if ($count > 0 ){
                    $new_title .= "," . $get['DataTypeTitle'];
                }
            }
            $new_title = substr($new_title,1);
        } else {
                $new_title = "";
                $id = (int)$data['CastingType'];
                $get_title = "SELECT DataTypeTitle FROM " . table_agency_data_type .
                             " WHERE DataTypeID = " . $id;
                $resource = $wpdb->get_row($get_title,ARRAY_A);
                $get = $resource;
                $count = $wpdb->num_rows;
                if ($count > 0 ){
                    $new_title = $get['DataTypeTitle'];
                }
        }

	        if($CastingIsActive == 3){
	        	$rowColor = "";
		        $DataTypeTitle = stripslashes($new_title);
				if(!empty($CastingIsActive) && $CastingIsActive == 3){
					$rowColor = "style=\"background:#00a0d2;\"";
				}
				echo "    <tr ". (isset($rowColor)?$rowColor:"") ." data-isactive=\"".$CastingIsActive."\">\n";
				echo "        <th class=\"check-column\" scope=\"row\">\n";
				echo "          <input type=\"checkbox\" value=\"". $CastingID ."\" class=\"administrator\" id=\"". $CastingID ."\" name=\"castingID[". $CastingID ."]\"/>\n";
				echo "        </th>\n";
				echo "        <td class=\"ProfileID column-ProfileID\">". $CastingID ."</td>\n";
				echo "        <td class=\"CastingContactNameFirst column-CastingContactNameFirst\">\n";
				echo "          ". $CastingContactNameFirst ."\n";
				echo "          <div class=\"row-actions\">\n";
				if( $CastingIsActive == 3){
				echo "            <span class=\"allow\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&amp;action=approveRecord&amp;CastingID=". $CastingID) ."\" title=\"". __("Approve this Record", RBAGENCY_casting_TEXTDOMAIN) . "\">". __("Approve", RBAGENCY_casting_TEXTDOMAIN) . "</a> | </span>\n";
				}
				echo "            <span class=\"edit\"><a href=\"". admin_url("admin.php?page=rb_agency_casting_approveclients&amp;action=editRecord&amp;CastingID=". $CastingID) ."\" title=\"". __("Edit this Record", RBAGENCY_casting_TEXTDOMAIN) . "\">". __("Edit", RBAGENCY_casting_TEXTDOMAIN) . "</a> | </span>\n";
				echo "            <span class=\"view\"><a href=\"/profile-casting/".  $CastingGallery ."/\" title=\"". __("View", RBAGENCY_casting_TEXTDOMAIN) . "\" target=\"_blank\">". __("View", RBAGENCY_casting_TEXTDOMAIN) . "</a> | </span>\n";
				echo "            <span class=\"delete\"><a class=\"submitdelete\" href=\"". admin_url("admin.php?page=". $_GET['page']) ."&amp;action=deleteRecord&amp;CastingID=". $CastingID ."\"  onclick=\"if ( confirm('". __("You are about to delete the profile for ", RBAGENCY_casting_TEXTDOMAIN) ." ". $CastingContactNameFirst ." ". $CastingContactNameLast ."\'". __("Cancel", RBAGENCY_casting_TEXTDOMAIN) . "\' ". __("to stop", RBAGENCY_casting_TEXTDOMAIN) . ", \'". __("OK", RBAGENCY_casting_TEXTDOMAIN) . "\' ". __("to delete", RBAGENCY_casting_TEXTDOMAIN) . ".') ) {return true;}return false;\" title=\"". __("Delete this Record", RBAGENCY_casting_TEXTDOMAIN) . "\">". __("Delete", RBAGENCY_casting_TEXTDOMAIN) . "</a> </span>\n";
				echo "          </div>\n";
				echo "        </td>\n";
				echo "        <td class=\"CastingContactNameLast column-CastingContactNameLast\">". $CastingContactNameLast ."</td>\n";
				echo "        <td class=\"CastingContactEmail column-CastingContactEmail\">". $CastingContactEmail ."</td>\n";
				//echo "        <td class=\"ProfilesProfileDate column-ProfilesProfileDate\">". rb_agency_get_age($ProfileDateBirth) ."</td>\n";
				echo "        <td class=\"CastingLocationCity column-CastingLocationCity\">". $CastingLocationCity ."</td>\n";
				echo "        <td class=\"CastingLocationCity column-CastingLocationState\">". rb_agency_getStateTitle($CastingLocationState) ."</td>\n";
				echo "        <td class=\"ProfileDateViewLast column-ProfileDateViewLast\">\n";
				echo "           ". rb_agency_makeago(rb_agency_convertdatetime($CastingDateCreated), $rb_agency_option_locationtimezone);
				echo "        </td>\n";
				echo "    </tr>\n";
	        }




        }

            if ($count_clients < 1) {
				if (isset($filter)) {
		echo "    <tr>\n";
		echo "        <th class=\"check-column\" scope=\"row\"></th>\n";
		echo "        <td class=\"name column-name\" colspan=\"5\">\n";
		echo "           <p>". __("No profiles found with this criteria.", RBAGENCY_casting_TEXTDOMAIN) ."</p>\n";
		echo "        </td>\n";
		echo "    </tr>\n";
				} else {
		echo "    <tr>\n";
		echo "        <th class=\"check-column\" scope=\"row\"></th>\n";
		echo "        <td class=\"name column-name\" colspan=\"5\">\n";
		echo "            <p>". __("There aren't any profiles loaded yet!", RBAGENCY_casting_TEXTDOMAIN) ."</p>\n";
		echo "        </td>\n";
		echo "    </tr>\n";
				}
        }
		echo " </tbody>\n";
		echo "</table>\n";

		echo "    			<select name=\"BulkAction_ProfileApproval2\">\n";
		echo "              <option value=\"\"> ". __("Bulk Action", RBAGENCY_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              <option value=\"Approve\"> ". __("Approve", RBAGENCY_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              <option value=\"Delete\"> ". __("Delete", RBAGENCY_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              </select>";
		echo "    <input type=\"submit\" value=\"". __("Apply", RBAGENCY_casting_TEXTDOMAIN) ."\" name=\"ProfileBulkAction\" class=\"button-secondary\"  />\n";

		echo "<div class=\"tablenav\">\n";
		echo "  <div class='tablenav-pages'>\n";
			if($items > 0) {
				echo $p->show();// Echo out the list of paging.
			}
		echo "  </div>\n";
		echo "</div>\n";

		echo "<p class=\"submit\">\n";
		//echo "  <input type=\"hidden\" value=\"deleteRecord\" name=\"action\" />\n";
		//echo "  <input type=\"submit\" value=\"". __('Delete') ."\" class=\"button-primary\" name=\"submit\" />	\n";
		echo "</p>\n";


		echo "</form>\n";
}

/*



$testlist = "SELECT * FROM " . table_agency_casting ;
$resultstest = $wpdb->get_results($testlist,ARRAY_A);

print_r($resultstest); */
?>
