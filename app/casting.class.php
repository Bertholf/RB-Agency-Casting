<?php

class RBAgency_Casting {

	/*
	 * Casting Cart
	 * Process Actions
	 */

		public static function cart_process(){

			/*
			 * Setup Requirements
			 */

			// Protect and defend the cart string!
				$cartString = "";
				$action = $_GET["action"];
				$actiontwo = $_GET["actiontwo"];


				if ($action == "cartAdd" && !isset($_GET["actiontwo"])) {
					// Add to Cart
					$response = self::cart_process_add();

				} elseif ($action == "formEmpty") {
					// Empty the Form
					extract($_SESSION);
					foreach($_SESSION as $key=>$value) {
						if (substr($key, 0, 7) == "Profile") {
							unset($_SESSION[$key]);
						}
					}

				} elseif ($action == "cartEmpty") {

					// Throw the baby out with the bathwater
					unset($_SESSION['cartArray']);

				} elseif ($action == "cartAdd" && $actiontwo ="cartRemove") {
					// Remove ID from Cart
					$id = $_GET["RemoveID"];
					$response = self::cart_process_remove($id);

				} elseif ($action == "searchSave") {
					// Save the Search
					if (isset($_SESSION['cartArray'])) {

						extract($_SESSION);
						foreach($_SESSION as $key=>$value) {
							// TODO: Why is this empty?
						}
						$_SESSION['cartArray'] = $cartArray;

					}

				}

				return true;
		}



	/*
	 * Casting Cart - Add to Cart
	 * @return str $cartString
	 */

		public static function cart_process_add(){

			// Get String
			if(count($_GET["ProfileID"]) > 0) {
				foreach($_GET["ProfileID"] as $value) {
					$cartString .= $value .",";
				}
			}

			// Clean It!
			$cartString = RBAgency_Common::clean_string($cartString);

			// Add to Session
			if (isset($_SESSION['cartArray'])) {
				$cartArray = $_SESSION['cartArray'];
				array_push($cartArray, $cartString);
			} else {
				$cartArray = array($cartString);
			}

			// Replace Session
			$_SESSION['cartArray'] = $cartArray;

			return $cartString;

		}


	/*
	 * Casting Cart - Remove from Cart
	 * @return str $cartString
	 */

		public static function cart_process_remove($id){

			// Remove ID from Cart
			if (isset($id)) {
				$cartArray = $_SESSION['cartArray'];
				$cartString = implode(",", $cartArray);

				// TODO: FIX  this, if ID = 3, changes 38 to 8
				$cartString = str_replace($id ."", "", $cartString);
				$cartString = RBAgency_Common::clean_string($cartString);

				// Put it back in the array, and wash your hands
				$_SESSION['cartArray'] = array($cartString);
			}

			// echo "TEST";
		}



	/*
	 * Show Casting Cart
	 */

		public static function cart_show(){
			global $wpdb;

			if (isset($_SESSION['cartArray']) && !empty($_SESSION['cartArray'])) {

				$cartArray = $_SESSION['cartArray'];
					$cartString = implode(",", array_unique($cartArray));
					$cartString = RBAgency_Common::clean_string($cartString);

				// Show Cart  
				$query = "SELECT  profile.*,media.* FROM ". table_agency_profile ." profile, ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1 AND profile.ProfileID IN (". $cartString .") ORDER BY profile.ProfileContactNameFirst ASC";
				$results = $wpdb->get_results($query,ARRAY_A) or  die( "<a href=\"?page=". $_GET['page'] ."&action=cartEmpty\" class=\"button-secondary\">". __("No profile selected. Try again", RBAGENCY_casting_TEXTDOMAIN) ."</a>"); //die ( __("Error, query failed", RBAGENCY_casting_TEXTDOMAIN ));
				$count = $wpdb->num_rows;
				echo "<div class=\"boxblock-container\" style=\"float: left; padding-top:24px; width: 49%; min-width: 500px;\">\n";
				echo "<div style=\"float: right; width: 100px; \"><a href=\"?page=". $_GET['page'] ."&action=cartEmpty\" class=\"button-secondary\">". __("Empty Cart", RBAGENCY_casting_TEXTDOMAIN) ."</a></div>";
				echo "<div style=\"float: left; line-height: 22px; font-family:Georgia; font-size:13px; font-style: italic; color: #777777; \">". __("Currently", RBAGENCY_casting_TEXTDOMAIN) ." <strong>". $count ."</strong> ". __("in Cart", RBAGENCY_casting_TEXTDOMAIN) ."</div>";
				echo "<div style=\"clear: both; border-top: 2px solid #c0c0c0; \" class=\"profile\">";

				if ($count == 1) {
					$cartAction = "cartEmpty";
				} elseif ($count < 1) {
					echo "". __("There are currently no profiles in the casting cart", RBAGENCY_casting_TEXTDOMAIN) .".";
					$cartAction = "cartEmpty";
				} else {
					$cartAction = "cartRemove";
				}

				foreach ($results as $data) {

					$ProfileDateUpdated = $data['ProfileDateUpdated'];
					echo "  <div style=\"position: relative; border: 1px solid #e1e1e1; line-height: 22px; float: left; padding: 10px; width: 210px; margin: 6px; \">";
					echo "    <div style=\"text-align: center; \"><h3>". stripslashes($data['ProfileContactNameFirst']) ." ". stripslashes($data['ProfileContactNameLast']) . "</h3></div>"; 
					echo "    <div style=\"float: left; width: 100px; height: 100px; overflow: hidden; margin-top: 2px; \"><img style=\"width: 100px; \" src=\"". RBAGENCY_UPLOADDIR ."". $data['ProfileGallery'] ."/". $data['ProfileMediaURL'] ."\" /></div>\n";
					echo "    <div style=\"float: left; width: 100px; height: 100px; overflow: scroll-y; margin-left: 10px; line-height: 11px; font-size: 9px; \">\n";

					if (!empty($data['ProfileDateBirth'])) {
						echo "<strong>".__("Age:",RBAGENCY_casting_TEXTDOMAIN)."</strong> ". rb_agency_get_age($data['ProfileDateBirth']) ."<br />\n";
					}
					// TODO: ADD MORE FIELDS

					echo "    </div>";
					echo "    <div style=\"position: absolute; z-index: 20; top: 120px; left: 200px; width: 20px; height: 20px; overflow: hidden; \"><a href=\"?page=". $_GET['page'] ."&actiontwo=cartRemove&action=cartAdd&RemoveID=". $data['ProfileID'] ."&\" title=\"". __("Remove from Cart", RBAGENCY_casting_TEXTDOMAIN) ."\"><img src=\"". RBAGENCY_PLUGIN_URL ."style/remove.png\" style=\"width: 20px; \" alt=\"". __("Remove from Cart", RBAGENCY_casting_TEXTDOMAIN) ."\" /></a></div>";
					echo "    <div style=\"clear: both; \"></div>";
					echo "  </div>";
				}

				echo "  <div style=\"clear: both;\"></div>\n";
				echo "</div>";

				if (($cartAction == "cartEmpty") || ($cartAction == "cartRemove")) {
				echo "<a name=\"compose\">&nbsp;</a>"; 
				echo "<div class=\"boxblock\">\n";
				echo "   <h3>". __("Cart Actions", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
				echo "   <div class=\"inner\">\n";
				echo "      <a href=\"?page=rb_agency_searchsaved&action=searchSave\" title=\"". __("Save Search & Email", RBAGENCY_casting_TEXTDOMAIN) ."\" class=\"button-primary\">". __("Save Search & Email", RBAGENCY_casting_TEXTDOMAIN) ."</a>\n";
				echo "      <a href=\"?page=rb_agency_search&action=massEmail#compose\" title=\"". __("Mass Email", RBAGENCY_casting_TEXTDOMAIN) ."\" class=\"button-primary\">". __("Mass Email", RBAGENCY_casting_TEXTDOMAIN) ."</a>\n";
				echo "      <a href=\"#\" onClick=\"window.open('". get_bloginfo("url") ."/profile-print/?action=castingCart&cD=1','mywindow','width=930,height=600,left=0,top=50,screenX=0,screenY=50,scrollbars=yes')\" title=\"Quick Print\" class=\"button-primary\">". __("Quick Print", RBAGENCY_casting_TEXTDOMAIN) ."</a>\n";
				echo "      <a href=\"#\" onClick=\"window.open('". get_bloginfo("url") ."/profile-print/?action=castingCart&cD=0','mywindow','width=930,height=600,left=0,top=50,screenX=0,screenY=50,scrollbars=yes')\" title=\"Quick Print - Without Details\" class=\"button-primary\">". __("Quick Print", RBAGENCY_casting_TEXTDOMAIN) ." - ". __("Without Details", RBAGENCY_casting_TEXTDOMAIN) ."</a>\n";
				echo "   </div>\n";
				echo "</div>\n";
				}// Is Cart Empty 

			} else {

				echo "<p>There are no profiles added to the casting cart.</p>\n";

			}


		}


	/*
	 * Casting Cart - Send Email Process
	 */

		public static function cart_send_process(){

			$isSent = false;
			$rb_agency_options_arr = get_option('rb_agency_options');
			$rb_agency_value_agencyname = $rb_agency_options_arr['rb_agency_option_agencyname'];
			$rb_agency_value_agencyemail = $rb_agency_options_arr['rb_agency_option_agencyemail'];


			$MassEmailSubject = $_POST["MassEmailSubject"];
			$MassEmailMessage = $_POST["MassEmailMessage"];
			$MassEmailRecipient = $_POST["MassEmailRecipient"];
			$MassEmailBccEmail = $_POST["MassEmailBccEmail"];

			$SearchID				= time(U);
			$SearchMuxHash			= RBAgency_Common::generate_random_string(8);

			$SearchMuxToName		=$_POST["MassEmailRecipient"];
			$SearchMuxToEmail		=$_POST["MassEmailRecipient"];

			$SearchMuxEmailToBcc	=$_POST['MassEmailBccEmail'];
			$SearchMuxSubject		= $_POST['MassEmailSubject'];
			$SearchMuxMessage		=$_POST['MassEmailMessage'];
			$SearchMuxCustomValue	='';
			$cartArray = $_SESSION['cartArray'];

			$cartString = implode(",", array_unique($cartArray));
			$cartString = rb_agency_cleanString($cartString);

			global $wpdb;
		$wpdb->query("INSERT INTO " . table_agency_searchsaved." (SearchProfileID,SearchTitle) VALUES('".$cartString."','".$SearchMuxSubject."')");

		$lastid = $wpdb->insert_id;

		// Create Record
		$insert = "INSERT INTO " . table_agency_searchsaved_mux ." 
				(
				SearchID,
				SearchMuxHash,
				SearchMuxToName,
				SearchMuxToEmail,
				SearchMuxSubject,
				SearchMuxMessage,
				SearchMuxCustomValue
				)" .
				"VALUES
				(
				'" . $wpdb->escape($lastid) . "',
				'" . $wpdb->escape($SearchMuxHash) . "',
				'" . $wpdb->escape($SearchMuxToName) . "',
				'" . $wpdb->escape($SearchMuxToEmail) . "',
				'" . $wpdb->escape($SearchMuxSubject) . "',
				'" . $wpdb->escape($SearchMuxMessage) . "',
				'" . $wpdb->escape($SearchMuxCustomValue) ."'
				)";
			$results = $wpdb->query($insert);

			$profileimage = "";
			$profileimage .='<p><div style="width:550px;min-height: 170px;">';
			$query = "SELECT search.SearchTitle, search.SearchProfileID, search.SearchOptions, searchsent.SearchMuxHash FROM ". table_agency_searchsaved ." search LEFT JOIN ". table_agency_searchsaved_mux ." searchsent ON search.SearchID = searchsent.SearchID WHERE search.SearchID = \"". $lastid ."\"";
			$qProfiles =  $wpdb->get_row($query,ARRAY_A);
			$data = $qProfiles;
			$query = "SELECT * FROM ". table_agency_profile ." profile, ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1 AND profile.ProfileID IN (".$data['SearchProfileID'].") ORDER BY ProfileContactNameFirst ASC";
			$results = $wpdb->get_results($query,ARRAY_A);
			$count = $wpdb->num_rows;
			foreach($results as $data2) {
				$profileimage .= " <div style=\"background:black; color:white;float: left; max-width: 100px; height: 150px; margin: 2px; overflow:hidden;\">";
				$profileimage .= " <div style=\"margin:3px;max-width:250px; max-height:300px; overflow:hidden;\">";
				$profileimage .= stripslashes($data2['ProfileContactNameFirst']) ." ". stripslashes($data2['ProfileContactNameLast']);
				$profileimage .= "<br /><a href=\"". RBAGENCY_PROFILEDIR . $data2['ProfileGallery'] ."/\" target=\"_blank\">";
				$profileimage .= "<img style=\"max-width:130px; max-height:150px; \" src=\"".RBAGENCY_UPLOADDIR ."". $data2['ProfileGallery'] ."/". $data2['ProfileMediaURL'] ."\" /></a>";
				$profileimage .= "</div>\n";
				$profileimage .= "</div>\n";
			}
			$profileimage .="</div></p>";

			// Mail it
			$headers[]  = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=iso-8859-1';
			$headers[] = 'From: '.$rb_agency_value_agencyname.' <'. $rb_agency_value_agencyemail .'>';

			/*if(!empty($expMail)){
				$expMail = explode(",",$MassEmailRecipient);
				foreach($expMail as $bccEmail){
						$headers[] = 'Bcc: '.$bccEmail;
				}
			}*/

			//For Bcc emails
			if(!empty($MassEmailBccEmail)){
				$bccMail = explode(",",$MassEmailBccEmail);
				foreach($bccMail as $bcc){
						$headers[] = 'Bcc: '.$bcc;
				}
			}
			$MassEmailMessage = str_replace("[link-place-holder]",site_url()."/client-view/".$SearchMuxHash."<br/><br/>".$profileimage ."<br/><br/>",$MassEmailMessage);
			$MassEmailMessage	= str_ireplace("[site-url]",get_bloginfo("url"),$MassEmailMessage);
			$MassEmailMessage	= str_ireplace("[site-title]",get_bloginfo("name"),$MassEmailMessage);
			$isSent = wp_mail($MassEmailRecipient, $MassEmailSubject, $MassEmailMessage, $headers);
			$url = admin_url('admin.php?page=rb_agency_searchsaved&m=1');
			if($isSent){ ?>
			<script type="text/javascript"> 
				window.location="<?php echo $url;?>";
			</script>
			<?php 
			}
			return $isSent;

		}

	/*
	 * Form to Send Casting Cart
	 */

		public static function cart_send_form(){

		global $wpdb;
		/*if(isset($_POST["SendEmail"])){
				// Process Form
				$isSent = RBAgency_Casting::cart_send_process();

			}*/

			if($_GET["action"] == "massEmail"){
				echo RBAgency_Casting::cart_show();
				// Filter Models Already in Cart
				if (isset($_SESSION['cartArray'])) {
					$cartArray = $_SESSION['cartArray'];
					$cartString = implode(",", $cartArray);
					$cartQuery =  " AND profile.ProfileID IN (". $cartString .")";
				}
				$rb_agency_options_arr = get_option('rb_agency_options');
				$rb_agency_value_agencyname = $rb_agency_options_arr['rb_agency_option_agencyname'];
				$rb_agency_value_agencyemail = $rb_agency_options_arr['rb_agency_option_agencyemail'];
				// Search Results
				$query = "SELECT profile.*  FROM ". table_agency_profile ." profile WHERE profile.ProfileID > 0 ".$cartQuery;
				$results2 = $wpdb->get_results($query,ARRAY_A);
				$count = $wpdb->num_rows;
				$pos = 0;
				$recipient = "";
				foreach ($results2 as $data) {
					$pos ++;
					$ProfileID = $data['ProfileID'];
					$recipient .=$data['ProfileContactEmail'];
					if($count != $pos){
						$recipient .= ",";
					}

				}

				// Email
				//echo "Email starts";
				echo "<form method=\"post\">";
				echo "     <div class=\"boxblock\">\n";
				echo "        <h3>". __("Compose Email", RBAGENCY_casting_TEXTDOMAIN) ."</h3>\n";
				echo "        <div class=\"inner\">\n";
				/*if($msg!=""){
				echo "          <div id=\"message\" class=\"updated\"><p>Email Messages successfully sent!</p></div>";
				}*/
				echo "          <strong>".__("Recipient:",RBAGENCY_casting_TEXTDOMAIN)."</strong><br/><textarea name=\"MassEmailRecipient\" style=\"width:100%;\">".$rb_agency_value_agencyemail."</textarea><br/>";
				echo "          <strong>".__("Bcc:",RBAGENCY_casting_TEXTDOMAIN)."</strong><br/><textarea name=\"MassEmailBccEmail\" style=\"width:100%;\">".$recipient."</textarea><br/>";
				echo "          <strong>".__("Subject:",RBAGENCY_casting_TEXTDOMAIN)."</strong> <br/><input type=\"text\" name=\"MassEmailSubject\" style=\"width:100%\"/>";
				echo "          <br/>";
			/*	echo "          <strong>Message:</strong><br/>     <textarea name=\"MassEmailMessage\"  style=\"width:100%;height:300px;\">this message was sent to you by ".$rb_agency_value_agencyname." ".network_site_url( '/' )."</textarea>";*/
				//Adding Wp editor
				$content = "Add Message Here<br />Click the following link (or copy and paste it into your browser):<br />
[link-place-holder]<br /><br />".__("This message was sent to you by:",RBAGENCY_casting_TEXTDOMAIN)."<br />[site-title]<br />[site-url]";
				$editor_id = 'MassEmailMessage';
				wp_editor( $content, $editor_id,array("wpautop"=>false,"tinymce"=>true) );

				echo "          <input type=\"submit\" value=\"". __("Send Email", RBAGENCY_casting_TEXTDOMAIN) . "\" name=\"SendEmail\"class=\"button-primary\" />\n";
				echo "        </div>\n";
				echo "     </div>\n";
				echo "</form>";
				echo "     </div>\n";
			}

			return true;

		}


		/*
		 * check if user is a casting agent
		 */
		public static function rb_is_user_casting(){

				global $wpdb;
				global $current_user;

				if(is_user_logged_in()){
					get_currentuserinfo();
					$result = $wpdb->get_results("SELECT CastingContactNameFirst FROM " . table_agency_casting . " WHERE CastingUserLinked = " . $current_user->ID ); 
					if(count($result) > 0){
						// We have a match!
						return true;
					} else {
						// Not linked to casting agent
						return false;
					}
				} else {
					// Not logged in
					return false;
				}

				return false;
		}


		/*
		 * get job type name thru id
		 */
		public static function rb_get_job_type_name($id=NULL){

			global $wpdb;

			if($id==NULL || $id=="" || empty($id)) return "";
			$job_type = $wpdb->get_row("SELECT * FROM " . table_agency_casting_job_type . " WHERE Job_Type_ID = " . $id,ARRAY_A);
			$type_name = "";
			$count = $wpdb->num_rows;
			if($count > 0){
				$t = $job_type;
				$type_name = $t['Job_Type_Title'];
			}

			return $type_name;
		}


		/*
		 * expan string criteria to readable format
		 */
		public static function rb_get_job_criteria( $criteria = NULL , $return_array = false){

			global $wbdp;

			if($criteria==NULL || $criteria=="" || empty($criteria)) return "No Criteria";

			$details = "";
			$return_arr = array();

			// if list of criteria
			if(preg_match("/\|/",$criteria)){
				$expand_arr = explode("|",$criteria);

				foreach($expand_arr as $arr){

					if(preg_match("/\//",$arr)){
						if($return_array){
							$d = explode("/",$arr);
							$return_arr[$d[0]] = $d[1];
						} else {
							$d = explode("/",$arr);
							$values = str_replace("-",", ",$d[1]);
							$details .= self::rb_get_custom_name($d[0]) . " = " . $values . "<br>";
						}
					}

				}
				if($return_array){
					return $return_arr;
				} else {
					return $details;
				}

			// only one criteria
			} else {

				//break items
				if(preg_match("/\//",$criteria)){
					if($return_array){
						$details = explode("/",$criteria);
						$return_arr[$details[0]] = $details[1];
						return $return_arr;
					} elseif($return_array === false){
						$details = explode("/",$criteria);
						$values = str_replace("-",", ",$details[1]);
						return self::rb_get_custom_name($details[0]) . " = " . $values;
					}
				}
			}
		}

		/*
		 * expan string criteria to readable format
		 */

		public static function rb_get_custom_name($id=NULL){

			global $wpdb;

			if($id==NULL) return "";

			$get_name = $wpdb->get_row("SELECT ProfileCustomTitle FROM " . table_agency_customfields . " WHERE ProfileCustomID = " . $id );

			if(count($get_name) > 0){
				return $get_name->ProfileCustomTitle;
			}

			return "";

		}


		/*
		 * process criteria passed by model
		 */
		public static function rb_get_job_criteria_passed($user_linked = NULL, $custom_criteria = NULL){

			global $wpdb;

			$passed_details = array();

			$get_profile_id = self::rb_casting_ismodel($user_linked);

			if($user_linked == NULL || 
				$user_linked == "" ||
				empty($user_linked) ||
				$get_profile_id === false ||
				$custom_criteria == "" ||
				$custom_criteria == NULL ||
				empty($custom_criteria)) return $passed_details;

			// get custom field mux
			$criteria_must_passed = array();
			$criteria_must_passed = self::rb_get_job_criteria($custom_criteria, true);

			// get actual quaalities of models through array
			$key_customid = implode(",", array_keys($criteria_must_passed)); 
			$query = "SELECT ProfileCustomID, ProfileCustomValue FROM " . table_agency_customfield_mux . " WHERE ProfileID = " . $get_profile_id . " AND ProfileCustomID IN(".$key_customid.")";
			$results = $wpdb->get_results($query);
			$actual_model_quality = array();
			if(count($results) > 0) {
				foreach($results as $actual){
					$actual_model_quality[$actual->ProfileCustomID] = $actual->ProfileCustomValue;
				}
			}

			// compare whats passed
			return self::rb_compare_criteria($criteria_must_passed, $actual_model_quality);

		}

		/*
		 * check if user is model / talent
		 * can also be used to return any column in table
		 * just assign it in the parameter $field_name
		 */
		public static function rb_casting_ismodel($user_linked = NULL, $field_name = NULL, $name = false){

			global $wpdb;

			if($user_linked == NULL || $user_linked == "" || empty($user_linked)) {
				return false;
			}

			$get_id = $wpdb->get_row($wpdb->prepare("SELECT ".$field_name." FROM " . table_agency_profile . " WHERE ProfileUserLinked = %d" ,$user_linked ),OBJECT,0);

			if(count($get_id) > 0){
				if(!empty($get_id->$field_name)){
					return $get_id->$field_name;
				}
				if(isset($get_id->ProfileID)){
					if(!$name){
						return $get_id->ProfileID;
					} else {
						return $get_id->ProfileContactDisplay;
					}
				}
				
			}

			return false;

		}

		/*
		 * check if user is casting agent
		 */
		public static function rb_casting_is_castingagent($user_linked = NULL, $field_name = 'CastingID'){

			global $wpdb;

			if(empty($user_linked)) {
				return false;
			}

			$get_id = $wpdb->get_row( "SELECT ".$field_name." FROM " . table_agency_casting . " WHERE CastingUserLinked = " . $user_linked ) ;

			if(count($get_id) > 0){
				return $get_id->$field_name;
			}

			return false;

		}



		public static function rb_compare_criteria($criteria = NULL, $model_criteria = NULL){

			global $wpdb;

			$actual_criteria_passed = array();

			if($criteria == NULL || $model_criteria == NULL) return "";

			// Loop
			foreach($criteria as $key => $val){

				if(strpos($val,"-") > -1 ){
						$val = explode("-",$val);
				}

				$q = $wpdb->get_row("SELECT * FROM ". table_agency_customfields ." WHERE ProfileCustomID = ".$key,ARRAY_A);
				$ProfileCustomType = $q;

				// NOW COMPARE ALL

				if ($ProfileCustomType["ProfileCustomType"] == 1 ||
					$ProfileCustomType["ProfileCustomType"] == 3 || 
					$ProfileCustomType["ProfileCustomType"] == 6) { // text, dropdown, radiobutton

					// at least compare text values vice versa
					if(strpos($val, $model_criteria[$key]) > -1){
						$actual_criteria_passed[$key] = $model_criteria[$key];
					} else {
						if(strpos($model_criteria[$key], $val) > -1){
							$actual_criteria_passed[$key] = $model_criteria[$key];
						}
					}

				} elseif ($ProfileCustomType["ProfileCustomType"] == 5) { //Checkbox

					// check arrays and compare vice versa
					// if some pass it is automatically passed.
					if(is_array($val)){
						if(strpos("-",$model_criteria[$key]) > -1){
							$model_criteria[$key] = explode("-",$model_criteria[$key]);
							$result = array_diff($criteria[$key], $model_criteria[$key]);
							if(count($result) > 0){
								$actual_criteria_passed[$key] = $model_criteria[$key];
							}
						} else {
							if(in_array($model_criteria[$key], $criteria[$key])){
								$actual_criteria_passed[$key] = $model_criteria[$key];
							}
						}
					} else {
						if(strpos("-",$model_criteria[$key]) > -1){
							$model_criteria[$key] = explode("-",$model_criteria[$key]);
							if(in_array( $criteria[$key], $model_criteria[$key])){
								$actual_criteria_passed[$key] = $model_criteria[$key];
							}
						} else {
							if(in_array($criteria[$key], $model_criteria[$key])){
								$actual_criteria_passed[$key] = $model_criteria[$key];
							}
						}
					}

				} elseif ($ProfileCustomType["ProfileCustomType"] == 7) { //Measurements 

					if(is_array($val)){
						arsort($val);
						if(is_numeric($val[0]) && is_numeric($val[1])){
							if($model_criteria[$key] >= $val[0] && $model_criteria[$key] <= $val[1]) {
								$actual_criteria_passed[$key] = $model_criteria[$key];
							}
						}
					} else {
						if(is_numeric($val)){
							if($model_criteria[$key] == $val){
								$actual_criteria_passed[$key] = $model_criteria[$key];
							}
						}
					}
				}
			}

			return $actual_criteria_passed;
		}


		/*
		 * get custom field values
		 */
		public static function load_custom_types($data = NULL){

				global $wpdb;

				$dat = explode("|",$data);

				$custom_fields = array();
				if(count($dat) > 0){
					foreach($dat as $d){
						$x = explode("/",$d);
						$custom_fields[$x[0]] = isset($x[1])?$x[1]:"";
					}
				}

				return $custom_fields;

		}

		/*
		 * actual loading of criteria fields
		 */
		public static function load_criteria_fields($data = NULL){

				global $wpdb;
				$rb_agency_options_arr = get_option('rb_agency_options');
					// What is the unit of measurement?
					$rb_agency_option_unittype = isset($rb_agency_options_arr['rb_agency_option_unittype'])?$rb_agency_options_arr['rb_agency_option_unittype']:1;


				$custom_fields = self::load_custom_types($data);



				echo "<script type='text/javascript'>
						function num_only(text) {
						console.log(text.val());
							if(text.val() !='')
							{
								var validationRegex = /[0-9]/g;
								if (!validationRegex.test(text.value)) {
									alert('Please enter only numbers.');
								}	
							}
						}
					</script>";

					$field_sql = "SELECT * FROM ". table_agency_customfields ." WHERE ProfileCustomView = 0 ORDER BY ProfileCustomOrder ASC";

				$field_results = $wpdb->get_results($field_sql,ARRAY_A);

				if(isset($custom_fields["age"]) && is_array($custom_fields["age"])){
									$custom_fields["age"] = @implode("-",$custom_fields["age"]);
				}

				$list_value = isset($custom_fields["age"])?$custom_fields["age"]:"";
				@list($min_val,$max_val) =  @explode("-",$list_value);

				echo "<div class=\"rbfield rbmulti rbmetric profilecustomid_age\" attrid=\"age\"  id=\"profilecustomid_age\">";
				echo "<label for=\"ProfileCustomIDage\">".__("Age",RBAGENCY_casting_TEXTDOMAIN)."</label>";
				echo "<div class=\"clear\"></div>";
				echo "<div><div><label for=\"ProfileCustomIDage_min\">".__("Min",RBAGENCY_casting_TEXTDOMAIN)."</label>";
				echo "<input value=\"".(isset($min_val)?$min_val:"")."\" class=\"stubby rbmin\" type=\"text\" name=\"ProfileCustomIDage[]\"  onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");'/></div>";
				echo "<div><label for=\"ProfileCustomIDage_max\">".__("Max",RBAGENCY_casting_TEXTDOMAIN)."</label>";
				echo "<input value=\"".(isset($max_val)?$max_val:"")."\" class=\"stubby rbmax\" type=\"text\" name=\"ProfileCustomIDage[]\" onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");' /></div>";
				echo "</div></div>";

				echo "<div class=\"rbfield rbselect rbsingle profilecustomid_gender\"  attrid=\"gender\"  id=\"profilecustomid_gender\">";
				echo "	<label for=\"ProfileCustomIDgender\">".__("Gender",RBAGENCY_casting_TEXTDOMAIN)."</label>";
				echo "	<div>";
				echo "		<select name=\"ProfileCustomIDgender[]\">";
				echo "			<option value=\"\">--</option>";
												$values = $wpdb->get_results("SELECT * FROM ".table_agency_data_gender);
												foreach($values as $value){
													// Validate Value
													if(!empty($value)) {
														// Identify Existing Value
														$isSelected = "";
														if($custom_fields["gender"]==stripslashes($value->GenderID)  || in_array(stripslashes($value->GenderID), $custom_fields["gender"])){
															$isSelected = "selected=\"selected\"";
															echo "		<option value=\"".stripslashes($value->GenderID)."\" ".$isSelected .">".stripslashes($value->GenderTitle)."</option>";
														} else {
															echo "		<option value=\"".stripslashes($value->GenderID)."\" >".stripslashes($value->GenderTitle)."</option>"; 
														}
													}
												}
				echo "		</select>";
				echo "	</div>";
				echo "</div>";



				foreach($field_results  as $data){

					// Set Variables
					$ProfileCustomID = $data['ProfileCustomID'];
					$ProfileCustomTitle = $data['ProfileCustomTitle'];
					$ProfileCustomType = $data['ProfileCustomType'];
					$ProfileCustomOptions = $data['ProfileCustomOptions'];
					$ProfileCustomShowSearch = $data['ProfileCustomShowSearch'];
					$ProfileCustomShowSearchSimple = $data['ProfileCustomShowSearchSimple']; 

						/* Field Type 
						 * 1 = Single Line Text
						 * 2 = Min / Max (Depreciated)
						 * 3 = Dropdown
						 * 4 = Textbox
						 * 5 = Checkbox
						 * 6 = Radiobutton
						 * 7 = Metric
						 *     1 = Inches
						 *     2 = Pounds
						 *     3 = Feet/Inches
						 */


						/*
						 * Single Text Line
						 */

						if($ProfileCustomType == 1) {
								echo "<div class=\"rbfield rbtext rbsingle profilecustomid_". $ProfileCustomID ."\" attrid=\"". $ProfileCustomID ."\" id=\"profilecustomid_". $ProfileCustomID ."\">";
								echo "<label for=\"ProfileCustomID". $ProfileCustomID ."\">". $ProfileCustomTitle ."</label>";
								//Commentd to fix language value populate
								//echo "<input type=\"text\" name=\"ProfileCustomID". $ProfileCustomID ."\" value=\"".$_SESSION["ProfileCustomID". $data1['ProfileCustomID']]."\" />";
								echo "<div><input type=\"text\" name=\"ProfileCustomID". $ProfileCustomID ."\" value=\"".
								(isset($custom_fields[$ProfileCustomID])?$custom_fields[$ProfileCustomID]:"")."\" /></div>";
								echo "</div>";

						/*
						 * Min Max
						 */
						} elseif($ProfileCustomType == 2) {

								echo "<div class=\"rbfield rbminmax rbtext rbsingle profilecustomid_". $ProfileCustomID ."\" attrid=\"". $ProfileCustomID ."\"  id=\"profilecustomid_". $ProfileCustomID ."\">";
								echo "<label for=\"ProfileCustomID". $ProfileCustomID ."\">". $ProfileCustomTitle ."</label>";
								$ProfileCustomOptions_String = str_replace(",",":",strtok(strtok($ProfileCustomOptions,"}"),"{"));
								list($ProfileCustomOptions_Min_label,$ProfileCustomOptions_Min_value,$ProfileCustomOptions_Max_label,$ProfileCustomOptions_Max_value) = explode(":",$ProfileCustomOptions_String);
								//print_r($custom_fields[$ProfileCustomID]);
							if(is_array($custom_fields[$ProfileCustomID])){
								$custom_fields[$ProfileCustomID]=@implode("-",$custom_fields[$ProfileCustomID]);
								list($min_val2,$max_val2) =  @explode("-",$custom_fields[$ProfileCustomID]);
							} else {
								list($min_val2,$max_val2) =  @explode("-",$custom_fields[$ProfileCustomID]);
							}

							if(!empty($ProfileCustomOptions_Min_value) && !empty($ProfileCustomOptions_Max_value)){
								echo "<div>";
								echo "		<label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Min", RBAGENCY_TEXTDOMAIN) . "&nbsp;&nbsp;</label>";
								echo "		<div><input type=\"text\" name=\"ProfileCustomID". $ProfileCustomID ."[]\" value=\"". $ProfileCustomOptions_Min_value ."\" onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");' /></div>";
								echo "</div>";
								echo "<div>";
								echo "		<label for=\"ProfileCustomLabel_max\" style=\"text-align:right;\">". __("Max", RBAGENCY_TEXTDOMAIN) . "&nbsp;&nbsp;</label>";
								echo "		<div><input type=\"text\" name=\"ProfileCustomID". $ProfileCustomID ."[]\" value=\"". $ProfileCustomOptions_Max_value ."\"  onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");'/></div>";
								echo "</div>";
							} else {
								echo "<div>";
								echo "		<label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Min", RBAGENCY_TEXTDOMAIN) . "&nbsp;&nbsp;</label>";
								echo "		<div><input type=\"text\" name=\"ProfileCustomID". $ProfileCustomID ."[]\" value=\"".$min_val2."\" onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");'/></div>";
								echo "</div>";
								echo "<div>";
								echo "		<label for=\"ProfileCustomLabel_max\" style=\"text-align:right;\">". __("Max", RBAGENCY_TEXTDOMAIN) . "&nbsp;&nbsp;</label>";
								echo "		<div><input type=\"text\" name=\"ProfileCustomID". $ProfileCustomID ."[]\" value=\"".$max_val2."\" onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");'/></div>";
								echo "</div>";
							}
							echo "</div>";

						/*
						 * Dropdown
						 */
						} elseif($ProfileCustomType == 3 || $ProfileCustomType == 9 ) {
								echo "<div class=\"rbfield rbselect rbsingle profilecustomid_". $ProfileCustomID ."\"  attrid=\"". $ProfileCustomID ."\"  id=\"profilecustomid_". $ProfileCustomID ."\">";
								echo "	<label for=\"ProfileCustomID". $ProfileCustomID ."\">". $ProfileCustomTitle ."</label>";
								echo "	<div>";
								echo "		<select name=\"ProfileCustomID". $ProfileCustomID ."[]\" ".($ProfileCustomType == 9 ?"multiple":"").">";
								echo "			<option value=\"\">--</option>";
												$values = explode("|",$ProfileCustomOptions);
												foreach($values as $value){
													// Validate Value
													if(!empty($value)) {
														// Identify Existing Value
														$isSelected = "";
														if($custom_fields[$ProfileCustomID]==stripslashes($value)  || in_array(stripslashes($value), $custom_fields[$ProfileCustomID])){
															$isSelected = "selected=\"selected\"";
															echo "		<option value=\"".stripslashes($value)."\" ".$isSelected .">".stripslashes($value)."</option>";
														} else {
															echo "		<option value=\"".stripslashes($value)."\" >".stripslashes($value)."</option>"; 
														}
													}
												}
								echo "		</select>";
								echo "	</div>";
								echo "</div>";


						/*
						 * Textbox
						 */
						} elseif($ProfileCustomType == 4) {
							/*
							TODO: Should we search text inside of text area?
											echo "<div class=\"rbfield rbsingle\">";
											echo "<label for=\"ProfileCustomID". $ProfileCustomID ."\">". $ProfileCustomTitle ."</label>";
											echo "<textarea name=\"ProfileCustomID". $ProfileCustomID ."\">". $custom_fields[$ProfileCustomID] ."</textarea>";
											echo "</div>";
							 */

						/*
						 * Checkbox
						 */
						} elseif($ProfileCustomType == 5) {
								echo "<fieldset class=\"rbfield rbcheckbox rbmulti profilecustomid_". $ProfileCustomID ."\"  attrid=\"". $ProfileCustomID ."\"  id=\"profilecustomid_". $ProfileCustomID ."\">";
								echo "<legend>". $ProfileCustomTitle ."</legend>";
								echo "<div>";
								$array_customOptions_values = explode("|", $ProfileCustomOptions);
								foreach($array_customOptions_values as $val){

									if(isset($custom_fields[$ProfileCustomID])){

										$dataArr = @explode(",",@implode(",",@explode("','",stripslashes($custom_fields[$ProfileCustomID]))));
										if(in_array($val,$dataArr,true)){
											echo "<div ><label><input type=\"checkbox\" checked=\"checked\" value=\"". $val."\"  name=\"ProfileCustomID". $ProfileCustomID ."[]\" />";
											echo "<span> ". $val."</span></label></div>";
										} else {
											if($val !=""){
												echo "<div><label><input type=\"checkbox\" value=\"". $val."\"  name=\"ProfileCustomID". $ProfileCustomID ."[]\" />";
												echo "<span> ". $val."</span></label></div>";
											}
										}
									} else {
										if($val !=""){
											echo "<div><label><input type=\"checkbox\" value=\"". $val."\"  name=\"ProfileCustomID". $ProfileCustomID ."[]\" />";
											echo "<span> ". $val."</span></label></div>";
										}
									}
								}

								echo "<input type=\"hidden\" value=\"\" name=\"ProfileCustomID". $ProfileCustomID ."[]\"/>";
								echo "</div>";
								echo "</fieldset>";

						/*
						 * Radio Button
						 */
						} elseif($ProfileCustomType == 6) {
								echo "<fieldset class=\"rbfield rbradio rbmulti profilecustomid_". $ProfileCustomID ."\"  attrid=\"". $ProfileCustomID ."\"  id=\"profilecustomid_". $ProfileCustomID ."\">";
								echo "<legend>". $ProfileCustomTitle ."</legend>";
								echo "<div>";
								$array_customOptions_values = explode("|", $ProfileCustomOptions);

								foreach($array_customOptions_values as $val){

									if(isset($custom_fields[$ProfileCustomID]) && $custom_fields[$ProfileCustomID] !=""){

										$dataArr = explode(",",implode(",",explode("','",$custom_fields[$ProfileCustomID])));

										if(in_array($val,$dataArr) && $val !=""){
											echo "<div><label><input type=\"radio\" checked=\"checked\" value=\"". $val."\"  name=\"ProfileCustomID". $ProfileCustomID ."[]\" />";
											echo "<span> ". $val."</span></label></div>";
										} else {
											if($val !=""){
												echo "<div><label><input type=\"radio\" value=\"". $val."\"  name=\"ProfileCustomID". $ProfileCustomID ."[]\" />";
												echo "<span> ". $val."</span></label></div>";
											}
										}
									} else {
										if($val !=""){
											echo "<div><label><input type=\"radio\" value=\"". $val."\"  name=\"ProfileCustomID". $ProfileCustomID ."[]\" />";
											echo "<span> ". $val."</span></label></div>";
										}
									}
								}
								echo "<input type=\"hidden\" value=\"\" name=\"ProfileCustomID". $ProfileCustomID ."[]\"/>";
								echo "</div>";
								echo "</fieldset>";

						/*
						 * Metric
						 */
						} elseif($ProfileCustomType == 7) {
								echo "<fieldset class=\"rbfield rbmetric rbmulti profilecustomid_". $ProfileCustomID ."\"  attrid=\"". $ProfileCustomID ."\"  id=\"profilecustomid_". $ProfileCustomID ."\">";

							/*
							 * Measurement Label
							 */

								$measurements_label = "";

								// 0 = Metrics(cm/kg)
								if($rb_agency_option_unittype ==0){
									if($ProfileCustomOptions == 1){
										$measurements_label  ="<em> (cm)</em>";
									} elseif($ProfileCustomOptions == 2){
										$measurements_label  ="<em> (kg)</em>";
									} elseif($ProfileCustomOptions == 3){
										$measurements_label  ="<em> (cm)</em>";
									}

								//1 = Imperial(in/lb)
								} elseif($rb_agency_option_unittype ==1){
									if($ProfileCustomOptions == 1){
										$measurements_label  ="<em> (in)</em>";
									} elseif($ProfileCustomOptions == 2){
										$measurements_label  ="<em> (lb)</em>";
									} elseif($ProfileCustomOptions == 3){
										$measurements_label  ="<em> (ft/in)</em>";
									}
								}

								echo "<legend>". $ProfileCustomTitle . $measurements_label ."</legend>";

							/*
							 * Handle Array
							 */

								// Is Array?
								if(isset($custom_fields[$ProfileCustomID]) && is_array($custom_fields[$ProfileCustomID])){
									$custom_fields[$ProfileCustomID] = @implode("-",$custom_fields[$ProfileCustomID]);
								}

								// List
								$list_value = isset($custom_fields[$ProfileCustomID])?$custom_fields[$ProfileCustomID]:"";
								@list($min_val,$max_val) =  @explode("-",$list_value);

								// Is Height and is Imperial
								if($ProfileCustomTitle=="Height" && $rb_agency_option_unittype == 1 && $data['ProfileCustomOptions']==3){

									echo "<div>";
									echo "<div><label>Min</label>";
									echo "<select class=\"rbmin\" name=\"ProfileCustomID". $ProfileCustomID ."[]\">\n";
									if (empty($ProfileCustomValue)) {
										echo "  <option value=\"\">--</option>\n";
									}
										// 
										$i=12;
										$heightraw = 0;
										$heightfeet = 0;
										$heightinch = 0;
										while($i<=96)  {
											$heightraw = $i;
											$heightfeet = floor($heightraw/12);
											$heightinch = $heightraw - floor($heightfeet*12);
											echo " <option value=\"". $i ."\" ". selected(isset($min_val)?$min_val:"", $i) .">". $heightfeet ." ft ". $heightinch ." in</option>\n";
											$i++;
										}
									echo " </select></div>\n";

									echo "<div><label>Max</label><select  class=\"rbmax\" name=\"ProfileCustomID". $ProfileCustomID ."[]\">\n";

									if (empty($ProfileCustomValue)) {
										echo "  <option value=\"\">--</option>\n";
									}
										// 
										$i=12;
										$heightraw = 0;
										$heightfeet = 0;
										$heightinch = 0;
										while($i<=96)  {
											$heightraw = $i;
											$heightfeet = floor($heightraw/12);
											$heightinch = $heightraw - floor($heightfeet*12);
											echo " <option value=\"". $i ."\" ". selected(isset($max_val)?$max_val:"", $i) .">". $heightfeet ." ft ". $heightinch ." in</option>\n";
											$i++;
										}
									echo " </select>\n";
									echo "</div>\n";
									echo "</div>";
								} else {
									echo "<div>";
									// for other search0
									echo "<div><label for=\"ProfileCustomID".$ProfileCustomID."_min\">Min</label><input value=\""
									.(!is_array($min_val) && $min_val != "Array" ? $min_val : "")
									."\" class=\"stubby rbmin\" type=\"text\" name=\"ProfileCustomID"
									.$ProfileCustomID."[]\"  onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");'/></div>";

									echo "<div><label for=\"ProfileCustomID".(isset($data1['ProfileCustomID'])?$data1['ProfileCustomID']:"")
									."_max\">Max</label><input value=\"".(isset($max_val)?$max_val:"") ."\" class=\"stubby rbmax\" type=\"text\" name=\"ProfileCustomID".$ProfileCustomID."[]\" onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");' /></div>";
									echo "</div>";
								}
							echo "</fieldset>";
					}

				}
			die();

		}

		/*
		 * get model details
		 */
		public static function rb_casting_get_model_details($id = NULL){

			global $wpdb;

			if($id == NULL) return "";

			$get_name = $wpdb->get_row("SELECT * FROM " . table_agency_profile . " WHERE ProfileID = " . $id);

			if(count($get_name) > 0){
					return $get_name;
			}

			return "";

		}

		/*
		 * display pagination
		 */
		public static function rb_casting_paginate($link = NULL, $table = NULL, $where = NULL, $count_per_page = NULL, $selected_page = 0 ){

			global $wpdb;

			if(($link == NULL || $link == "") || 
				($table == NULL || $table == "" ) ||
				($count_per_page == NULL || $count_per_page == "")) return "";
			$results = $wpdb->get_row("SHOW TABLES LIKE '".$table."'");
				$count = $wpdb->num_rows;
			if($count == 1) {

				if(!empty($where) && $where != "" && $where != NULL){
						$get_row_count = $wpdb->get_row("SELECT COUNT(1) as total FROM " . $table . " " . $where, ARRAY_A);
				} else {
						$get_row_count = $wpdb->get_row("SELECT COUNT(1) as total FROM " . $table, ARRAY_A);
				}

				$total = $get_row_count;

				$ceiling = ceil($total["total"] / $count_per_page);

				if($ceiling > 1){

						echo "<div id='jobposting-pagination' class='rbpagination bottom' >";

						if(($ceiling - $selected_page) != ($ceiling - 1) && ($selected_page != 0)){
							echo "<a href='".$link.($selected_page-1)."'>".__("prev",RBAGENCY_casting_TEXTDOMAIN)."</a>";
						}

						echo "<select name='paginate_page' style='width:100px' onchange='window.location.href= this.options[this.selectedIndex].value'>";

							for($x = 1; $x <= $ceiling; $x++){
									echo "<option value='".$link.$x."' ".selected($x,$selected_page,false).">".$x."</option>";
							}

						echo "</select>";

						if(($ceiling - $selected_page) != 0){
							if($selected_page == 0 or $selected_page == ""){
								$next_link = 2; 
							} else {
								$next_link = $selected_page + 1; 
							}
							echo "<a href='".$link.$next_link."'>".__("next",RBAGENCY_casting_TEXTDOMAIN)."</a>";
						}
						echo "</div>";

				}

			}

			return "";
		}

		/*
		 * get percentage passed
		 */
		public static function rb_casting_get_percentage_passed($Job_ID=NULL, $Job_Criteria_Passed=NULL){

			global $wpdb;

			if($Job_ID == NULL || $Job_ID == 0 || $Job_ID == "") return "";

			if($Job_Criteria_Passed == NULL || $Job_Criteria_Passed == "") return "";

			$get_criteria = $wpdb->get_row("SELECT Job_Criteria FROM " . table_agency_casting_job . " WHERE Job_ID = " . $Job_ID);

			if(count($get_criteria) > 0){

					if(preg_match("/\|/", $get_criteria->Job_Criteria)){
						$count = count(explode("|", $get_criteria->Job_Criteria));
					} else {
						$count = 1;
					}

					$res = ( $Job_Criteria_Passed / $count ) * 100;
					$res = round($res); 
					return " or " . $res . "% Matched";

			}

			return "";

		}

		/*
		 * update data for applicants when criteria was changed from clients end
		 */
		public static function rb_update_applicant_data($criteria = NULL, $JobID = NULL){

			global $wpdb;

			if($JobID == NULL || $JobID == 0 || $JobID == "") return "";

			if(self::rb_get_job_visibility($JobID) == 2){

				if($criteria == NULL || $criteria == "" || empty($criteria)) return "";

				$get_all_applicants = "SELECT Job_UserLinked FROM " . table_agency_casting_job_application . " WHERE Job_ID = " . $JobID;

				$applicants_result = $wpdb->get_results($get_all_applicants);

				if(count($applicants_result)){

					foreach($applicants_result as $applicants){
							$job_criterias = RBAgency_Casting::rb_get_job_criteria_passed($applicants->Job_UserLinked, $criteria);
							$Job_Criteria_Details = serialize($job_criterias);

							// get precentage
							if(preg_match("/\|/", $criteria)){
								$count = count(explode("|", $criteria));
							} else {
								$count = 1;
							}

							$res = ( count($job_criterias) / $count ) * 100;

							$percentage = round($res); 

							$wpdb->query("UPDATE " . table_agency_casting_job_application . 
										" SET Job_Criteria_Details = '" . $Job_Criteria_Details . "',
											Job_Criteria_Passed = " . count($job_criterias) . ", 
											Job_Criteria_Percentage = " . $percentage .
										" WHERE Job_Userlinked = " . $applicants->Job_UserLinked . " AND Job_ID = " . $JobID );

					}

				}

			} elseif(self::rb_get_job_visibility($JobID) == 1){

				$get_all_applicants = "SELECT Job_UserLinked FROM " . table_agency_casting_job_application . " WHERE Job_ID = " . $JobID;
				$applicants_result = $wpdb->get_results($get_all_applicants);
				if(count($applicants_result)){
					foreach($applicants_result as $applicants){
							$wpdb->query("UPDATE " . table_agency_casting_job_application . 
										" SET Job_Criteria_Details = '',
											Job_Criteria_Passed = 10, 
											Job_Criteria_Percentage = 100 " . 
										" WHERE Job_Userlinked = " . $applicants->Job_UserLinked . " AND Job_ID = " . $JobID );
					}
				}

			} elseif(self::rb_get_job_visibility($JobID) == 0){

				$get_all_applicants = "SELECT Job_UserLinked FROM " . table_agency_casting_job_application . " WHERE Job_ID = " . $JobID;
				$applicants_result = $wpdb->get_results($get_all_applicants);

				if(count($applicants_result)){

					foreach($applicants_result as $applicants){
						$wpdb->query("UPDATE " . table_agency_casting_job_application . 
									" SET Job_Criteria_Details = '',
										Job_Criteria_Passed = 0, 
										Job_Criteria_Percentage = 0 " . 
									" WHERE Job_Userlinked = " . $applicants->Job_UserLinked . " AND Job_ID = " . $JobID );
					}

				}

			}

			return true;

		}


		/*
		 * Get job owner id from casting jobs
		 */
		public static function rb_casting_job_ownerid($JobID = NULL){

			global $wpdb;

			if($JobID == NULL || $JobID == 0 || $JobID == "") return "";

			$get_owner = "SELECT Job_UserLinked FROM " . table_agency_casting_job . " WHERE Job_ID = " . $JobID;

			$owner_result = $wpdb->get_row($get_owner);

			if(count($owner_result)){
				return $owner_result->Job_UserLinked;
			}
			return "";
		}

		/*
		 * get primary image for applicants
		 */
		public static function rb_get_model_image($PID = NULL){

			global $wpdb;

			if(empty($PID) or is_null($PID)) return false;

			$profile_id = "SELECT * FROM ". table_agency_profile .
						" WHERE ProfileID = " . $PID;

			$get_id = $wpdb->get_row($profile_id);

			if(count($get_id) > 0){

				$get_image = "SELECT ProfileMediaURL FROM ". table_agency_profile_media .
								" WHERE ProfileID = " .$get_id->ProfileID . " AND ProfileMediaPrimary = 1";

				$get_res = $wpdb->get_row($get_image);

				if(count($get_res) > 0){

					$image = get_bloginfo('wpurl'). "/wp-content/uploads/profile-media/". $get_id->ProfileGallery."/". $get_res->ProfileMediaURL;

					return $image;

				}

			}

			return "";

		}


		/*
		 * send notifications upon registration
		 */
		public static function rb_casting_send_notification($user_id, $plaintext_pass = ''){

				$user = new WP_User($user_id);

				$user_login = stripslashes($user->user_login);
				$user_email = stripslashes($user->user_email);

				$message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "<br><br>";
				$message .= sprintf(__('Username: %s'), $user_login) . "<br><br>";
				$message .= sprintf(__('E-mail: %s'), $user_email) . "<br>";

				$rb_agency_options_arr = get_option('rb_agency_options');

				@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);

				if ( empty($plaintext_pass) )  
					return;
				$message  = __('Hi there,',RBAGENCY_casting_TEXTDOMAIN) . "<br><br>";
				$message .= sprintf(__("Thanks for joining %s! Here's how to log in:",RBAGENCY_casting_TEXTDOMAIN), get_option('blogname')) . "<br><br>"; 
				$message .= __('Login: ',RBAGENCY_interact_TEXTDOMAIN)."<a href='".get_option('home') ."/casting-login'>".get_option('home') ."/casting-login </a><br>"; 
				$message .= sprintf(__('Username: %s',RBAGENCY_casting_TEXTDOMAIN), $user_login) . "<br>"; 
				$message .= sprintf(__('Password: %s',RBAGENCY_casting_TEXTDOMAIN), $plaintext_pass) . "<br><br>"; 
				$message .= sprintf(__('If you have any problems, please contact us at %s.',RBAGENCY_casting_TEXTDOMAIN), get_option('admin_email')) . "<br><br>"; 
				$message .= __('Regards,',RBAGENCY_casting_TEXTDOMAIN)."<br>";
				$message .= get_option('blogname') . __(' Team',RBAGENCY_casting_TEXTDOMAIN) ."<br>"; 
				$message .= get_option('home') ."<br>"; 
				$message .= "<br>"; 
				$message .= '<img src="'.get_option('home').$rb_agency_options_arr['rb_agency_option_agencylogo'].'" width="200">';
				$headers = 'From: '. get_option('blogname') .' <'. get_option('admin_email') .'>' . "<br>";

				//add_filter( 'wp_mail_content_type', function set_content_type( $content_type ) {
					//return 'text/html';
				//});

				wp_mail($user_email, sprintf(__('%s Registration Successful! Login Details',RBAGENCY_casting_TEXTDOMAIN), get_option('blogname')), $message, $headers);
		}


		/*
		 * get visibility
		 */
		public static function rb_get_job_visibility($jobid = NULL){

			global $wpdb;

			if(empty($jobid) or is_null($jobid)) return false;

			$visibility_id = "SELECT Job_Visibility FROM ". table_agency_casting_job .
						" WHERE Job_ID = " . $jobid;

			$get = $wpdb->get_row($visibility_id);

			if(count($get) > 0){
				return $get->Job_Visibility;
			}

			return "";
		}


	/* 
	 *  update casting cart
	 */
	public static function rb_update_castingcart($talent = NULL, $JobID = NULL) {

		global $wpdb;

		if(is_null($talent) && $talent != '') return "";
		if(is_null($JobID) && $JobID != '') return "";

		if(is_user_logged_in()){
			if(isset($talent) && $talent !== "" ){

				if(strpos("none", $JobID) > -1 ){

					$talent_arr = trim($talent,";");
					$talent_arr = explode(";",$talent_arr);
					foreach($talent_arr as $talent){

						$data = explode(":",$talent);
						$talent = self::rb_casting_ismodel($data[1], "ProfileID");
						$JobID = $data[0];

						$query_castingcart = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". table_agency_castingcart."  WHERE CastingCartTalentID = %s  AND CastingCartProfileID = %s AND CastingJobID = %s",$talent,rb_agency_get_current_userid(),$JobID),ARRAY_A);
						$count_castingcart = $wpdb->num_rows;
						$datas_castingcart = $query_castingcart;

						if($count_castingcart<=0){ //if not exist insert favorite!
							$insert = "INSERT INTO " . table_agency_castingcart . "(CastingCartProfileID,CastingCartTalentID,CastingJobID) VALUES(%s,%s,%s)"; 
							$wpdb->query($wpdb->prepare($insert,rb_agency_get_current_userid(), $talent, $JobID ));
						} else { // favorite model exist, now delete!
							$wpdb->query($wpdb->prepare("DELETE FROM  ". table_agency_castingcart."  WHERE CastingCartTalentID = %s AND CastingCartProfileID = %s AND CastingJobID = %s",$talent,rb_agency_get_current_userid(),$JobID));
						}
					}

					$arr = array( "data" => "success");
					echo json_encode($arr);
				} else {

					//$talent = self::rb_casting_ismodel($talent, "ProfileID");
					$query_castingcart = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". table_agency_castingcart."  WHERE CastingCartTalentID = %s  AND CastingCartProfileID = %s AND CastingJobID = %s",$talent,rb_agency_get_current_userid(),$JobID),ARRAY_A);
					$count_castingcart = $wpdb->num_rows;
					$datas_castingcart = $query_castingcart;

					if($count_castingcart<=0){ //if not exist insert favorite!
						$insert = "INSERT INTO " . table_agency_castingcart . "(CastingCartProfileID,CastingCartTalentID,CastingJobID) VALUES(%s,%s,%s)"; 
						$wpdb->query($wpdb->prepare($insert,rb_agency_get_current_userid(), $talent, $JobID ));
						$arr = array( "data" => "inserted");
						echo json_encode($arr);
					} else { // favorite model exist, now delete!
						$wpdb->query($wpdb->prepare("DELETE FROM  ". table_agency_castingcart."  WHERE CastingCartTalentID = %s AND CastingCartProfileID = %s AND CastingJobID = %s",$talent,rb_agency_get_current_userid(),$JobID));
						$arr = array("data" => "deleted");
						echo json_encode($arr);
					}
				}

			}
		} else {
			echo "not_logged";
		}

		die();

	}


		/* 
		 *  check in cart
		 */
		public static function rb_check_in_cart($talent = NULL, $JobID = NULL) {

			global $wpdb;

			if(is_null($talent) && $talent != '') return false;

			if(is_user_logged_in()){

				if(isset($talent) && $talent ){
					//$talent = self::rb_casting_ismodel($talent, "ProfileID");
					$query_castingcart =$wpdb->get_results($wpdb->prepare("SELECT * FROM ". table_agency_castingcart."  WHERE CastingCartTalentID= %s  AND CastingCartProfileID = %s AND CastingJobID = %s ",$talent,rb_agency_get_current_userid(),$JobID ));
					$count_castingcart =$wpdb->num_rows;

					if($count_castingcart > 0){
						return true;
					} else {
						return false;
					}

				}

			}
			return false;
		}

		/*
		 * Admin casting jobs list
		 */
		public static function rb_display_casting_jobs(){

			global $wpdb;

			$sqldata = "";
			$query = "";

			if(isset($_REQUEST["m"]) && $_REQUEST['m'] == '1' ) {
				// Message of successful mail form mass email 
				echo "<div id=\"message\" class=\"updated\"><p>".__("Email Messages successfully sent!",RBAGENCY_casting_TEXTDOMAIN)."</p></div>";
			}

			if(isset($_POST["mass_delete"])){
				unset($_POST["mass_delete"]);
				$ids = implode(",",$_POST);
				$wpdb->query("DELETE FROM ".table_agency_casting_job." WHERE Job_ID IN(".$ids.") ");

					echo "<div id=\"message\" class=\"updated\"><p>".__("Successfully deleted.",RBAGENCY_casting_TEXTDOMAIN)."</p></div>";

			}

			$rb_agency_options_arr = get_option('rb_agency_options');
				$rb_agency_option_locationtimezone = (int)$rb_agency_options_arr['rb_agency_option_locationtimezone'];

			// Sort By
			$sort = "";
			if (isset($_GET['sort']) && !empty($_GET['sort'])){
				$sort = $_GET['sort'];
			} else {
				$sort = "jobs.Job_ID ";
			}

			// Sort Order
			$dir = "";
			if (isset($_GET['dir']) && !empty($_GET['dir'])){
				$dir = $_GET['dir'];
				if ($dir == "desc" || !isset($dir) || empty($dir)){
					$sortDirection = "asc";
				} else {
					$sortDirection = "asc";
				}
			} else {
				$sortDirection = "desc";
				$dir = "desc";
			}

			// Filter
			$filter = " WHERE jobs.Job_ID > 0  AND agency.CastingUserLinked = jobs.Job_UserLinked ";
			if (isset($_GET['Job_Title']) && !empty($_GET['Job_Title'])){
				$selectedTitle = isset($_GET['Job_Title'])?$_GET['Job_Title']:"";
				$query .= "&Job_Title". $selectedTitle ."";
				$filter .= " AND jobs.Job_Title LIKE '%". $selectedTitle ."%'";
			}
			if(isset($_GET['Agency_Producer']) && !empty($_GET['Agency_Producer'])){
				$selectedTitle = isset($_GET['Agency_Producer'])?$_GET['Agency_Producer']:"";
				$query .= "&Agency_Producer". $selectedTitle ."";
				$filter .= " AND agency.CastingContactCompany LIKE '%". $selectedTitle ."%' ";
			}

			//Paginate
			$sqldata  = "SELECT jobs.*,talents.* , agency.* FROM ". table_agency_casting_job ." jobs LEFT JOIN ". table_agency_castingcart_availability ." talents ON jobs.Job_ID = talents.CastingAvailabilityID LEFT JOIN ".table_agency_casting." as agency ON agency.CastingUserLinked = jobs.Job_UserLinked ". $filter  .""; // number of total rows in the database
			$results=  $wpdb->get_results($sqldata);

			$items =$wpdb->num_rows; // number of total rows in the database
			if($items > 0) {

				$p = new RBAgency_Pagination;
				$p->items($items);
				$p->limit(50); // Limit entries per page
				$p->target("admin.php?page=". (isset($_GET['page'])?$_GET['page']:"") .$query);
				@$p->currentPage(isset($_GET[$p->paging])?$_GET[$p->paging]:0); // Gets and validates the current page
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

			?>

			<?php if((empty($_GET["action2"]) && empty($_GET["Job_ID"])) || isset($_GET["action2"]) && $_GET["action2"] == "deleteCastingJob"){ ?>
			<a href="<?php echo admin_url("admin.php?page=rb_agency_castingjobs&action=informTalent&action2=addnew") ?>" class="button-primary">Add New Job</a>

				<table cellspacing="0" class="widefat fixed">
					<thead>
						<tr>
							<td style="width: 360px;" nowrap="nowrap">
								<form method="GET" action="<?php echo admin_url("admin.php?page=". $_GET['page']); ?>&amp;action=informTalent">
								<input type='hidden' name='page_index' id='page_index' value='<?php echo isset($_GET['page_index'])?$_GET['page_index']:""; ?>' />  
								<?php echo __("Search by :",RBAGENCY_casting_TEXTDOMAIN); ?> 
								<?php echo __("Title:",RBAGENCY_casting_TEXTDOMAIN); ?> <input type="text" name="Job_Title" value="<?php echo isset($Job_Title)?$Job_Title:""; ?>" style="width: 100px;" />
									
									<input type="hidden" name="action" value="informTalent"/>
									<input type='hidden' name='page' id='page' value='<?php echo $_GET['page']; ?>' />
								
							</td>
							<td style="width: 360px;" nowrap="nowrap">
								
								<input type='hidden' name='page_index' id='page_index' value='<?php echo isset($_GET['page_index'])?$_GET['page_index']:""; ?>' />  
								Agency/Producer : <input type="text" name="Agency_Producer" value="<?php echo isset($_GET['Agency_Producer'])?$_GET['Agency_Producer'] : ""; ?>" style="width: 100px;" />
								<input type="submit" value="Filter" class="button-primary" />	
								</form>
							</td>
							<td style="width: 200px;" nowrap="nowrap">
								<form method="GET" action="<?php echo admin_url("admin.php?page=". $_GET['page']); ?>">
								<input type='hidden' name='page_index' id='page_index' value='<?php echo isset($_GET['page_index'])?$_GET['page_index']:""; ?>' />  
								<input type='hidden' name='page' id='page' value='<?php echo $_GET['page']; ?>' />
								<input type="submit" value="Clear Filters" class="button-secondary" />
								<input type="hidden" name="action" value="informTalent"/>
								</form>
							</td>
							<td>&nbsp;</td>
						</tr>
				</thead>
				</table>

				<form method="post" action="<?php echo admin_url("admin.php?page=". $_GET['page']); ?>">
				<table cellspacing="0" class="widefat fixed">
				<thead>
					<tr class="thead">
						<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
						<th class="column" scope="col" style="width:50px;"><a href="admin.php?page=<?php echo $_GET['page']; ?>&sort=Job_ID&dir=<?php echo $sortDirection; ?>">ID</a></th>
						<th class="column" scope="col" ><a href="admin.php?page=<?php echo $_GET['page']; ?>&sort=Job_Title&dir=<?php echo $sortDirection; ?>">Job Title</a></th>
						<th class="column" scope="col" ><a href="admin.php?page=<?php echo $_GET['page']; ?>&sort=Job_UserLinked&dir=<?php echo $sortDirection; ?>">Agency/Producer</a></th>
						<th class="column" scope="col" style="width:80px;"><a href="admin.php?page=<?php echo $_GET['page']; ?>&sort=Job_Date_Start&dir=<?php echo $sortDirection; ?>">Profiles</a></th>
						<th class="column" scope="col">Date Created</th>
					</tr>
				</thead>
				<tfoot>
					<tr class="thead">
						<th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"/></th>
						<th class="column" scope="col">ID</th>
						<th class="column" scope="col">Job Title</th>
						<th class="column" scope="col">Agency/Producer</th>
						<th class="column" scope="col">Profiles</th>
						<th class="column" scope="col">Date Created</th>
					</tr>
				</tfoot>
				<tbody>

				<?php

				$query2 = "SELECT jobs.*, agency.* FROM ". table_agency_casting_job ." jobs, ".table_agency_casting."  as agency ". $filter  ." ORDER BY $sort $dir $limit";
				$results2 = $wpdb->get_results($query2, ARRAY_A);
				$count2 = $wpdb->num_rows;

				foreach ($results2 as $data2) {
					$Job_Title = stripslashes($data2['Job_Title']);
					$Job_ID = stripslashes($data2['Job_ID']);
					$Job_Talents = stripslashes($data2['Job_Talents']);
					$Job_Talents = explode(",",str_replace("NULL","",$Job_Talents));
					$Job_AgencyName = stripslashes($data2["CastingContactCompany"]);

				?>
				<tr>
					<th class="check-column" scope="row">
						<input type="checkbox" value="<?php echo $Job_ID; ?>" class="administrator" id="<?php echo $Job_ID; ?>" name="<?php echo $Job_ID; ?>"/>
					</th>
					<td>
						<?php echo $Job_ID; ?>
					</td>
					<td>
						<?php echo $Job_Title; ?>
						<div class="row-actions">
								<span class="view"><a href="<?php echo get_bloginfo("url")."/job-detail/".$Job_ID; ?>" target="_blank">View</a> | </span>
								<span class="view"><a href="<?php echo get_bloginfo("url")."/view-applicants/?filter_jobtitle=".$Job_ID."&filter_applicant=&filter_jobpercentage=&filter_rating=&filter_perpage=10&filter=filter"; ?>" target="_blank">Applicants</a> | </span>
								<span class="edit"><a href="admin.php?page=<?php echo $_GET['page']; ?>&action=informTalent&Job_ID=<?php echo $Job_ID; ?>">Edit</a> | </span>
								<span class="delete"><a class='submitdelete' title='Delete this Record' href='<?php echo admin_url("admin.php?page=". $_GET['page']); ?>&amp;action=informTalent&amp;action2=deleteCastingJob&amp;removeJob_ID=<?php echo $Job_ID; ?>' onclick="if ( confirm('You are about to delete this record\'\n \'Cancel\' to stop, \'OK\' to delete.') ) {return true;}return false;">Delete</a></span>
								<span class="edit"><a href="admin.php?page=<?php echo $_GET['page']; ?>&action=viewAllAuditions&Job_ID=<?php echo $Job_ID; ?>">&nbsp; | &nbsp;View All Auditions</a></span>
						</div>
					</td>
					<td>
					<?php echo $Job_AgencyName; ?>
					</td>
					<td>
						<?php  $casting_cart = $wpdb->get_row($wpdb->prepare("SELECT count(*) as total FROM ".table_agency_castingcart." WHERE CastingJobID = %d ",$Job_ID)); ?>
					<?php  echo isset($casting_cart->total)?$casting_cart->total:0; ?>
					</td>
					<td>
						<?php echo date("M d, Y - h:iA",strtotime($data2["Job_Date_Created"]));?>
					</td>
				</tr>
				<?php
				}

					if ($count2 < 1) {
						if (isset($filter)) {
				?>
				<tr>
					<th class="check-column" scope="row"></th>
					<td class="name column-name" colspan="3">
						<p><?php echo __("No profiles found with this criteria.",RBAGENCY_casting_TEXTDOMAIN); ?></p>
					</td>
				</tr>
				<?php
						} else {
				?>
				<tr>
					<th class="check-column" scope="row"></th>
					<td class="name column-name" colspan="3">
						<p><?php echo __("There aren't any Profiles loaded yet!",RBAGENCY_casting_TEXTDOMAIN); ?></p>
					</td>
				</tr>
				<?php
						}
				?>
				<?php }?>
				</tbody>
			</table>
			<?php if($items > 0) { ?>
			<div class="tablenav">
				<div class='tablenav-pages'>
					<?php 

						echo $p->show();// Echo out the list of paging. 
					?>
				</div>
			</div>
			<?php }?>
		</div>
		<input type="submit" class="btn button-secondary" onclick="javascript:return !confirm('Are you sure that you want to delete the selected?')?false:true;" name="mass_delete" value="Delete"/>
			<?php
			}
		}

		public static function sendText($mobile, $link, $message = ""){

			$rb_agency_options_arr = get_option('rb_agency_options');
			$rb_agency_value_agency_easytxturl = isset($rb_agency_options_arr['rb_agency_option_agency_easytxturl'])?$rb_agency_options_arr['rb_agency_option_agency_easytxturl']:"";
			$rb_agency_value_agency_easytxtkey = isset($rb_agency_options_arr['rb_agency_option_agency_easytxtkey'])?$rb_agency_options_arr['rb_agency_option_agency_easytxtkey']:"";
			$rb_agency_value_agency_easytxtsecret = isset($rb_agency_options_arr['rb_agency_option_agency_easytxtsecret'])?$rb_agency_options_arr['rb_agency_option_agency_easytxtsecret']:"";
			$rb_agency_value_agencyname = $rb_agency_options_arr['rb_agency_option_agencyname'];
			$rb_agency_value_agencyemail = $rb_agency_options_arr['rb_agency_option_agencyemail'];

			$content = "";
			if(!empty($message)){
				$content = str_replace("[casting-job-url]", $link, $message);;
			} else {
				$content = $rb_agency_value_agencyname.' '.__("has put you forward for a Job. See the following link:",RBAGENCY_casting_TEXTDOMAIN).' '.$link;
			}

			$xml_data ='<request>
							<content>'.$content.'</content>
							<recipients>';
							foreach($mobile as $number){
									$number = str_replace(' ', '', $number);
									$number = trim($number);
									$number = preg_replace("/[^0-9,.]/", "", $number);
									$xml_data .= '<recipient>'.$number.'</recipient>';
							}
			$xml_data .= '</recipients>
						</request>';

			$url = "http://".$rb_agency_value_agency_easytxtkey.":".$rb_agency_value_agency_easytxtsecret."@".$rb_agency_value_agency_easytxturl."/api2/xml/sms";
			$ch = "";
			if(function_exists("curl_init")){
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
				curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$output = curl_exec($ch);
				//echo $output;
				curl_close($ch);
			}
	}


	/*
	 * Notiffy talents for the job availability
	 */
	public static function sendEmail($emails,$link, $message = ""){
			$rb_agency_options_arr = get_option('rb_agency_options');
			$rb_agency_value_agencyname = $rb_agency_options_arr['rb_agency_option_agencyname'];
			$rb_agency_value_agencyemail = $rb_agency_options_arr['rb_agency_option_agencyemail'];
			$agency_name = $rb_agency_options_arr['rb_agency_option_agencyname'];

			// Mail it
			$MassEmailMessage = "";
			$MassEmailMessage = "Hi,<br><br>";
			$link_anchor = "<a href=".$link.">".$link."</a>";
			if(!empty($message)){
					$MassEmailMessage .= str_replace("[casting-job-url]", $link_anchor, $message);
			} else {
					$MassEmailMessage .= $rb_agency_value_agencyname." ".__("has put you forward for a Job. Click link to view job info & confirm availability:",RBAGENCY_casting_TEXTDOMAIN)." ".$link_anchor."<br><br>";
			}
			$MassEmailMessage	.= "Regards,<br>";
			$MassEmailMessage .= $agency_name."<br>"; 
			$MassEmailMessage .= "<a href='".get_option('home')."'>".get_option('home') ."</a><br><br>";
			$MassEmailMessage .= '<img src="'.site_url().$rb_agency_options_arr['rb_agency_option_agencylogo'].'" width="200">';
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=iso-8859-1';
			$headers[] = 'From: '.$rb_agency_value_agencyname .' <'. $rb_agency_value_agencyemail .'>';
			
			$isSent = wp_mail(trim($emails[0]), $rb_agency_value_agencyname." ".__(": Job Availability",RBAGENCY_casting_TEXTDOMAIN)." ", $MassEmailMessage, $headers);
			
		

	}


	/*
	 * Notify admin about the confirmed job availability
	 */
	public static function sendEmailCastingAvailability($Talents_Display_Name,$Availability,$Job_Name,$link){
			$rb_agency_options_arr = get_option('rb_agency_options');
			$rb_agency_value_agencyname = $rb_agency_options_arr['rb_agency_option_agencyname'];
			$rb_agency_value_agencyemail = $rb_agency_options_arr['rb_agency_option_agencyemail'];

			// Mail it
			$MassEmailMessage	= $TalentsDisplayName." has changed the job availability to \"".$Availability."\" for the job '".$Job_Name."'. "
								. "\nClick here to review your casting cart: ".$link
								.  "\n\n-".get_bloginfo("name");
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=iso-8859-1';
			$headers[] = 'From: '. $rb_agency_value_agencyname .' <'.$rb_agency_value_agencyemail .'>';

			$isSent = wp_mail($rb_agency_value_agencyemail, $rb_agency_value_agencyname." ".__(": Job Availability",RBAGENCY_casting_TEXTDOMAIN)."", $MassEmailMessage, $headers);
	}


	/*
	 * Notify admin about the availability of shortlisted profiles for a specifc job
	 */
	public static function sendEmailAdminCheckAvailability($castingname, $castingemail, $message, $link){
		$rb_agency_options_arr = get_option('rb_agency_options');
		$rb_agency_value_agencyname = $rb_agency_options_arr['rb_agency_option_agencyname'];
		$rb_agency_value_agencyemail = trim($rb_agency_options_arr['rb_agency_option_agencyemail']);

		// Mail it
		$Message	= str_replace("[shortlisted-link-placeholder]", $link, $message);
		//$headers[] = 'MIME-Version: 1.0';
		//$headers[] = 'Content-type: text/html; charset=iso-8859-1';
		//$headers[] = 'From: "'. $castingname .'" <'. trim($castingemail) .'>';
		$headers = 'From: "'. $castingname .'" <'. trim($castingemail) .'>';
		$isSent = wp_mail($rb_agency_value_agencyemail, $rb_agency_value_agencyname." ".__(": Check availability",RBAGENCY_casting_TEXTDOMAIN)." ", $Message, $headers);

	}


	/*
	 * Notify casting about the casting cart changes
	 */
	public static function sendClientNotification($Client_Email_Address,$Message,$bcc_emails){
		$rb_agency_options_arr = get_option('rb_agency_options');
		$rb_agency_value_agencyname = $rb_agency_options_arr['rb_agency_option_agencyname'];
		$rb_agency_value_agencyemail = $rb_agency_options_arr['rb_agency_option_agencyemail'];

			// Mail it
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset=iso-8859-1';
		$headers[] = 'From: '. $rb_agency_value_agencyname .' <'. $rb_agency_value_agencyemail .'>';
		$bcc_emails_arr = explode(",",$bcc_emails);
		foreach ($bcc_emails_arr as $key) {
			$headers[] = 'Bcc: '.$key;
		}
		$isSent = wp_mail($Client_Email_Address, $rb_agency_value_agencyname." ".__(": Casting Cart",RBAGENCY_casting_TEXTDOMAIN)." ", $Message, $headers);

	}


	/*
	 * Notify casting about the new applicant for a job
	 */

	public static function sendClientNewJobNotification($Client_Email_Address,$Job_Name,$Message){
			$rb_agency_options_arr = get_option('rb_agency_options');
			$rb_agency_value_agencyname = $rb_agency_options_arr['rb_agency_option_agencyname'];
			$rb_agency_value_agencyemail = $rb_agency_options_arr['rb_agency_option_agencyemail'];

			// Mail it
		$headers = 'From: '. $rb_agency_value_agencyname.' <'. $rb_agency_value_agencyemail .'>' . "\r\n";
		$isSent = wp_mail($Client_Email_Address, $rb_agency_value_agencyname." ".__(": New Job Applicant for ",RBAGENCY_casting_TEXTDOMAIN)." ".$Job_Name, $Message, $headers);
	}


// end class
}

/* 
 * Casting Cart Actions
 */

if(isset($_REQUEST["action"]) && $_REQUEST['action'] == 'cartAdd' ) {
	// Process Cart
	$cart = RBAgency_Casting::cart_process();
}
if(isset($_POST["SendEmail"])){
	// Process Form
	$isSent = RBAgency_Casting::cart_send_process();
}
if(isset($_REQUEST["action"]) && $_REQUEST['action'] == 'cartEmpty' ) {
	// Empty Cart
	unset($_SESSION['cartArray']);
}

/*add_filter('wp_mail_from','custom_wp_mail_from');
function custom_wp_mail_from($email) {
  return get_bloginfo("admin_email");
}
 
add_filter('wp_mail_from_name','custom_wp_mail_from_name');
function custom_wp_mail_from_name($name) {
  return get_bloginfo("name");
}
*/