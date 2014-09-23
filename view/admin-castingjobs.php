<?php

$siteurl = get_option('siteurl');
	// Casting Class
	include (dirname(__FILE__) ."/../app/casting.class.php");
    include(rb_agency_BASEREL ."ext/easytext.php");

	global $wpdb;

	// Get Options
	$rb_agency_options_arr = get_option('rb_agency_options');
		$rb_agency_option_agencyname		= $rb_agency_options_arr['rb_agency_option_agencyname'];
		$rb_agency_option_agencyemail	= $rb_agency_options_arr['rb_agency_option_agencyemail'];
		$rb_agency_option_agencyheader	= $rb_agency_options_arr['rb_agency_option_agencyheader'];

	// Declare Hash
	$SearchMuxHash	=  isset($_GET["SearchMuxHash"])?$_GET["SearchMuxHash"]:""; // Set Hash
	$hash =  "";

	wp_register_script('jquery_latest', plugins_url('../js/jquery-1.11.0.min.js', __FILE__),false,1,true); 
	wp_enqueue_script('jquery_latest');
	wp_enqueue_script( 'jqueryui',  plugins_url('../js/jquery-ui.js', __FILE__),false,1,true); 
	wp_register_script('jquery-timepicker',  plugins_url('../js/jquery-timepicker.js', __FILE__),false,1,true); 
	wp_enqueue_script('jquery-timepicker');
	wp_register_style( 'timepicker-style', plugins_url('../css/timepicker-addon.css', __FILE__) );
	wp_enqueue_style( 'timepicker-style' );

    echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">';
	
	/*
	 * Display Inform Talent
	 */
	if(isset($_SESSION['cartArray']) || isset($_GET["action"]) && $_GET["action"] == "informTalent" || !isset($_GET["action"])){
     ?>
	    
	

	<div style="clear:both"></div>

		<div class="wrap">
		 <div id="rb-overview-icon" class="icon32"></div>
		 <h2>Casting Jobs</h2>

		
		 <?php 
		 // Delete selected profiles
		if(isset($_POST["action2"]) && $_POST["action2"] == "deleteprofile"){
									  	$arr_selected_profile = array();
									  	$data = current($wpdb->get_results($wpdb->prepare("SELECT * FROM ".table_agency_casting_job." WHERE Job_ID= %d ", $_GET["Job_ID"])));
										$arr_profiles = explode(",",$data->Job_Talents);
									  		
										foreach($_POST as $key => $val ){
									  		 if(strpos($key, "profiletalent") !== false){
									  		 		$wpdb->query($wpdb->prepare("DELETE FROM ".table_agency_castingcart_profile_hash." WHERE CastingProfileHashProfileID = %s",$val));
									  	
									  		 	  	array_push($arr_selected_profile, $val);
													
									  		 }
									  	}
									  	$new_set_profiles = implode(",",array_diff($arr_profiles,$arr_selected_profile));
									  	$wpdb->query($wpdb->prepare("UPDATE ".table_agency_casting_job." SET Job_Talents=%s WHERE Job_ID = %d", $new_set_profiles, $_GET["Job_ID"]));
	 									echo ('<div id="message" class="updated"><p>'.count($arr_selected_profile).(count($arr_selected_profile) <=1?" profile":" profiles").' removed successfully!</p></div>');
		}
		 // Delete selected profiles
		if(isset($_POST["action2"]) && $_POST["action2"] == "deletecastingprofile"){
									  	$arr_selected_profile = array();
									  	$data = current($wpdb->get_results($wpdb->prepare("SELECT * FROM ".table_agency_casting_job." WHERE Job_ID= %d ", $_GET["Job_ID"])));
										$arr_profiles = explode(",",$data->Job_Talents);
									    			 
										foreach($_POST as $key => $val ){
									  		 if(strpos($key, "profiletalent") !== false){
									  		 		$wpdb->query($wpdb->prepare("DELETE FROM ".table_agency_castingcart." WHERE CastingCartTalentID = %s",$val));
									  	
									  		 	  	array_push($arr_selected_profile, $val);
									  		 	  	$profile_user_linked = $wpdb->get_row("SELECT ProfileUserLinked FROM ".table_agency_profile." WHERE ProfileID = '".$val."' ");
									  		 	  	$wpdb->query("DELETE FROM ".table_agency_casting_job_application." WHERE Job_ID = '".$_GET["Job_ID"]."' AND Job_UserLinked = '".$profile_user_linked->ProfileUserLinked."'");
										
													
									  		 }
									  	}


									  	echo ('<div id="message" class="updated"><p>'.count($arr_selected_profile).(count($arr_selected_profile) <=1?" profile":" profiles").' removed successfully!</p></div>');
		}
		// Remove to Profile Casting
		if(isset($_POST["addprofilestocasting"])){

			$profiles = explode(",",$_POST["addprofilestocasting"]);
			  $job_id = 0;
			$agent_id = 0;
			

			if(isset($_GET["Job_ID"]) && !isset($_POST["addtoexisting"])){
				$existing_profiles = $wpdb->get_results("SELECT CastingCartTalentID FROM ".table_agency_castingcart." WHERE CastingJobID = '".$_GET["Job_ID"]."'",ARRAY_A);
				$job_id  = $_GET["Job_ID"];
				$agent_id = $_POST["Agent_ID"];
			}elseif(isset($_POST["addtoexisting"])){
				list($job_id,$agent_id) = explode("-",$_POST["Job_ID"]);
				$existing_profiles = $wpdb->get_results("SELECT CastingCartTalentID FROM ".table_agency_castingcart." WHERE CastingJobID = '".$job_id."'",ARRAY_A);
			}
			$arr_profiles = array();
			foreach ($existing_profiles as $key) {
				array_push($arr_profiles, $key["CastingCartTalentID"]);
			}

			$sql = "";
			foreach ($profiles as $key) {
				if(!in_array($key,$arr_profiles)){
					$sql .="('','".$agent_id."','".$key."','".$job_id."')";
					if(end($profiles) != $key){
						$sql .= ",";
					}
				}
				/*$wpdb->get_results("SELECT * FROM ".table_agency_casting_job_application." WHERE Job_ID='".$job_id."' AND Job_UserLinked = '".$key."'");
				$is_applied = $wpdb->num_rows;
				if($is_applied <= 0){
					$get_profile_user_linked = $wpdb->get_row("SELECT ProfileUserLinked FROM ".table_agency_profile." WHERE ProfileID ='".$key."' ");
				
					$wpdb->query("INSERT INTO  " . table_agency_casting_job_application . " (Job_ID, Job_UserLinked) VALUES('".$job_id."','".$get_profile_user_linked->ProfileUserLinked."') ");		
				}*/

			}
			if(!empty($sql)){
			//$wpdb->query("INSERT INTO " . table_agency_casting_job_application . "  (Job_ID, Job_UserLinked) VALUES  (".$job_id.",". $current_user->ID .")");
			

			    $wpdb->query("INSERT INTO ".table_agency_castingcart."(CastingCartID, CastingCartProfileID, CastingCartTalentID,CastingJobID) VALUES".$sql);
				echo ('<div id="message" class="updated"><p>'.count($profiles).(count($profiles) <=1?" profile":" profiles").' successfully added to casting cart!</p></div>');
			}

		}
		// Add selected profiles
		if(isset($_POST["addprofiles"])){

			 if(isset($_GET["action2"]) && $_GET["action2"] == "addnew"){
			 		$profiles = $_POST["addprofiles"];
										
			 		    if(strpos($profiles,",") !== false){
								$profiles = explode(",",$profiles);
								foreach ($profiles as $key) {
									array_push($_SESSION["cartArray"],$key);
								}
						}else{
							  array_push($_SESSION["cartArray"],$profiles);
						}
						
			}else{
										$data = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".table_agency_casting_job." WHERE Job_ID= %d ", $_GET["Job_ID"]));
									  	$add_new_profiles = $data->Job_Talents.",".$_POST["addprofiles"];
									 	$castingHash = $wpdb->get_row("SELECT * FROM ".table_agency_casting_job." WHERE Job_ID='".$_GET["Job_ID"]."'");
									
										$profiles = $_POST["addprofiles"];
										
										if(strpos($profiles,",") !== false){
											$profiles = explode(",",$profiles);
											foreach($profiles as $profileid){
												 	$hash_profile_id = RBAgency_Common::generate_random_string(20,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
									
												$sql = "INSERT INTO ".table_agency_castingcart_profile_hash."
												(
													CastingProfileHashID,
													CastingProfileHashJobID,
													CastingProfileHashProfileID,
													CastingProfileHash
												)  VALUES(
													'',
													'".$castingHash->Job_Talents_Hash."',
													'".$profileid."',
													'".$hash_profile_id."'
												)";
												$wpdb->query($sql);

												$results = $wpdb->get_row("SELECT ProfileContactPhoneCell,ProfileContactEmail, ProfileID FROM ".table_agency_profile." WHERE ProfileID IN(".(!empty($profileid)?$profileid:"''").")",ARRAY_A);
												if(!empty( $results )){
													RBAgency_Casting::sendText(array($results["ProfileContactPhoneCell"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$castingHash->Job_Talents_Hash."/".$hash_profile_id);
													RBAgency_Casting::sendEmail(array($results["ProfileContactEmail"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$castingHash->Job_Talents_Hash."/".$hash_profile_id);
												}
												
											}
										}else{
											 	$hash_profile_id = RBAgency_Common::generate_random_string(20,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
												$profileid = str_replace(",","",(isset($_POST["addprofiles"])?$_POST["addprofiles"]:""));
											$sql = "INSERT INTO ".table_agency_castingcart_profile_hash."
												(
													CastingProfileHashID,
													CastingProfileHashJobID,
													CastingProfileHashProfileID,
													CastingProfileHash
												) 
												VALUES
												(
												'',
												'".$castingHash->Job_Talents_Hash."',
												'".$profileid."',
												'".$hash_profile_id."'
												)";
												$wpdb->query($sql);
												 $results = $wpdb->get_row("SELECT ProfileContactPhoneCell,ProfileContactEmail, ProfileID FROM ".table_agency_profile." WHERE ProfileID IN(".(!empty($profileid)?$profileid:"''").")",ARRAY_A);
												if(!empty( $results )){
													RBAgency_Casting::sendText(array($results["ProfileContactPhoneCell"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$castingHash->Job_Talents_Hash."/".$hash_profile_id);
													RBAgency_Casting::sendEmail(array($results["ProfileContactEmail"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$castingHash->Job_Talents_Hash."/".$hash_profile_id);
												}
												
										}

									
									
										$wpdb->query($wpdb->prepare("UPDATE ".table_agency_casting_job." SET Job_Talents=%s WHERE Job_ID = %d", implode(",",array_unique(explode(",",$add_new_profiles))), $_GET["Job_ID"]));
	 									echo ('<div id="message" class="updated"><p>Added successfully!</p></div>');
	 			}
	
		}
		// Insert Profiles to Casting Job
		  if(isset($_POST["action2"]) && $_POST["action2"] =="add"){
          	   	if (!isset($_GET["Job_ID"])) {

									$cartArray = isset($_SESSION['cartArray'])?$_SESSION['cartArray']:array();
									$cartString = implode(",", array_unique($cartArray));
									$cartString = RBAgency_Common::clean_string($cartString);
									$hash = RBAgency_Common::generate_random_string(10,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
									$sql = "INSERT INTO ".table_agency_casting_job." (
												Job_Title, 
												Job_Text,
												Job_Date_Start,
												Job_Date_End,
												Job_Location,
												Job_Region,
												Job_Offering,
												Job_Talents,
												Job_Visibility,
												Job_Criteria,
												Job_Type,
												Job_Talents_Hash,	
												Job_Audition_Date_Start,
												Job_Audition_Date_End,
												Job_Audition_Venue,
												Job_Audition_Time,
												Job_UserLinked,
												Job_Date_Created
										)
										VALUES(
												'".esc_attr($_POST["Job_Title"])."', 
												'".esc_attr($_POST["Job_Text"])."',
												'".esc_attr($_POST["Job_Date_Start"])."',
												'".esc_attr($_POST["Job_Date_End"])."',
												'".esc_attr($_POST["Job_Location"])."',
												'".esc_attr($_POST["Job_Region"])."',
												'".esc_attr($_POST["Job_Offering"])."',
												'".$cartString."',
												'".esc_attr($_POST["Job_Visibility"])."',
												'".esc_attr($_POST["Job_Criteria"])."',
												'".esc_attr($_POST["Job_Type"])."',
												'".$hash."',	
												'".esc_attr($_POST["Job_Audition_Date_Start"])."',
												'".esc_attr($_POST["Job_Audition_Date_End"])."',
												'".esc_attr($_POST["Job_Audition_Venue"])."',
												'".esc_attr($_POST["Job_Audition_Time"])."',
												'".esc_attr($_POST["Job_AgencyName"])."',
												NOW()
											)
									";


									$wpdb->query($sql);
								
									$results = $wpdb->get_results("SELECT ProfileContactPhoneCell,ProfileContactEmail, ProfileID FROM ".table_agency_profile." WHERE ProfileID IN(".(!empty($cartString)?$cartString:"''").")",ARRAY_A);
								
									foreach($results as $mobile){
										$hash_profile_id = RBAgency_Common::generate_random_string(20,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
					
										$sql = "INSERT INTO ".table_agency_castingcart_profile_hash."
										(
											CastingProfileHashID,
											CastingProfileHashJobID,
											CastingProfileHashProfileID,
											CastingProfileHash
										) 
										VALUES(
											'',
											'".$hash."',
											'".$mobile["ProfileID"]."',
											'".$hash_profile_id."'
										)";
										$wpdb->query($sql);

										RBAgency_Casting::sendText(array($mobile["ProfileContactPhoneCell"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$hash."/".$hash_profile_id);
										RBAgency_Casting::sendEmail(array($mobile["ProfileContactEmail"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$hash."/".$hash_profile_id);
									
									}

											 unset($_SESSION['cartArray']);
											echo ('<div id="message" class="updated"><p>Added successfully! <a href="'.admin_url("admin.php?page=rb_agency_castingjobs").'">View jobs</a></p></div>');
	
				}else{
					echo "No profiles selected in Casting cart.";
				}
          }elseif(isset($_POST["action2"]) && $_POST["action2"] =="edit"){

									$sql = "UPDATE ".table_agency_casting_job." 
										 SET
												Job_Title = '".esc_attr($_POST["Job_Title"])."', 
												Job_Text = '".esc_attr($_POST["Job_Text"])."',
												Job_Date_Start = '".esc_attr($_POST["Job_Date_Start"])."',
												Job_Date_End = '".esc_attr($_POST["Job_Date_End"])."',
												Job_Location = '".esc_attr($_POST["Job_Location"])."',
												Job_Region = '".esc_attr($_POST["Job_Region"])."',
												Job_Offering = '".esc_attr($_POST["Job_Offering"])."',
												Job_Talents = '".esc_attr($_POST["Job_Talents"])."',
												Job_Visibility = '".esc_attr($_POST["Job_Visibility"])."',
												Job_Criteria = '".esc_attr($_POST["Job_Criteria"])."',
												Job_Type = '".esc_attr($_POST["Job_Type"])."',
												Job_Talents_Hash = '".esc_attr($_POST["Job_Talents_Hash"])."',	
												Job_Audition_Date_Start = '".esc_attr($_POST["Job_Audition_Date_Start"])."',
												Job_Audition_Date_End = '".esc_attr($_POST["Job_Audition_Date_End"])."',
												Job_Audition_Venue = '".esc_attr($_POST["Job_Audition_Venue"])."',
												Job_Audition_Time = '".esc_attr($_POST["Job_Audition_Time"])."'
											WHERE Job_ID = ".esc_attr($_GET["Job_ID"])."
									";
									
									$wpdb->query($sql);

                              if(isset($_POST["resend"])){
									$results = $wpdb->get_results("SELECT ProfileID,ProfileContactPhoneCell,ProfileContactEmail FROM ".table_agency_profile." WHERE ProfileID IN(". implode(",",array_filter(explode(",",$_POST["Job_Talents_Resend_To"]))).")",ARRAY_A);
									$arr_mobile_numbers = array();
									$arr_email = array();
									$castingHash = $wpdb->get_row("SELECT * FROM ".table_agency_casting_job." WHERE Job_ID='".$_GET["Job_ID"]."'");
									foreach($results as $mobile){
										array_push($arr_mobile_numbers, $mobile["ProfileContactPhoneCell"]);
										array_push($arr_email, $mobile["ProfileContactEmail"]);
										$results_hash = $wpdb->get_row($wpdb->prepare("SELECT * FROM  ".table_agency_castingcart_profile_hash." WHERE  CastingProfileHashProfileID = %s",$mobile["ProfileID"]));
										RBAgency_Casting::sendText(array($mobile["ProfileContactPhoneCell"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$castingHash->Job_Talents_Hash."/".$results_hash->CastingProfileHash);
										RBAgency_Casting::sendEmail(array($mobile["ProfileContactEmail"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$castingHash->Job_Talents_Hash."/".$results_hash->CastingProfileHash);
									}
	
							  }
							  unset($_SESSION['cartArray']);
											echo ('<div id="message" class="updated"><p>Updated successfully!</p></div>');
	

          }elseif(isset($_GET["action2"]) && $_GET["action2"] == "deleteCastingJob"){
          	       $wpdb->query("DELETE FROM ".table_agency_casting_job." WHERE Job_ID = '".$_GET["removeJob_ID"]."'");
          	       echo ('<div id="message" class="updated"><p>Deleted successfully!</p></div>');
          }

				$Job_ID = ""; 
			 	$Job_Title = ""; 
				$Job_Text = "";
				$Job_Date_Start = "";
				$Job_Date_End = "";
				$Job_Location = "";
				$Job_Region = "";
				$Job_Offering = "";
				$Job_Talents = "";
				$Job_Visibility = "";
				$Job_Criteria = "";
				$Job_Type = "";
				$Job_Talents_Hash = "";	
				$Job_Audition_Date_Start = "";
				$Job_Audition_Date_End = "";
				$Job_Audition_Venue = "";
				$Job_Audition_Time = "";
				$CastingContactEmail = "";

		   if(isset($_GET["Job_ID"])){
		 	
			 	$sql =  "SELECT job.*, agency.* FROM ".table_agency_casting_job." as job INNER JOIN ".table_agency_casting." as agency ON job.Job_UserLinked = agency.CastingUserLinked WHERE Job_ID= %d ";
			 	$data = $wpdb->get_results($wpdb->prepare($sql, $_GET["Job_ID"]));
			 	$data = current($data);

	 			$Job_ID = $data->Job_ID; 
	 			$Job_AgencyName = $data->CastingContactCompany;
	 			$Job_Agency_ID = $data->Job_UserLinked;
			 	$Job_Title = $data->Job_Title; 
				$Job_Text = $data->Job_Text;
				$Job_Date_Start = $data->Job_Date_Start;
				$Job_Date_End = $data->Job_Date_End;
				$Job_Location = $data->Job_Location;
				$Job_Region = $data->Job_Region;
				$Job_Offering = $data->Job_Offering;
				$Job_Talents = implode(",",array_filter(explode(",",$data->Job_Talents)));
				$Job_Visibility = $data->Job_Visibility;
				$Job_Criteria = $data->Job_Criteria;
				$Job_Type = $data->Job_Type;
				$Job_Talents_Hash = $data->Job_Talents_Hash;	
				$Job_Audition_Date_Start = $data->Job_Audition_Date_Start;
				$Job_Audition_Date_End = $data->Job_Audition_Date_End;
				$Job_Audition_Venue = $data->Job_Audition_Venue;
				$Job_Audition_Time = $data->Job_Audition_Time;
				$CastingContactEmail = $data->CastingContactEmail;
				$CastingContactDisplay = $data->CastingContactDisplay;
			
			 }
        

      // Notify Client
	if(isset($_POST["notifyclient"])){
		   $bcc_emails = isset($_POST["bcc_emails"])?$_POST["bcc_emails"]:"";

		   $notified = RBAgency_Casting::sendClientNotification($CastingContactEmail,$_POST["message"],$bcc_emails);
		   		   	echo ('<div id="message" class="updated"><p>Notification successfully sent!</p></div>');
		   	
	}
		   
/*	if(isset($_GET["action2"]) && $_GET["action2"] == "addtoexisting"){
									$cartArray = isset($_SESSION['cartArray'])?$_SESSION['cartArray']:array();
									$cartString = implode(",", array_unique($cartArray));
									$cartString = RBAgency_Common::clean_string($cartString);
						
		 echo "<div class=\"boxblock-container\">";
				 echo "<div class=\"boxblock\" style=\"width:50%\" >";
				 
							echo "<h3>Add to existing Job</h3>";
						 
					 echo "<div class=\"innerr\" style=\"padding: 10px;\">";
	 				 echo "<form class=\"castingtext\" method=\"post\" action=\"\">";
					   echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
							echo "<div>";
								echo "<select name=\"Job_ID\" style=\"width:80%;\">";
								echo "<option value=\"\">- Select -</option>";
								$castings = $wpdb->get_results("SELECT * FROM ".table_agency_casting_job." ORDER BY Job_ID DESC");
								foreach ($castings as $key) {
									echo "<option value=\"".$key->Job_ID."-".$key->Job_UserLinked."\">".$key->Job_Title."</option>";
								}
								echo "<select>";
							    echo "&nbsp;<input type=\"submit\" class=\"button-primary button\" name=\"addtoexisting\" value=\"Submit\"/>";
							echo "</div>";
						echo "</div>";
						echo "<input type=\"hidden\" name=\"addprofilestocasting\" value=\"".$cartString."\"/>";
					 echo "</form>";
					echo "</div>";
				echo "</div>";
		echo "</div>";
				
				 	 
	}*/
    
    if(isset($_GET["action2"]) && $_GET["action2"] == "addnew" || isset($_GET["Job_ID"])){
	
				 echo "<div class=\"boxblock-container\">";
				 echo "<div class=\"boxblock\" style=\"width:50%\" >";
				 
						 if(isset($_GET["Job_ID"])){
							echo "<h3>Edit Talent Jobs</h3>";
						 }else{
						 	echo "<h3>Talent Jobs</h3>";
						}
				 echo "<div class=\"innerr\" style=\"padding: 10px;\">";
/*				  if(!isset($_GET["Job_ID"]) && (empty( $_SESSION['cartArray'] ) || !isset($_GET["action"]) )){
			      	  echo "Casting cart is empty. Click <a href=\"?page=rb_agency_search\">here</a> to search and add profiles to casting jobs.";
			      }else{*/
				 echo "<form class=\"castingtext\" method=\"post\" action=\"\">";
				   echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_AgencyName\">Agency/Producer</label>";
						echo "<div>";
						if(isset($_GET["action2"]) && $_GET["action2"] == "addnew"){
							echo "<select name=\"Job_AgencyName\" style=\"width:186px;\">";
							echo "<option value=\"\">- Select -</option>";
							$castings = $wpdb->get_results("SELECT * FROM ".table_agency_casting." WHERE CastingIsActive = 1 ORDER BY CastingContactCompany DESC");
							foreach ($castings as $key) {
								echo "<option value=\"".$key->CastingUserLinked."\">".$key->CastingContactDisplay." - ".$key->CastingContactCompany."</option>";
							}
							echo "<select>";
						}else{
							echo "<input type=\"text\" disabled=\"disabled\" id=\"Job_AgencyName\" name=\"Job_AgencyName\" value=\"".$Job_AgencyName."\">";
						}
						echo "</div>";
					echo "</div>";
					 echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Title\">Job Title</label>";
						echo "<div><input type=\"text\" id=\"Job_Title\" name=\"Job_Title\" value=\"".$Job_Title."\"></div>";
					echo "</div>";
					 echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Text\">Description</label>";
						echo "<div><textarea id=\"Job_Title\" name=\"Job_Text\">".$Job_Text."</textarea></div>";
					echo "</div>";
					 echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Offering\">Offer</label>";
						echo "<div><input type=\"text\" id=\"Job_Offering\" name=\"Job_Offering\" value=\"".$Job_Offering."\"></div>";
					echo "</div>";
					 echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Date_Start\">Job Date Start</label>";
						echo "<div><input type=\"text\" class=\"datepicker\" id=\"Job_Date_Start\" name=\"Job_Date_Start\" value=\"".$Job_Date_Start."\"></div>";
					echo "</div>";
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Date_End\">Job Date End</label>";
						echo "<div><input type=\"text\" class=\"datepicker\" id=\"Job_Date_End\" name=\"Job_Date_End\" value=\"".$Job_Date_End."\"></div>";
					echo "</div>";
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Location\">Location</label>";
						echo "<div><input type=\"text\" id=\"Job_Location\" name=\"Job_Location\" value=\"".$Job_Location."\"></div>";
					echo "</div>";
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Region\">Region</label>";
						echo "<div><input type=\"text\" id=\"Job_Region\" name=\"Job_Region\" value=\"".$Job_Region."\"></div>";
					echo "</div>";
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Type\">Job Type</label>";
						echo "<div>";
						echo "<select id='Job_Type' name='Job_Type'>";
							$get_job_type = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job_type); 
						    $count = $wpdb->num_rows;
									echo "<option value=''>-- Select Type --</option>";

									if(count($get_job_type)){
										foreach($get_job_type as $jtype){
											echo "<option value='".$jtype->Job_Type_ID."' ".selected($jtype->Job_Type_ID,$Job_Type,false).">".$jtype->Job_Type_Title."</option>";
										}
									}

		 				echo "	</select> ";
		 				if( $count <=0 ){
		 					echo "<div style=\"float:right;\">There are no job types added. <a href=\"".admin_url("admin.php?page=rb_agency_casting_jobpostings&action=manage_types")."\">Click here to add</a></div><div class=\"clear\"></div>";
		 				}
						echo "</div>";
					echo "</div>";
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Visibility\">Job Visibility</label>";
						echo "<div>";
						echo "<select id='Job_Visibility' name='Job_Visibility'>
									<option value=''>-- Select Type --</option>
									<option value='0' ".selected(isset($Job_Visibility)?$Job_Visibility:"","0",false).">Invite Only</option>
									<option value='1' ".selected(isset($Job_Visibility)?$Job_Visibility:"","1",false).">Open to All</option>
									<option value='2' ".selected(isset($Job_Visibility)?$Job_Visibility:"","2",false).">Matching Criteria</option>
								</select>";
						echo "&nbsp;<a title=\"Match Criteria\" href=\"#TB_inline?width=200&height=550&inlineId=add-criteria\" class=\"thickbox\"  id=\"job_criteria_field\" ".((isset($Job_Visibility) && $Job_Visibility == 2)?"":"style=\"display:none;\"").">Set</a>";
					echo '<input type="hidden" name="Job_Criteria" value="" />';
					echo '<div id="add-criteria" style="display:none;">';
					echo '<script type="text/javascript">';
					if(!empty($Job_Criteria)){
						echo 'jQuery(function(){ jQuery("#criteria").html("Loading Criteria List");
								
								jQuery.ajax({
										type: "POST",
										url: "'. admin_url('admin-ajax.php') .'",
										data: {
											action: "load_criteria_fields",
											value: "'.$Job_Criteria.'"
										},
										success: function (results) {
											jQuery("#criteria").html(results);
										},
										error: function (err){
											console.log(err);
										}
								}); });';
					}else{
							echo 'jQuery(function(){ jQuery("#criteria").html("Loading Criteria List");
									jQuery.ajax({
											type: "POST",
											url: "'. admin_url('admin-ajax.php') .'",
											data: {
												action: "load_criteria_fields"
											},
											success: function (results) {
												jQuery("#criteria").html(results);
											},
											error: function (err){
												console.log(err);
											}
									}); 
								});';
					}	
					echo 'jQuery(function(){
					         jQuery("#getcriteria").click(function(){
					         	    var criteria = [];
					         		jQuery("#criteria .rbfield").each(function(){
					         				if(jQuery(this).hasClass("rbselect")){
					         					 var val = jQuery(this).find("select").val();
					         					 var id = jQuery(this).attr("attrid");
					         					 if(val !=""){
						         					 criteria.push(id+"/"+val);
						         				}
					         				}else if(jQuery(this).hasClass("rbtext")){
					         				 	var val = jQuery(this).find("input[type=text]").val();
					         				 	var id = jQuery(this).attr("attrid");
					         				 	if(val != ""){
						         					criteria.push(id+"/"+val);
						         				}
					         				}else if(jQuery(this).hasClass("rbmulti")){
					         					var min = jQuery(this).find(".rbmin").val();
					         					var max=  jQuery(this).find(".rbmax").val();
					         					var id = jQuery(this).attr("attrid");
					         					if(min!="" && max !="" && min!== undefined && max !== undefined){
						         					criteria.push(id+"/"+min+"-"+max);
						         				}
						         			}

						         			if(jQuery(this).hasClass("rbradio")){
					         					var val = jQuery(this).find("input:checked").val();
					         					var id = jQuery(this).attr("attrid");
					         					if(val != ""){
						         					criteria.push(id+"/"+val);
						         				}
					         				}
					         				 if(jQuery(this).hasClass("rbcheckbox")){
					         					var arr = [];
					         					var id = jQuery(this).attr("attrid");
					         					var val = jQuery(this).find("input:checked").each(function(){
					         						 arr.push(jQuery(this).val());
					         					});
					         					criteria.push(id+"/"+arr.toString());
					         				}
					         		});
									jQuery("input[name=\'Job_Criteria\']").val(criteria.join("|"));
									console.log(criteria.join("|"));
									jQuery(".updatecriteria").html("&nbsp;Criteria successfully added!");
					         });
						});
					';
					echo "</script>";
					echo "<style type='text/css'>";
				    echo ".rbfield label{float: left;margin-top: 5px;width:150px;}";
				    echo ".rbfield {border-bottom:1px solid #ccc;padding-bottom:10px;padding-top:10px;}";
				    echo "</style>";
					echo '<div  style="margin:auto;width:70%;">';
						echo '<div id="criteria"></div>';
						echo "<div class=\"rbfield\"><a href=\"javascript:;\" id=\"getcriteria\" class=\"button-primary button\">Update</a><span class=\"updatecriteria\"></span></div>";
						echo "</div>";
					echo "</div>";
					
					echo "</div>";
					
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Audition_Date_Start\">Audition Date Start</label>";
						echo "<div><input type=\"text\"  class=\"datepicker\" id=\"Job_Audition_Date_Start\" name=\"Job_Audition_Date_Start\" value=\"".$Job_Audition_Date_Start."\"></div>";
					echo "</div>";
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Audition_Date_End\">Audition Date End</label>";
						echo "<div><input type=\"text\"  class=\"datepicker\" id=\"Job_Audition_Date_End\" name=\"Job_Audition_Date_End\" value=\"".$Job_Audition_Date_End."\"></div>";
					echo "</div>";
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Audition_Time\">Audition Time</label>";
						echo "<div><input type=\"text\"  class=\"timepicker\" id=\"Job_Audition_Time\" name=\"Job_Audition_Time\" value=\"".$Job_Audition_Time."\"></div>";
					echo "</div>";
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"Job_Audition_Venue\">Audition Venue</label>";
						echo "<div><input type=\"text\" id=\"Job_Audition_Venue\" name=\"Job_Audition_Venue\" value=\"".$Job_Audition_Venue."\"></div>";
					echo "</div>";

					 if(isset($_GET["Job_ID"])){
					echo "<div class=\"rbfield rbtext rbsingle \" id=\"\">";
						echo "<label for=\"comments\">&nbsp;</label>";
						echo "<div>";
						echo "<input type=\"checkbox\" name=\"resend\" value=\"1\"/> &nbsp;Resend notifcation to selected shortlisted talents \n\n";
					 	echo "</div>";
					echo "</div><br/><br/>";
                   
                    	echo "<input type=\"submit\" value=\"Save\" name=\"castingJob\" class=\"button-primary\" />";
                    	echo "<input type=\"hidden\" name=\"action2\" value=\"edit\"/>";
                    	echo "<input type=\"hidden\" name=\"Job_Talents\" value=\"".$Job_Talents."\"/>";
                    	echo "<input type=\"hidden\" name=\"Job_Talents_Hash\" value=\"".$Job_Talents_Hash."\"/>";
                    	echo "<input type=\"hidden\" name=\"Job_Talents_Resend_To\" value=\"\"/>";
                    	echo "<a href=\"".admin_url("admin.php?page=". $_GET['page'])."\" class=\"button\">Cancel</a>\t";
                    	echo "<a target=\"_blank\" style=\"float:right;\" href=\"".get_bloginfo("url")."/view-applicants/?filter_jobtitle=".(!empty($_GET["Job_ID"])?$_GET["Job_ID"]:0)."&filter_applicant=&filter_jobpercentage=&filter_rating=&filter_perpage=10&filter=filter\"  class=\"button-primary\">View Applicants</a>";
						echo "<div style=\"clear:both\"></div>";


                    }else{
						echo "<input type=\"hidden\" name=\"action2\" value=\"add\"/>";
                    	echo "<input type=\"submit\" value=\"Submit\" name=\"castingJob\" class=\"button-primary\" />";
                    	echo "<a href=\"".admin_url("admin.php?page=rb_agency_castingjobs")."\" class=\"button\">Cancel</a>";


                    }
				  	
				  	
				  echo "</form>";
				  echo "</div>";
				//  } // if casting cart is not empty
					echo '<script type="text/javascript">
							jQuery(document).ready(function(){
								jQuery( ".datepicker" ).datepicker();
								jQuery( ".datepicker" ).datepicker("option", "dateFormat", "yy-mm-dd");
								jQuery("#Job_Date_Start").val("'.$Job_Date_Start.'");
								jQuery("#Job_Date_End").val("'.$Job_Date_End.'");
								jQuery("#Job_Audition_Date_Start").val("'.$Job_Audition_Date_Start.'");
								jQuery("#Job_Audition_Date_End").val("'.$Job_Audition_Date_End.'");
											
					
								jQuery("#Job_Visibility").change(function(){
									if(jQuery(this).val() == 2){
										jQuery("#job_criteria_field").show();
									} else {
										jQuery("#criteria").html("");
										jQuery("#job_criteria_field").hide();
										
									}
								});
								jQuery(".timepicker").timepicker({
									hourGrid: 4,
									minuteGrid: 10,
									timeFormat: "hh:mm tt"
								});
							});
					  </script>';
				  echo "</div>";
				  echo "</div>";

                 
                 $cartArray = null;
				// Set Casting Cart Session
				if (isset($_SESSION['cartArray']) && !isset($_GET["Job_ID"])) {

					$cartArray = $_SESSION['cartArray'];
			     }elseif(isset($_GET["Job_ID"])){
			     	$cartArray = explode(",",$Job_Talents);
				
				} 
			    ?>
                 <script type="text/javascript">
                 jQuery(document).ready(function(){
                 	 var arr = [];
                 	 var arr_casting = [];
	                 	
	                 jQuery("#selectall").change(function(){
	                 		var ischecked = jQuery(this).is(':checked');
	                 		jQuery("form[name=formDeleteProfile] input[type=checkbox]").each(function(){
	                 			if(ischecked){
	                 			 jQuery(this).removeAttr("checked");
	                 			 jQuery(this).prop("checked",true);
	                 			 arr.push(jQuery(this).val());
	                 			}else{
	                 		     jQuery(this).prop("checked",true);
	                 		     jQuery(this).removeAttr("checked");
	                 			 arr = [];
								}
							});
	                 		jQuery("input[name=Job_Talents_Resend_To]").val(arr.toString());
	                 });

	                 jQuery("#selectallcasting").change(function(){
	                 		var ischecked = jQuery(this).is(':checked');
	                 		jQuery("form[name=formDeleteCastingProfile] input[type=checkbox]").each(function(){
	                 			if(ischecked){
	                 			 jQuery(this).removeAttr("checked");
	                 			 jQuery(this).prop("checked",true);
	                 			 arr.push(jQuery(this).val());
	                 			}else{
	                 		     jQuery(this).prop("checked",true);
	                 		     jQuery(this).removeAttr("checked");
	                 			 arr = [];
								}
							});
	                 		jQuery("input[name=Job_Talents_Resend_To]").val(arr.toString());
	                 });

	                  jQuery("#selectallcasting").change(function(){
	                 		var ischecked = jQuery(this).is(':checked');
	                 		jQuery("#castingcartbox input[type=checkbox]").each(function(){
	                 			if(ischecked){
	                 			 jQuery(this).removeAttr("checked");
	                 			 jQuery(this).prop("checked",true);
	                 			 arr_casting.push(jQuery(this).val());
	                 			}else{
	                 		     jQuery(this).prop("checked",true);
	                 		     jQuery(this).removeAttr("checked");
	                 			 arr_casting = [];
								}
							});
							
	                 });


	                 jQuery("input[name^=deleteprofilescasting]").click(function(){
		              	    if(jQuery("#castingcartbox input[name^=profiletalent]:checked").length > 0){
			              		 if(confirm("Are you sure that you want to delete the selected profiles? Click 'Yes' to delete, 'Cancel' to exit.")){
				            		jQuery("#castingcartbox input[name^=profiletalent]:checked").each(function(){
				            				jQuery("form[name=formDeleteCastingProfile]").submit();
				            		});
				            	}
				            }else{
				            	alert("You must select a profile to delete");
				            }

		             });
	                

	           
		             jQuery("#shortlisted input[name^=profiletalent],#castingcartbox input[name^=profiletalent]").click(function(){
		             	Array.prototype.remove = function(value) {
						  var idx = this.indexOf(value);
						  if (idx != -1) {
						      return this.splice(idx, 1); 
						  }
						  return false;
						}
		             				if(jQuery(this).is(':checked')){
		                 				arr.push(jQuery(this).val());
		                 			}else{
		                 				arr.remove(jQuery(this).val());
		                 			}
		                 		
		                 		jQuery("input[name=Job_Talents_Resend_To]").val(arr.toString());
		             });

		              jQuery("#shortlisted input[name^=deleteprofiles]").click(function(){
		              	    if(jQuery("#shortlisted input[name^=profiletalent]:checked").length > 0){
			              		 if(confirm("Are you sure that you want to delete the selected profiles? Click 'Yes' to delete, 'Cancel' to exit.")){
				            		jQuery("#shortlisted input[name^=profiletalent]:checked").each(function(){
				            				jQuery("form[name=formDeleteProfile]").submit();
				            		});
				            	}
				            }else{
				            	alert("You must select a profile to delete");
				            }

		             });
		             
		             jQuery("#shortlisted input[name^=addtocastingcart]").click(function(){
		             
		                   if(jQuery("#shortlisted input[name^=profiletalent]:checked").length > 0){
			              		 if(confirm("Are you sure that you want to add the selected profiles to casting cart? Click 'Yes' to add, 'Cancel' to exit.")){
				            				jQuery("input[name=addprofilestocasting]").val(arr.toString());
				            				jQuery("form[name=formAddProfileToCasting]").submit();
				            		
				            	}
				            }else{
				            	alert("You must select a profile");
				            }

		             });
		             
		            });
                 </script>
 
			    <?php
			     if((!empty( $_SESSION['cartArray']) || isset($_GET["Job_ID"])) ):

			         if( (isset($_GET["action2"]) && $_GET["action2"] != "addnew") || !isset($_GET["action2"])) { 

			         	 $casting_cart = $wpdb->get_results($wpdb->prepare("SELECT CastingCartTalentID FROM ".table_agency_castingcart." WHERE CastingJobID = %d ",$_GET["Job_ID"]),ARRAY_A);
							 // Show Cart  
							 $arr_profiles = array();
							 foreach ($casting_cart as $key) {
							 	array_push($arr_profiles, $key["CastingCartTalentID"]);
							 }
							 $query = "SELECT  profile.*,media.* FROM ". table_agency_profile ." profile, ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1 AND profile.ProfileID IN (".(!empty($arr_profiles)?implode(",", $arr_profiles):"''").") ORDER BY profile.ProfileContactNameFirst ASC";
							 $results = $wpdb->get_results($query, ARRAY_A);
							 $total_casting_profiles = $wpdb->num_rows;
						 echo "<div id=\"castingcartbox\" class=\"boxblock-container\" >";
						 echo "<div class=\"boxblock\">";
						 echo "<h3>Client's Casting Cart - ".($total_casting_profiles > 1?$total_casting_profiles." profiles":$total_casting_profiles." profile");
						 echo "<span style=\"font-size:12px;float:right;margin-top: -5px;\">";
						 echo "<a  href=\"#TB_inline?width=600&height=350&inlineId=notifyclient\" class=\"thickbox button-primary\" title=\"Notify Client\">Notify Client</a>";
						 echo "| <input type=\"submit\" name=\"deleteprofilescasting\" class=\"button-primary\" id=\"deleteprofiles\" value=\"Remove selected\" />";
						 echo "<input type=\"checkbox\" id=\"selectallcasting\"/>Select all</span>";
						 echo "</h3>";
						 echo "<div id=\"notifyclient\" style=\"display:none;\">";
						 echo "<form method=\"post\" action=\"\">";
						 echo "<input type=\"hidden\" name=\"notifyclient\" value=\"1\"/>";
						 echo "<table>";
						 echo "<tr>";
						 echo "<td><label>Client's Email:</label></td>";
						 echo "<td   style=\"width:500px;\"><input type=\"text\" disabled=\"disabled\" name=\"emailaddress\" style=\"width:100%;\" value=\"".$CastingContactEmail."\"/></td>";
						 echo "</tr>";
						 echo "<tr>";
						 echo "<td style=\"vertical-align: top;\"><label>BCC:</label></td>";
						 echo "<td   style=\"width:500px;\">";
						 echo "<input type=\"text\" name=\"bcc_emails\" style=\"width:100%;\" value=\"\"/>";
						 echo "<span style=\"font-size:11px;color:#ccc;\">You can enter multiple addresses, separated by commas.</span>";
						 echo "</td>";
						 echo "</tr>";
						 echo "<tr>";
						 echo "<td valign=\"top\"><label>Message:</label></td>";
						 echo "<td  style=\"width:500px;\"><textarea name=\"message\"  style=\"width:100%;height:200px;\">Hi ".$CastingContactDisplay.", \n\nWe have updated the casting cart for the job ".ucfirst($Job_Title).".\n\nTo review, please click the link below: \n\n". get_bloginfo("wpurl")."/profile-casting/?Job_ID=".$Job_ID."\n\n\n - ".get_bloginfo("name")."</textarea></td>";
						 echo "</tr>";
						 echo "<tr>";
						 echo "<td>&nbsp;</td><td><input type=\"submit\" value=\"Send\" class=\"button-primary\"/></td>";
						 echo "</tr>";
						 echo "</table>";
						 echo "</form>";
						 echo "</div>";
						 	echo "<form method=\"post\" name=\"formDeleteCastingProfile\" action=\"".admin_url("admin.php?page=rb_agency_castingjobs&action=informTalent&Job_ID=".(!empty($_GET["Job_ID"])?$_GET["Job_ID"]:0))."\" >\n";								
							echo "<input type=\"hidden\" name=\"action2\" value=\"deletecastingprofile\"/>";
			
							 echo "<div class=\"innerr\" style=\"padding: 10px;\">";
							
	
								
												foreach ($results as $data) {
													echo "<div style=\"width: 16.6%;float:left\" id=\"profile-".$data["ProfileID"]."\">";
													echo "<div style=\"height: 200px; margin-right: 5px; overflow: hidden; \"><span style=\"text-align:center;background:#ccc;color:#000;font-weight:bold;width:100%;padding:10px;display:block;\">".(isset($_GET["Job_ID"])?"<input type=\"checkbox\" name=\"profiletalent_".$data["ProfileID"]."\" value=\"".$data["ProfileID"]."\"/>":""). stripslashes($data['ProfileContactNameFirst']) ." ". stripslashes($data['ProfileContactNameLast']) . "</span><a href=\"". rb_agency_PROFILEDIR . $data['ProfileGallery'] ."/\" target=\"_blank\"><img style=\"width: 100%; \" src=\"". get_bloginfo("url")."/wp-content/plugins/rb-agency/ext/timthumb.php?src=".rb_agency_UPLOADDIR . $data["ProfileGallery"] ."/". $data['ProfileMediaURL'] ."&h=178&w=118\" /></a>";
													echo "</div>\n";
													if(isset($_GET["Job_ID"])){
														$query = "SELECT CastingAvailabilityStatus as status FROM ".table_agency_castingcart_availability." WHERE CastingAvailabilityProfileID = %d AND CastingJobID = %d";
														$prepared = $wpdb->prepare($query,$data["ProfileID"],$_GET["Job_ID"]);
														$availability = $wpdb->get_row($prepared);
														
														$count2 = $wpdb->num_rows;

														if($count2 <= 0){
															echo "<span style=\"text-align:center;color:#5505FF;font-weight:bold;width:80%;padding:10px;display:block;\">Unconfirmed</span>\n";
														}else{
														   if($availability->status == "available"){
														    echo "<span style=\"text-align:center;color:#2BC50C;font-weight:bold;width:80%;padding:10px;display:block;\">Available</span>\n";
															}else{
															echo "<span style=\"text-align:center;color:#EE0F2A;font-weight:bold;width:80%;padding:10px;display:block;\">Not Available</span>\n";
															}
														}
													}
													echo "</div>\n";
													echo "<style type=\"text/css\">";
													echo "#shortlisted #profile-".$data["ProfileID"]."{opacity: 0.3;}";
													echo "</style>";

												}
								
								if($count <= 0){
									echo "No profiles found.";
								}
							
							 echo "<div class=\"clear\"></div>";
							 echo "</div>";
							 echo "</form>";
						 echo "</div>";
						 echo "</div>";
						}
						 // Talents Shortlisted
                		 echo "<div id=\"shortlisted\" class=\"boxblock-container\" >";
						 echo "<div class=\"boxblock\">";
						 if(!empty($cartArray)){		   
									$cartString = implode(",", array_unique($cartArray));
									$cartString = RBAgency_Common::clean_string($cartString);
				}
						// Show Cart  
						$query = "SELECT  profile.*,media.* FROM ". table_agency_profile ." profile, ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1 AND profile.ProfileID IN (".(!empty($cartString)?$cartString:0).") ORDER BY profile.ProfileContactNameFirst ASC";
						$results = $wpdb->get_results($query, ARRAY_A);
						$count = $wpdb->num_rows;
						$total_casting_profiles = $count;
						 echo "<h3>Talents Shortlisted by Admin - ".($total_casting_profiles > 1?$total_casting_profiles." profiles":$total_casting_profiles." profile");
						 if(!empty( $_SESSION['cartArray']) || isset($_GET["Job_ID"])){
							 echo "<span style=\"font-size:12px;float:right;margin-top: -5px;\">";
							 echo "<a  href=\"#TB_inline?width=600&height=550&inlineId=add-profiles\" class=\"thickbox button-primary\" title=\"Add profiles to '".$Job_Title."' Job\">Add Profiles</a>";
							 if(isset($_GET["Job_ID"])){
							 	echo "<input type=\"submit\" name=\"deleteprofiles\" class=\"button-primary\" id=\"deleteprofiles\" value=\"Remove selected\" />";
							 	echo "<input type=\"submit\" name=\"addtocastingcart\" class=\"button-primary\" id=\"addtocastingcart\" value=\"Add to Client's Casting Cart\" />";
							 	echo "<input type=\"checkbox\" id=\"selectall\"/>Select all</span>";
							 }
						 }
						 echo "</h3>";
						 echo "<div class=\"innerr\" style=\"padding: 10px;\">";
					

					?>
					<?php add_thickbox(); ?>
					<div id="add-profiles" style="display:none;">
					<table>
					<tr>
					<td><label>First Name:</label> <input type="text" name="firstname"/></td>
					<td><label>Last Name:</label> <input type="text" name="lastname"/></td>
					</tr>
					</table>    
					<div class="results-info" style="width:80%;float:left;border:1px solid #fafafa;padding:5px;background:#ccc;">
				       Loading...
					</div>
					<input type="submit" value="Add to Job" id="addtojob" class="button-primary" style="float:right" />
					
					<div id="profile-search-result">
					    
					 </div>
					<style type="text/css">
 					.profile-search-list{
 						background:#FAFAFA;
 						width: 31.3%;
 						float:left;
 						margin:5px;
 						cursor: pointer;
 						border:1px solid #fff;
 					}
 					.profile-search-list.selected{
 						border:1px solid black;
 					}
 					.castingtext label{
						float: left;
						margin-top: 5px;
						margin-right: 20px;
						width:140px;
					}
					.castingtext input[type=text], .castingtext textarea{
						width:50%;
					}
 					</style>
 					
					</div>
					<script type="text/javascript">
 					jQuery(function(){
 						    var arr_profiles = [];
 						    var selected_info = "";
 						    var total_selected = 0;
 						    var arr_listed = Array();
							
							jQuery("form[name=formDeleteProfile] div[id^=profile-]").each(function(i,d){
 						    		  arr_listed[i] = jQuery(this).attr("id").split("profile-")[1];
 						    });

 							function get_profiles(){


		 						jQuery.ajax({
										type: 'POST',
								   		dataType: 'json',
								  		url: '<?php echo admin_url('admin-ajax.php'); ?>',
								   		data: { 
								  			'action': 'rb_agency_search_profile'
								  		},
								  		success: function(d){
								  			var profileDisplay = "";
								  			console.log(arr_listed);
								  			jQuery.each(d,function(i,p){
								  				if(jQuery.inArray(p.ProfileID+"",arr_listed) < 0){
										  				
										  				var fullname = p.ProfileContactNameFirst+" "+p.ProfileContactNameLast;
										  				
										  				if(fullname.length > 10) fullname = fullname.substring(0,15)+"[..]";
										  				
										  				profileDisplay = "<table class=\"profile-search-list\" id=\"profile-"+p.ProfileID+"\">"
																		 +"<tr>"
																		   +"<td style=\"width:40px;height:40pxbackground:#ccc;\">"+((p.ProfileMediaURL !="")?"<img src=\"<?php echo  get_bloginfo('url').'/wp-content/plugins/rb-agency/ext/timthumb.php?src='.rb_agency_UPLOADDIR;?>/"+p.ProfileGallery+"/"+p.ProfileMediaURL+"&w=40&h=40\" style=\"width:40px;height:40px;\"/>":"")+"</td>"
																		   +"<td>"
																		   +"<strong>"+fullname+"</strong>"
																		   +"<br/>"
																		   +"<span style=\"font-size: 11px;\">"+getAge(p.ProfileDateBirth)+","+p.GenderTitle+"</span>"
																		   +"<br/>"
																		   +"<a href=\"<?php echo get_bloginfo("wpurl");?>/profile/"+p.ProfileGallery+"/\" target=\"_blank\">View Profile</a>"
																		   +"</td>"
																		 +"</tr>"
																		 +"</table>";
										  				jQuery("#profile-search-result").append(profileDisplay);
										  				arr_profiles.push({name:p.ProfileContactNameFirst.toLowerCase()+" "+p.ProfileContactNameLast.toLowerCase(),profileid:p.ProfileID});
										  		
										  		}
								  			});
											
						 						jQuery("table[class^=profile-search-list]").click(function(){
								 						jQuery(this).toggleClass("selected" );
								 						 total_selected = 0;
								 						jQuery("table.profile-search-list.selected").each(function(){
								 							 total_selected++;
								 							
								 						});
								 						jQuery(".selected-info").remove();
									 					if(total_selected >0){
									 						jQuery("#TB_ajaxWindowTitle").html(jQuery("#TB_ajaxWindowTitle").html()+"<span class=\"selected-info\"> - "+total_selected+" profiles selected.</span>");
									 					}
									 	
								 				});
								 				
								 				jQuery(".results-info").html(arr_profiles.length+ " Profiles found. "+selected_info);
								  			
								  		},
								  		error: function(e){
								  			console.log(e);
								  		}
								});
							}

							get_profiles();

							function getAge(dateString) 
							{
							    var today = new Date();
							    var birthDate = new Date(dateString);
							    var age = today.getFullYear() - birthDate.getFullYear();
							    var m = today.getMonth() - birthDate.getMonth();
							    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) 
							    {
							        age--;
							    }
							    if(isNaN(age)){
							    	age = "Not Set";
							    	return age;
							    }
							    return age+"y/o";
							}

							  var fname = jQuery("div[id=add-profiles] input[name=firstname]");
				              var lname = jQuery("div[id=add-profiles] input[name=lastname]");
				              jQuery("#add-profiles input[name=firstname],#add-profiles input[name=lastname]").keyup(function(){
				              	  var keyword = fname.val().toLowerCase()+ " " +lname.val().toLowerCase();
				              	 
				              	  var result = find(arr_profiles,keyword);
				              	  
				              	  if(result.length > 0){
				              	  	jQuery("table[id^=profile-]").hide();
				              	  	jQuery("table[id^=profile-][class=selected]").show();

				              	  	jQuery.each(result,function(i,p){
												jQuery("table[id^='profile-"+p.profileid+"']").show();

									});
									jQuery(".results-info").html("Search Result: "+result.length+" "+(result.length>1?"profiles":"profile")+" found. "+selected_info);
				              	  }else{
				              	  	jQuery(".results-info").html("'"+keyword+"' not found. "+selected_info);
				              	  }
				              	 
				              });

				              function find(arr,keyword) {
								    var result = [];

								   jQuery.each(arr,function(i,p){
								   	    if (p.name.indexOf(keyword) >= 0) {
								            result.push({profileid:p.profileid});
								        }
								    });

								    return result;
							}

							jQuery("#addtojob").click(function(){
								  var arr_profiles_selected = [];
								  jQuery("table.profile-search-list.selected").each(function(){
								  	var profiles = jQuery(this).attr("id").split("profile-")[1];
								  		arr_profiles_selected.push(profiles);
								  });
								   jQuery("input[name=addprofiles]").val(arr_profiles_selected.join());
								   window.parent.tb_remove();
								   arr_profiles_selected = [];
								   jQuery("form[name=formAddProfile]").submit();

		
							});

							 						
	 					
	 				});
 					</script>
 				<?php 
 				if(isset($_GET["action2"]) && $_GET["action2"] == "addnew"){
 					echo "<form method=\"post\" name=\"formAddProfile\" action=\"".admin_url("admin.php?page=rb_agency_castingjobs&action2=addnew&action=informTalent")."\" >\n";								
				}else{
 					echo "<form method=\"post\" name=\"formAddProfile\" action=\"".admin_url("admin.php?page=rb_agency_castingjobs&action=informTalent&Job_ID=".(!empty($_GET["Job_ID"])?$_GET["Job_ID"]:0))."\" >\n";								
				}
				echo "<input type=\"hidden\" value=\"\" name=\"addprofiles\"/>";
				echo "</form>";

 				echo "<form method=\"post\" name=\"formAddProfileToCasting\" action=\"".admin_url("admin.php?page=rb_agency_castingjobs&action=informTalent&Job_ID=".(!empty($_GET["Job_ID"])?$_GET["Job_ID"]:0))."\" >\n";								
				echo "<input type=\"hidden\" value=\"\" name=\"addprofilestocasting\"/>";
				echo "<input type=\"hidden\" value=\"".(isset($Job_Agency_ID)?$Job_Agency_ID:"") ."\" name=\"Agent_ID\" />";
				echo "</form>";
 
				
				
				echo "<form method=\"post\" name=\"formDeleteProfile\" action=\"".admin_url("admin.php?page=rb_agency_castingjobs&action=informTalent&Job_ID=".(!empty($_GET["Job_ID"])?$_GET["Job_ID"]:0))."\" >\n";								
				echo "<input type=\"hidden\" name=\"action2\" value=\"deleteprofile\"/>";
				foreach ($results as $data) {
					echo "<div style=\"width: 16.6%;float:left\" id=\"profile-".$data["ProfileID"]."\">";
					echo "<div style=\"height: 200px; margin-right: 5px; overflow: hidden; \">";
					echo "<span style=\"text-align:center;background:#ccc;color:#000;font-weight:bold;width:100%;padding:10px;display:block;\">";
					if(isset($_GET["Job_ID"])){
						echo "<input type=\"checkbox\" name=\"profiletalent_".$data["ProfileID"]."\" value=\"".$data["ProfileID"]."\"/>";
					}
					echo  stripslashes($data['ProfileContactNameFirst']) ." ". stripslashes($data['ProfileContactNameLast']) . "</span>";
					echo "<a href=\"". rb_agency_PROFILEDIR . $data['ProfileGallery'] ."/\" target=\"_blank\">";
					echo "<img style=\"width: 100%; \" src=\"". get_bloginfo("url")."/wp-content/plugins/rb-agency/ext/timthumb.php?src=".rb_agency_UPLOADDIR . $data["ProfileGallery"] ."/". $data['ProfileMediaURL'] ."&h=178&w=118\" />";
					echo "</a>";
					echo "</div>\n";
									if(isset($_GET["Job_ID"])){
										$query = "SELECT CastingAvailabilityStatus as status FROM ".table_agency_castingcart_availability." WHERE CastingAvailabilityProfileID = %d AND CastingJobID = %d";
										$prepared = $wpdb->prepare($query,$data["ProfileID"],$_GET["Job_ID"]);
										$availability = current($wpdb->get_results($prepared));
										
										$count2 = $wpdb->num_rows;

										if($count2 <= 0){
											echo "<span style=\"text-align:center;color:#5505FF;font-weight:bold;width:80%;padding:10px;display:block;\">Unconfirmed</span>\n";
										}else{
										   if($availability->status == "available"){
										    echo "<span style=\"text-align:center;color:#2BC50C;font-weight:bold;width:80%;padding:10px;display:block;\">Available</span>\n";
											}else{
											echo "<span style=\"text-align:center;color:#EE0F2A;font-weight:bold;width:80%;padding:10px;display:block;\">Not Available</span>\n";
											}
										}
									}
					echo "</div>\n";
				}
				echo "</form>\n";

				if($count <= 0){
					echo "No profiles found.";
				}
				
			  	  		echo "<div style=\"clear:both;\"></div>";
					echo "</div>";
				  echo "</div>";
				echo "</div>";
			endif;
		echo "</div>";

		 } } // end add/edit job
	    
	    // Load casting jobs list
	   RBAgency_Casting::rb_display_casting_jobs(); ?>