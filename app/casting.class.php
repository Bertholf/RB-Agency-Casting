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

			if (isset($_SESSION['cartArray']) && !empty($_SESSION['cartArray'])) {

				$cartArray = $_SESSION['cartArray'];
					$cartString = implode(",", array_unique($cartArray));
					$cartString = RBAgency_Common::clean_string($cartString);

				// Show Cart  
				$query = "SELECT  profile.*,media.* FROM ". table_agency_profile ." profile, ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1 AND profile.ProfileID IN (". $cartString .") ORDER BY profile.ProfileContactNameFirst ASC";
				$results = mysql_query($query) or  die( "<a href=\"?page=". $_GET['page'] ."&action=cartEmpty\" class=\"button-secondary\">". __("No profile selected. Try again", rb_agency_casting_TEXTDOMAIN) ."</a>"); //die ( __("Error, query failed", rb_agency_casting_TEXTDOMAIN ));
				$count = mysql_num_rows($results);
				echo "<div class=\"boxblock-container\" style=\"float: left; padding-top:24px; width: 49%; min-width: 500px;\">\n";
				echo "<div style=\"float: right; width: 100px; \"><a href=\"?page=". $_GET['page'] ."&action=cartEmpty\" class=\"button-secondary\">". __("Empty Cart", rb_agency_casting_TEXTDOMAIN) ."</a></div>";
				echo "<div style=\"float: left; line-height: 22px; font-family:Georgia; font-size:13px; font-style: italic; color: #777777; \">". __("Currently", rb_agency_casting_TEXTDOMAIN) ." <strong>". $count ."</strong> ". __("in Cart", rb_agency_casting_TEXTDOMAIN) ."</div>";
				echo "<div style=\"clear: both; border-top: 2px solid #c0c0c0; \" class=\"profile\">";

				if ($count == 1) {
					$cartAction = "cartEmpty";
				} elseif ($count < 1) {
					echo "". __("There are currently no profiles in the casting cart", rb_agency_casting_TEXTDOMAIN) .".";
					$cartAction = "cartEmpty";
				} else {
					$cartAction = "cartRemove";
				}
				
				while ($data = mysql_fetch_array($results)) {
					
					$ProfileDateUpdated = $data['ProfileDateUpdated'];
					echo "  <div style=\"position: relative; border: 1px solid #e1e1e1; line-height: 22px; float: left; padding: 10px; width: 210px; margin: 6px; \">";
					echo "    <div style=\"text-align: center; \"><h3>". stripslashes($data['ProfileContactNameFirst']) ." ". stripslashes($data['ProfileContactNameLast']) . "</h3></div>"; 
					echo "    <div style=\"float: left; width: 100px; height: 100px; overflow: hidden; margin-top: 2px; \"><img style=\"width: 100px; \" src=\"". rb_agency_UPLOADDIR ."". $data['ProfileGallery'] ."/". $data['ProfileMediaURL'] ."\" /></div>\n";
					echo "    <div style=\"float: left; width: 100px; height: 100px; overflow: scroll-y; margin-left: 10px; line-height: 11px; font-size: 9px; \">\n";

					if (!empty($data['ProfileDateBirth'])) {
						echo "<strong>Age:</strong> ". rb_agency_get_age($data['ProfileDateBirth']) ."<br />\n";
					}
					// TODO: ADD MORE FIELDS

					echo "    </div>";
					echo "    <div style=\"position: absolute; z-index: 20; top: 120px; left: 200px; width: 20px; height: 20px; overflow: hidden; \"><a href=\"?page=". $_GET['page'] ."&actiontwo=cartRemove&action=cartAdd&RemoveID=". $data['ProfileID'] ."&\" title=\"". __("Remove from Cart", rb_agency_casting_TEXTDOMAIN) ."\"><img src=\"". rb_agency_BASEDIR ."style/remove.png\" style=\"width: 20px; \" alt=\"". __("Remove from Cart", rb_agency_casting_TEXTDOMAIN) ."\" /></a></div>";
					echo "    <div style=\"clear: both; \"></div>";
					echo "  </div>";
				}
				mysql_free_result($results);
				echo "  <div style=\"clear: both;\"></div>\n";
				echo "</div>";
				
				if (($cartAction == "cartEmpty") || ($cartAction == "cartRemove")) {
				echo "<a name=\"compose\">&nbsp;</a>"; 
				echo "<div class=\"boxblock\">\n";
				echo "   <h3>". __("Cart Actions", rb_agency_casting_TEXTDOMAIN) ."</h3>\n";
				echo "   <div class=\"inner\">\n";
				echo "      <a href=\"?page=rb_agency_searchsaved&action=searchSave\" title=\"". __("Save Search & Email", rb_agency_casting_TEXTDOMAIN) ."\" class=\"button-primary\">". __("Save Search & Email", rb_agency_casting_TEXTDOMAIN) ."</a>\n";
				echo "      <a href=\"?page=rb_agency_search&action=massEmail#compose\" title=\"". __("Mass Email", rb_agency_casting_TEXTDOMAIN) ."\" class=\"button-primary\">". __("Mass Email", rb_agency_casting_TEXTDOMAIN) ."</a>\n";
				echo "      <a href=\"#\" onClick=\"window.open('". get_bloginfo("url") ."/profile-print/?action=castingCart&cD=1','mywindow','width=930,height=600,left=0,top=50,screenX=0,screenY=50,scrollbars=yes')\" title=\"Quick Print\" class=\"button-primary\">". __("Quick Print", rb_agency_casting_TEXTDOMAIN) ."</a>\n";
				echo "      <a href=\"#\" onClick=\"window.open('". get_bloginfo("url") ."/profile-print/?action=castingCart&cD=0','mywindow','width=930,height=600,left=0,top=50,screenX=0,screenY=50,scrollbars=yes')\" title=\"Quick Print - Without Details\" class=\"button-primary\">". __("Quick Print", rb_agency_casting_TEXTDOMAIN) ." - ". __("Without Details", rb_agency_casting_TEXTDOMAIN) ."</a>\n";
				echo "   </div>\n";
				echo "</div>\n";
				} // Is Cart Empty 

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
		$wpdb->query("INSERT INTO " . table_agency_searchsaved." (SearchProfileID,SearchTitle) VALUES('".$cartString."','".$SearchMuxSubject."')") or die(mysql_error());
					
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
			$qProfiles =  mysql_query($query);
			$data = mysql_fetch_array($qProfiles);
			$query = "SELECT * FROM ". table_agency_profile ." profile, ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1 AND profile.ProfileID IN (".$data['SearchProfileID'].") ORDER BY ProfileContactNameFirst ASC";
			$results = mysql_query($query);
			$count = mysql_num_rows($results);
			while ($data2 = mysql_fetch_array($results)) {
				$profileimage .= " <div style=\"background:black; color:white;float: left; max-width: 100px; height: 150px; margin: 2px; overflow:hidden;  \">";
				$profileimage .= " <div style=\"margin:3px;max-width:250px; max-height:300px; overflow:hidden;\">";
				$profileimage .= stripslashes($data2['ProfileContactNameFirst']) ." ". stripslashes($data2['ProfileContactNameLast']);
				$profileimage .= "<br /><a href=\"". rb_agency_PROFILEDIR . $data2['ProfileGallery'] ."/\" target=\"_blank\">";
				$profileimage .= "<img style=\"max-width:130px; max-height:150px; \" src=\"".rb_agency_UPLOADDIR ."". $data2['ProfileGallery'] ."/". $data2['ProfileMediaURL'] ."\" /></a>";
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
			if($isSent){?>
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
				$results2 = mysql_query($query);
				$count = mysql_num_rows($results2);
				$pos = 0;
				$recipient = "";
				while ($data = mysql_fetch_array($results2)) {
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
				echo "        <h3>". __("Compose Email", rb_agency_casting_TEXTDOMAIN) ."</h3>\n";
				echo "        <div class=\"inner\">\n";
				/*if($msg!=""){
				echo "          <div id=\"message\" class=\"updated\"><p>Email Messages successfully sent!</p></div>";
				}*/
				echo "          <strong>Recipient:</strong><br/><textarea name=\"MassEmailRecipient\" style=\"width:100%;\">".$rb_agency_value_agencyemail."</textarea><br/>";
				echo "          <strong>Bcc:</strong><br/><textarea name=\"MassEmailBccEmail\" style=\"width:100%;\">".$recipient."</textarea><br/>";
				echo "          <strong>Subject:</strong> <br/><input type=\"text\" name=\"MassEmailSubject\" style=\"width:100%\"/>";
				echo "          <br/>";
			/*	echo "          <strong>Message:</strong><br/>     <textarea name=\"MassEmailMessage\"  style=\"width:100%;height:300px;\">this message was sent to you by ".$rb_agency_value_agencyname." ".network_site_url( '/' )."</textarea>";*/
				//Adding Wp editor
				$content = "Add Message Here<br />Click the following link (or copy and paste it into your browser):<br />
[link-place-holder]<br /><br />This message was sent to you by:<br />[site-title]<br />[site-url]";
				$editor_id = 'MassEmailMessage';
				wp_editor( $content, $editor_id,array("wpautop"=>false,"tinymce"=>true) );
				
				echo "          <input type=\"submit\" value=\"". __("Send Email", rb_agency_casting_TEXTDOMAIN) . "\" name=\"SendEmail\"class=\"button-primary\" />\n";
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
			
			global $wbdp;
			
			if($id==NULL || $id=="" || empty($id)) return "";
			$job_type = mysql_query("SELECT * FROM " . table_agency_casting_job_type . " WHERE Job_Type_ID = " . $id) or die(mysql_error());
			$type_name = "";
			if(mysql_num_rows($job_type) > 0){
				$t = mysql_fetch_row($job_type,MYSQL_BOTH);
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
						}else{
							$d = explode("/",$arr);
							$values = str_replace("-",", ",$d[1]);
							$details .= self::rb_get_custom_name($d[0]) . " = " . $values . "<br>";
						} 
					}

				}
				if($return_array){
					return $return_arr;
				}else{
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
		public static function rb_casting_ismodel($user_linked = NULL, $field_name = NULL){

			global $wpdb;

			if($user_linked == NULL || $user_linked == "" || empty($user_linked)) {
				return false;
			}

			$get_id = $wpdb->get_row( "SELECT * FROM " . table_agency_profile . " WHERE ProfileUserLinked = " . $user_linked ) ;

			if(count($get_id) > 0){
				if($field_name == NULL){
					return $get_id->ProfileID;
				} else {
					return @$get_id->$field_name;
				}
			}

			return false;

		}

		/*
		 * check if user is casting agent
		 */
		public static function rb_casting_is_castingagent($user_linked = NULL){

			global $wpdb;

			if($user_linked == NULL || $user_linked == "" || empty($user_linked)) {
				return false;
			}

			$get_id = $wpdb->get_row( "SELECT CastingID FROM " . table_agency_casting . " WHERE CastingUserLinked = " . $user_linked ) ;

			if(count($get_id) > 0){
				return $get_id->CastingID;
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

				$q = mysql_query("SELECT * FROM ". table_agency_customfields ." WHERE ProfileCustomID = ".$key);
				$ProfileCustomType = mysql_fetch_assoc($q);

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
						$custom_fields[$x[0]] = $x[1]; 						
					}				
				}
				
				return $custom_fields;
		
		}
		
		/*
		 * actual loading of criteria fields
		 */
		 public static function load_criteria_fields($data = NULL){
			 
				global $wpdb;
				
				$custom_fields = self::load_custom_types($data);
				
				echo "<script type='text/javascript'>
						function num_only(text) {
							var validationRegex = /[1-9]/g;
							if (!validationRegex.test(text.value)) {
								alert('Please enter only numbers.');
							}
						}	  
					  </script>";
				
				$query1 = "SELECT ProfileCustomID, ProfileCustomTitle, ProfileCustomType, ProfileCustomOptions, ProfileCustomOrder, ProfileCustomView, ProfileCustomShowGender, ProfileCustomShowProfile, ProfileCustomShowSearch, ProfileCustomShowLogged, ProfileCustomShowAdmin FROM ". table_agency_customfields ." WHERE ProfileCustomView = 0  ORDER BY ProfileCustomOrder ASC";
									$results1 = mysql_query($query1);
									$count1 = mysql_num_rows($results1);
									$pos = 0;
				
				$query2 = "SELECT ProfileGender,ProfileUserLinked  FROM ".table_agency_profile." WHERE ProfileUserLinked = '".rb_agency_get_current_userid()."' ";
									$results2 = mysql_query($query2);
									  $dataList2 = mysql_fetch_assoc($results2); 
									$count2 = mysql_num_rows($results2);
									
				while ($data1 = mysql_fetch_array($results1)) { 
				
					$ProfileCustomID = $data1['ProfileCustomID'];
					$ProfileCustomTitle = $data1['ProfileCustomTitle'];
					$ProfileCustomType = $data1['ProfileCustomType'];
					$ProfileCustomValue = $data1['ProfileCustomValue'];
								
					if($ProfileCustomType!=4)	{
						
					   /*
						* Opening of Field or Div
						*/
						if($ProfileCustomType == 7 OR $ProfileCustomType == 5 OR $ProfileCustomType == 6){
							echo "<fieldset class=\"search-field multi\">";
						} else {
							echo "<div class=\"search-field single\">";	
						}
						
							   /*
								* Custom Field Contents Goes here
								*/
								
								// SET Label for Measurements
								// Imperial(in/lb), Metrics(ft/kg)
								$rb_agency_options_arr = get_option('rb_agency_options');
								$rb_agency_option_unittype  = $rb_agency_options_arr['rb_agency_option_unittype'];
								$measurements_label = "";
								/*
								0- metric
								1 - cm
								2- kg
								3 - inches/feet
								1-imperials	
								1- inches
								2- pounds
								3-inches/feet
								*/
								if ($ProfileCustomType == 7) { //measurements field type
									if($rb_agency_option_unittype ==0){ // 0 = Metrics(ft/kg)
										if($data1['ProfileCustomOptions'] == 1) {
											$measurements_label  ="<em> (cm)</em>";
										} elseif($data1['ProfileCustomOptions'] == 2) {
											$measurements_label  ="<em> (kg)</em>";
										} elseif($data1['ProfileCustomOptions'] == 3) {
											$measurements_label  ="<em> (ft/in)</em>";
										}
									} elseif($rb_agency_option_unittype ==1){ //1 = Imperial(in/lb)
										if($data1['ProfileCustomOptions'] == 1){
											$measurements_label  ="<em> (in)</em>";					   
										} elseif($data1['ProfileCustomOptions'] == 2) {
											$measurements_label  ="<em> (lb)</em>";		
										} elseif($data1['ProfileCustomOptions'] == 3) {
											$measurements_label  ="<em> (ft/in)</em>";
										}
									}		
								}
							
								if($ProfileCustomType == 7 OR $ProfileCustomType == 5 OR $ProfileCustomType == 6){
									echo "<legend>".$data1['ProfileCustomTitle'].$measurements_label."</legend>";
								} else {
									echo "<label for=\"ProfileCustomID". $data1['ProfileCustomID'] ."\">". $data1['ProfileCustomTitle']."</label>";							 
								}
							
								if ($ProfileCustomType == 1) { //TEXT		
									echo "<div><input type=\"text\" name=\"ProfileCustomID". $data1['ProfileCustomID'] ."\" value=\"".$custom_fields[$data1['ProfileCustomID']]."\" /></div>";
								} elseif ($ProfileCustomType == 2) { // Min Max
								   
									$ProfileCustomOptions_String = str_replace(",",":",strtok(strtok($data1['ProfileCustomOptions'],"}"),"{"));
									list($ProfileCustomOptions_Min_label,$ProfileCustomOptions_Min_value,$ProfileCustomOptions_Max_label,$ProfileCustomOptions_Max_value) = explode(":",$ProfileCustomOptions_String);
								 
									if(!empty($ProfileCustomOptions_Min_value) && !empty($ProfileCustomOptions_Max_value)){
										echo "<div>";
										echo "	<label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Min", rb_agency_TEXTDOMAIN) . "&nbsp;&nbsp;</label>";
										echo "	<div><input type=\"text\" name=\"ProfileCustomID". $data1['ProfileCustomID'] ."\" value=\"". $ProfileCustomOptions_Min_value ."\" onkeyup='num_only(this);' /></div>";
										echo "</div>";
										echo "<div>";
										echo "	<label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Max", rb_agency_TEXTDOMAIN) . "&nbsp;&nbsp;</label>";
										echo "	<div><input type=\"text\" name=\"ProfileCustomID". $data1['ProfileCustomID'] ."\" value=\"". $ProfileCustomOptions_Max_value ."\"  onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");'  /></div>";					
										echo "</div>";
									} else {
										echo "<div>";
										echo "	<label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Min", rb_agency_TEXTDOMAIN) . "&nbsp;&nbsp;</label>";
										echo "	<div><input type=\"text\" name=\"ProfileCustomID". $data1['ProfileCustomID'] ."\" value=\"".$custom_fields[$data1['ProfileCustomID']]."\"  onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");' /></div>";
										echo "</div>";
										echo "<div>";
										echo "	<label for=\"ProfileCustomLabel_min\" style=\"text-align:right;\">". __("Max", rb_agency_TEXTDOMAIN) . "&nbsp;&nbsp;</label>";
										echo "	<div><input type=\"text\" name=\"ProfileCustomID". $data1['ProfileCustomID'] ."\" value=\"".$custom_fields[$data1['ProfileCustomID']]."\"  onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");' /></div>";
										echo "</div>";		
									}
								 
								} elseif ($ProfileCustomType == 3) { // SELECT
									
									list($option1,$option2) = explode(":",$data1['ProfileCustomOptions']);	
										
									$data = explode("|",$option1);
									$data2 = explode("|",$option2);				
								 
									echo "<label>".$data[0]."</label>";
									
									echo "<div><select name=\"ProfileCustomID". $data1['ProfileCustomID'] ."\">";
									echo "<option value=\"\">--</option>";
									  
										foreach($data as $val1){
											
											if($val1 != end($data) && $val1 != $data[0]){
											
												 $isSelected = "";
												if($custom_fields[$data1['ProfileCustomID']]==$val1){
													$isSelected = "selected=\"selected\"";
													echo "<option value=\"".$val1."\" ".$isSelected .">".$val1."</option>";
												} else {
													echo "<option value=\"".$val1."\" >".$val1."</option>"; 
												}
										
											}
										}
									echo "</select></<div>";
										
								} elseif ($ProfileCustomType == 4) {
									echo "<div><textarea name=\"ProfileCustomID". $data1['ProfileCustomID'] ."\">". $custom_fields[$data1['ProfileCustomID']] ."</textarea></div>";
								} elseif ($ProfileCustomType == 5) {
							
									$array_customOptions_values = explode("|",$data1['ProfileCustomOptions']);
							
										foreach($array_customOptions_values as $val){
											if(isset($custom_fields[$data1['ProfileCustomID']])){ 
							
												$dataArr = explode(",",implode(",",explode("','",$custom_fields[$data1['ProfileCustomID']])));
												if(in_array($val,$dataArr,true)){
													echo "<label><input type=\"checkbox\" checked=\"checked\" value=\"". $val."\"  name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\" />";
													echo "<span>&nbsp;&nbsp;". $val."</span></label>";
												} else {
													if($val !=""){	
														echo "<label><input type=\"checkbox\" value=\"". $val."\"  name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\" />";
														echo "<span>&nbsp;&nbsp;". $val."</span></label>";	
													}
												}
											} else {
												if($val !=""){	
													echo "<label><input type=\"checkbox\" value=\"". $val."\"  name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\" />";
													echo "<span>&nbsp;&nbsp;". $val."</span></label>";	
												}
											}
										}
					
									echo "<input type=\"hidden\" value=\"\" name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\"/>";				   
								}
								elseif ($ProfileCustomType == 6) {
							
									$array_customOptions_values = explode("|",$data1['ProfileCustomOptions']);
							
									foreach($array_customOptions_values as $val) {
							
										if(isset($custom_fields[$data1['ProfileCustomID']]) && $custom_fields[$data1['ProfileCustomID']] !=""){ 
							
											$dataArr = explode(",",implode(",",explode("','",$custom_fields[$data1['ProfileCustomID']])));
							
											if(in_array($val,$dataArr) && $val !="") {
												echo "<label><input type=\"radio\" checked=\"checked\" value=\"". $val."\"  name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\" />";
												echo "<span>&nbsp;&nbsp;". $val."</span></label>";
											} else {
												if($val !="") {
													echo "<label><input type=\"radio\" value=\"". $val."\"  name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\" />";
													echo "<span>&nbsp;&nbsp;". $val."</span></label>";	
												}
											}
										} else {
											if($val !="") {
												echo "<label><input type=\"radio\" value=\"". $val."\"  name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\" />";
												echo "<span>&nbsp;&nbsp;". $val."</span></label>";	
											}
										}
									}
									echo "<input type=\"hidden\" value=\"\" name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\"/>";	       
								}
								
								elseif ($ProfileCustomType == 7) {
							
									// get the values from db				
									list($min_val,$max_val) =  explode("-", $custom_fields[$data1['ProfileCustomID']]);
					
										if($data1['ProfileCustomTitle']=="Height" && $data1['ProfileCustomOptions']==3){
					
											echo "<div><label>Min</label><select name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\">\n";
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
												echo " <option value=\"". $i ."\" ". selected($min_val, $i) .">". $heightfeet ." ft ". $heightinch ." in</option>\n";
													  $i++;
													}
												echo " </select></div>\n";
					
												echo "<div><label>Max</label><select name=\"ProfileCustomID". $data1['ProfileCustomID'] ."[]\">\n";
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
												echo " <option value=\"". $i ."\" ". selected($max_val, $i) .">". $heightfeet ." ft ". $heightinch ." in</option>\n";
													  $i++;
													}
												echo " </select></div>\n";
							
										} else {
											
											// for other search
											echo "<div><label for=\"ProfileCustomID".$data1['ProfileCustomID']
											."_min\">Min</label><input value=\""
											.$min_val
											."\" class=\"stubby\" type=\"text\" name=\"ProfileCustomID"
											.$data1['ProfileCustomID']."[]\"  onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");' /></div>";
					
											echo "<div><label for=\"ProfileCustomID".$data1['ProfileCustomID']
											."_max\">Max</label><input value=\"".$max_val
											."\" class=\"stubby\" type=\"text\" name=\"ProfileCustomID".$data1['ProfileCustomID']."[]\"  onkeyup='num_only(this); this.value = this.value.replace(/[^0-9]+/g, \"\");' /></div>";											
										}
									// END - RYAN EDITING
								}
								
						/*
						 * Close Div or FieldSet
						 */
						if($ProfileCustomType==7 || $ProfileCustomType==5 || $ProfileCustomType == 6){
							echo "</fieldset>";
						} else {
							echo "</div>";	
						}
					}
					
				}
				
				mysql_free_result($results1);
				mysql_free_result($results2); 	
			 
			 die();
		 
		 }
		 
		 /*
		  * get model details
		  */
		  public static function rb_casting_get_model_details($id = NULL){
			   
			   global $wpdb;
			   
			   if($id == NULL) return "";
			   
			   $get_name = $wpdb->get_row("SELECT * FROM " . table_agency_profile . " WHERE ProfileUserLinked = " . $id);	 

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
			   
			   if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$table."'"))==1) { 
				   
				   if(!empty($where) && $where != "" && $where != NULL){
				   		$get_row_count = mysql_query("SELECT COUNT(1) FROM " . $table . " " . $where) or die(mysql_error());	 
				   } else {
				   		$get_row_count = mysql_query("SELECT COUNT(1) FROM " . $table) or die(mysql_error());	 
				   }
				   
				   $total = mysql_fetch_array($get_row_count);
				   
				   $ceiling = ceil($total[0] / $count_per_page);
				   
				   if($ceiling > 1){
					    
						echo "<div style='padding:12px;'>";
						
						if(($ceiling - $selected_page) != ($ceiling - 1) && ($selected_page != 0)){
					    	echo "<a href='".$link.($selected_page-1)."' style='margin:12px'>prev</a>";
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
							echo "<a href='".$link.$next_link."' style='margin:12px'>next</a>";
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
										 " WHERE Job_Userlinked = " . $applicants->Job_UserLinked . " AND Job_ID = " . $JobID ) or die(mysql_error());
			 
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
										 " WHERE Job_Userlinked = " . $applicants->Job_UserLinked . " AND Job_ID = " . $JobID ) or die(mysql_error());
			 
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
										 " WHERE Job_Userlinked = " . $applicants->Job_UserLinked . " AND Job_ID = " . $JobID ) or die(mysql_error());
			 
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
				 
				 $profile_id = "SELECT ProfileID, ProfileGallery FROM ". table_agency_profile .
				 			   " WHERE ProfileUserLinked = " . $PID;
				 
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
			  
					$message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "\r\n\r\n";  
					$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";  
					$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";  
			  
					@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);  
			  
					if ( empty($plaintext_pass) )  
						return;  
					$message  = __('Hi there,') . "\r\n\r\n";  
					$message .= sprintf(__("Thanks for joining %s! Here's how to log in:"), get_option('blogname')) . "\r\n\r\n"; 
					$message .= get_option('home') ."/casting-login/\r\n"; 
					$message .= sprintf(__('Username: %s'), $user_login) . "\r\n"; 
					$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n\r\n"; 
					$message .= sprintf(__('If you have any problems, please contact us at %s.'), get_option('admin_email')) . "\r\n\r\n"; 
					$message .= __('Regards,')."\r\n";
					$message .= get_option('blogname') . __(' Team') ."\r\n"; 
					$message .= get_option('home') ."\r\n"; 
			 
					$headers = 'From: '. get_option('blogname') .' <'. get_option('admin_email') .'>' . "\r\n";
					wp_mail($user_email, sprintf(__('%s Registration Successful! Login Details'), get_option('blogname')), $message, $headers);  
	  
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

						if(isset($talent) && $talent ){ 
							
							if(strpos("none", $JobID) > -1 ){
								
								$talent_arr = trim($talent,";");
								$talent_arr = explode(";",$talent_arr);
								foreach($talent_arr as $talent){
									
									$data = explode(":",$talent);
									$talent = self::rb_casting_ismodel($data[1], "ProfileID");
									$JobID = $data[0];
									
									$query_castingcart = mysql_query("SELECT * FROM ". table_agency_castingcart."  WHERE CastingCartTalentID=".$talent."  AND CastingCartProfileID = ".rb_agency_get_current_userid() . " AND CastingJobID = " . $JobID) or die(mysql_error());
									$count_castingcart = mysql_num_rows($query_castingcart);
									$datas_castingcart = mysql_fetch_assoc($query_castingcart);
				
									if($count_castingcart<=0){ //if not exist insert favorite!
										$insert = "INSERT INTO " . table_agency_castingcart . " SET CastingCartProfileID = " .rb_agency_get_current_userid()  . ", CastingCartTalentID = " . $talent . ", CastingJobID = " . $JobID; 
										mysql_query($insert) or die(mysql_error());
									} else { // favorite model exist, now delete!
										mysql_query("DELETE FROM  ". table_agency_castingcart."  WHERE CastingCartTalentID = ".$talent."  AND CastingCartProfileID = ".rb_agency_get_current_userid()." AND CastingJobID = " . $JobID) or die(mysql_error());
									}
									
								}								

								$arr = array( "data" => "success");
								echo json_encode($arr);
							
							} else {
								
								$talent = self::rb_casting_ismodel($talent, "ProfileID");
								$query_castingcart = mysql_query("SELECT * FROM ". table_agency_castingcart."  WHERE CastingCartTalentID=".$talent."  AND CastingCartProfileID = ".rb_agency_get_current_userid() . " AND CastingJobID = " . $JobID) or die(mysql_error());
								$count_castingcart = mysql_num_rows($query_castingcart);
								$datas_castingcart = mysql_fetch_assoc($query_castingcart);
			
								if($count_castingcart<=0){ //if not exist insert favorite!
									$insert = "INSERT INTO " . table_agency_castingcart . " SET CastingCartProfileID = " .rb_agency_get_current_userid()  . ", CastingCartTalentID = " . $talent . ", CastingJobID = " . $JobID; 
									mysql_query($insert) or die(mysql_error());
									$arr = array( "data" => "inserted");
									echo json_encode($arr);
								} else { // favorite model exist, now delete!
									mysql_query("DELETE FROM  ". table_agency_castingcart."  WHERE CastingCartTalentID = ".$talent."  AND CastingCartProfileID = ".rb_agency_get_current_userid(). " AND CastingJobID = " . $JobID)  or die("error");
									$arr = array("data" => "deleted");
									echo json_encode($arr);							
								}
							
							}

						}
					}

					else {
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
						
							$talent = self::rb_casting_ismodel($talent, "ProfileID");
							$query_castingcart = mysql_query("SELECT * FROM ". table_agency_castingcart."  WHERE CastingCartTalentID=".$talent."  AND CastingCartProfileID = ".rb_agency_get_current_userid() . " AND CastingJobID = " . $JobID) or die(mysql_error());
							$count_castingcart = mysql_num_rows($query_castingcart);
							
							if($count_castingcart > 0){
								return true;
							}
	
						}

					}
					
					return false;
			
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
