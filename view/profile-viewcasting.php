<?php
global $user_ID;
global $wpdb;
// *************************************************************************************************** //
// Get Category

// This is the Portfolio-Category page 
if(!headers_sent())
header("Cache-control: private"); //IE 6 Fix

include( RBAGENCY_casting_BASEREL."/app/casting.class.php");

// Get Profile
//$ProfileType = get_query_var('target'); 

$rb_agency_options_arr = get_option("rb_agency_options");
$rb_agency_option_allowsendemail = $rb_agency_options_arr["rb_agency_option_allowsendemail"];
$rb_agency_option_agencyname = $rb_agency_options_arr["rb_agency_option_agencyname"];
$rb_agency_option_agencyemail = $rb_agency_options_arr["rb_agency_option_agencyemail"];

if (isset($ProfileType) && !empty($ProfileType)){
	$DataTypeID = 0;
	$DataTypeTitle = "";
	$query = "SELECT DataTypeID, DataTypeTitle FROM ". table_agency_data_type ." WHERE DataTypeTag = '". $ProfileType ."'";

	$results = $wpdb->get_results($query,ARRAY_A);
	foreach ($results as $data) {
		$DataTypeID = $data['DataTypeID'];
		$DataTypeTitle = $data['DataTypeTitle'];
		$filter .= " AND profile.ProfileType=". $DataTypeID ."";
	}
}

if(isset($_POST["action"]) && $_POST["action"] == "sendEmailCastingCart"){

	$SearchID				= time(U);
	$SearchMuxHash			= RBAgency_Common::generate_random_string(8);
	$SearchMuxToName		= $_POST['SearchMuxToName'];
	$SearchMuxToEmail		= get_option('admin_email');
	$SearchMuxEmailToBcc	= $_POST['SearchMuxEmailToBcc'];
	$SearchMuxSubject		= get_bloginfo('name') . " - ".$_POST['SearchMuxSubject'];
	$SearchMuxMessage		= $_POST['SearchMuxMessage'];
	$SearchMuxCustomValue	= $_POST['SearchMuxCustomValue'];

	// Get Casting Cart
	$query = "SELECT  profile.*, profile.ProfileGallery, profile.ProfileContactDisplay, profile.ProfileDateBirth, profile.ProfileLocationState, profile.ProfileID as pID , cart.CastingCartTalentID, cart.CastingCartTalentID, (SELECT media.ProfileMediaURL FROM ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1) AS ProfileMediaURL FROM ". table_agency_profile ." profile INNER JOIN  ".table_agency_castingcart."  cart WHERE  cart.CastingCartTalentID = profile.ProfileID   AND cart.CastingCartProfileID = '".rb_agency_get_current_userid()."' AND ProfileIsActive = 1 ORDER BY profile.ProfileContactNameFirst";
	$result = $wpdb->get_results($query,ARRAY_A);
	$pID = "";
	$profileid_arr = array();

	foreach($result as $fetch){
		$profileid_arr[] = $fetch["pID"];
	}

	$casting = implode(",",$profileid_arr);
	$wpdb->query("INSERT INTO " . table_agency_searchsaved." (SearchProfileID) VALUES('".$casting."')");

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

	$SearchMuxMessage = str_replace("[casting-link-placeholder]",network_site_url()."/client-view/".$SearchMuxHash,$SearchMuxMessage);

	add_filter('wp_mail_content_type','rb_agency_set_content_type');
	function rb_agency_set_content_type($content_type){
		return 'text/html';
	}

	// Mail it
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// To send HTML mail, the Content-type header must be set
	$headers .= 'To: '. $rb_agency_option_agencyname .' <'. $SearchMuxToEmail .'>' . "\r\n";
	$headers = 'From: '. $SearchMuxToName .' <'. $_POST['SearchMuxToEmail'] .'>' . "\r\n";

	if(!empty($SearchMuxEmailToBcc)){
		$headers = 'Bcc: '.$SearchMuxEmailToBcc.'' . "\r\n";
	}

	$isSent = wp_mail($SearchMuxToEmail, $SearchMuxSubject, $SearchMuxMessage, $headers);
	if($isSent){
		wp_redirect(network_site_url()."/profile-casting-cart/?emailSent");exit;
	}
}

echo $rb_header = RBAgency_Common::rb_header(); ?>

<script type="text/javascript">
		jQuery(document).ready(function(){jQuery(".rblinks").css({display:"block"});});
		function printDiv(divName) {
			var printContents = document.getElementById(divName).innerHTML;
			var originalContents = document.body.innerHTML;
			document.body.innerHTML = originalContents;
			window.print();
		}
</script>

<?php

	echo "	<div class=\"".fullwidth_class()."  clearfix\">\n";
	echo "  	<div id=\"rbcontent\" role=\"main\" >\n";
	echo '			<header class="entry-header">';
	echo '				<h1 class="entry-title">Casting Cart</h1>';
	echo '			</header>';
	echo '			<div class="entry-content">';
	echo "				<div id=\"rbcasting-cart\">\n";
	echo "					<div class=\"cb\"></div>\n"; ?>

							<script type="text/javascript">
							jQuery(document).ready(function(){
								jQuery("#sendemail").click(function(){
									jQuery('#emailbox').toggle('slow'); 
								});
								jQuery("#checkavailability").click(function(){
										jQuery("#checkavailabilityForm").toggle('slow'); 
										jQuery("#sendProfilesForm").hide('slow');
										if(jQuery(this).val() == "[+]Check Availability"){
											jQuery(this).val("[-]Check Availability");
											jQuery("#sendProfiles").val("[+]Send Profiles");
										} else {
											jQuery(this).val("[+]Check Availability");
										}
								});
								jQuery("#sendProfiles").click(function(){
										jQuery("#sendProfilesForm").toggle('slow'); 
										jQuery("#checkavailabilityForm").hide('slow'); 
										if(jQuery(this).val() == "[+]Send Profiles"){
											jQuery(this).val("[-]Send Profiles");
											jQuery("#checkavailability").val("[+]Check Availability");
										} else {
											jQuery(this).val("[+]Send Profiles");
										}
								});
								jQuery("#inviteprofiles").click(function(){
										jQuery("#inviteprofilesForm").toggle('slow'); 
										if(jQuery(this).val() == "[+]Invite Profiles"){
											jQuery(this).val("[-]Invite Profiles");
										} else {
											jQuery(this).val("[+]Invite Profiles");
										}
								});


							});
							</script>

							<div id="emailbox" style="display:none;">
								<form method="post" enctype="multipart/form-data" action="">
									<input type="hidden" name="action" value="cartEmail" />
									<div class="field"><label for="SearchMuxToName">Sender Name:</label><br/><input type="text" id="SearchMuxToName" name="SearchMuxToName" value="" required/></div>
									<div class="field"><label for="SearchMuxToEmail">Sender Email:</label><br/><input type="email" id="SearchMuxToEmail" name="SearchMuxToEmail" value="" required/></div>
									<div class="field"><label for="SearchMuxSubject">Subject:</label><br/><input type="text" id="SearchMuxSubject" name="SearchMuxSubject" value="Casting Cart" required></div>
									<div class="field"><label for="SearchMuxMessage">Message to Admin:</label><br/>
										<textarea id="SearchMuxMessage" name="SearchMuxMessage" style="width: 500px; height: 300px; ">[casting-link-placeholder]</textarea>
									</div>
									<p>(Note: The "[casting-link-placeholder]" will be the link to your casting cart page) </p>
									<div class="field submit">
										<input type="hidden" name="action" value="sendEmailCastingCart" />
										<input type="submit" name="submit" value="Send Email" class="button-primary" /> 
									</div>
								</form>
							</div>
					<?php 
					echo "				</div>\n";
					echo "			</div>\n";

				echo "			<div class=\"cb\"></div>\n";
				if(is_user_logged_in()){
					if(current_user_can("edit_posts")){
							$Jobs = $wpdb->get_results("SELECT * FROM ".table_agency_casting_job." ");
					} else {
							$Jobs = $wpdb->get_results("SELECT * FROM ".table_agency_casting_job." WHERE Job_UserLinked = ".rb_agency_get_current_userid());
					}
					echo "<form method=\"get\" action=\"\" id=\"search-job\" class=\"search-form\">";
					echo "<div id=\"field\"><select name=\"Job_ID\">";
					echo "<option value=\"\">- Select a job-</option>";
					foreach ($Jobs as $key) {
						echo "<option value=\"".$key->Job_ID."\" ".selected($key->Job_ID,isset($_GET["Job_ID"])?$_GET["Job_ID"]:"")." >".$key->Job_Title."</option>";
					}
					echo "</select></div>";
					echo "<div id=\"action\"><input type=\"submit\" name=\"search\"  value=\"Search\"/>";
						if(!isset($_GET["Job_ID"]) || empty($_GET["Job_ID"])){
							echo "<input type=\"button\" name=\"clear\" value=\"Clear\" onclick=\"window.location.href='".get_bloginfo("url")."/profile-casting/'\"/>";
						} else {
							echo "<input type=\"button\" name=\"clear\" value=\"Back to Profile Casting\" onclick=\"window.location.href='".get_bloginfo("url")."/profile-casting/'\"/>";
						}
					echo "</div></form>";

					if(isset($_POST["addtojob"])){
						$data = $wpdb->get_row("SELECT * FROM ".table_agency_casting_job." WHERE Job_ID ='".$_POST["job_id"]."' ");
						$wpdb->query("UPDATE ".table_agency_castingcart." SET CastingJobID='".$_POST["job_id"]."', CastingCartProfileID='".$data->Job_UserLinked."' WHERE CastingCartProfileID='".rb_agency_get_current_userid()."' AND CastingCartTalentID IN(".$_POST["shortlistprofiles"].")");
						wp_redirect(get_bloginfo("url")."/profile-casting/?Job_ID=".$_POST["job_id"]);
					}

					if(isset($_POST["removefromcart"])){

						$wpdb->query("DELETE FROM ".table_agency_castingcart." WHERE CastingCartProfileID='".rb_agency_get_current_userid()."' AND CastingCartTalentID IN(".$_POST["shortlistprofiles"].")");
						echo "<div class=\"\">Succesfully removed.</div>";
					}

					if(isset($_POST["removefromjob"])){
						$wpdb->query("UPDATE ".table_agency_castingcart." SET CastingJobID='', CastingCartProfileID='".rb_agency_get_current_userid()."' WHERE CastingJobID='".$_POST["job_id"]."' AND CastingCartTalentID IN(".$_POST["shortlistprofiles"].")");
					}
					echo "<div class=\"result-action\">";
						echo "<label><input type=\"checkbox\" name=\"selectallprofiles\"  id=\"selectall\"/> Select all</label>";

					if(!isset($_GET["Job_ID"]) || empty($_GET["Job_ID"])){
						echo "<form method=\"post\" name=\"castingcartForm\" action=\"\"><div class=\"action\">";
						echo "<input type=\"submit\" name=\"removefromcart\" onclick=\"return confirm('Are you sure to remove selected profiles?')?1:false;\" value=\"Remove selected profile(s)\"/>";
						echo "<input type=\"submit\" name=\"addtojob\"  value=\"Add selected profile(s)\"/>";
						echo "<input type=\"hidden\" name=\"shortlistprofiles\" value=\"\"/>";
						echo "<input type=\"hidden\" name=\"job_id\" value=\"\"/>";
						echo "</div></form>";
					}

					if(isset($_GET["Job_ID"]) && !empty($_GET["Job_ID"])){
						echo "<form method=\"post\" action=\"\"><div class=\"action\">";
						echo "<input type=\"submit\" name=\"removefromjob\"  value=\"Remove selected profile(s)\"/>";
						echo "<input type=\"hidden\" name=\"job_id\" value=\"".$_GET["Job_ID"]."\"/>";
						echo "<input type=\"hidden\" name=\"shortlistprofiles\" value=\"\"/>";
						echo "</div></form>";
						echo "<style type=\"text/css\">.rb_profile_tool{display:none;}</style>";

						if($rb_agency_option_allowsendemail == 1){
							echo "<input type=\"button\" name=\"inviteprofiles\"  id=\"inviteprofiles\" value=\"[+]Invite Profiles\"/>";
						} elseif($rb_agency_option_allowsendemail == 2){
							echo "<input type=\"button\" name=\"checkavailability\"  id=\"checkavailability\" value=\"[+]Check Availability\"/>";
						}

						echo "<input type=\"button\" name=\"sendProfiles\" id=\"sendProfiles\" value=\"[+]Send Profiles\" />";
						}
						echo "</div>";

					}
					echo "<div style=\"clear:both;\"></div>";

					if(isset($_POST["inviteprofiles"])){
						$cartString = $_POST["shortlistprofiles"];
						$results = $wpdb->get_results("SELECT ProfileContactPhoneCell,ProfileContactEmail, ProfileID FROM ".table_agency_profile." WHERE ProfileID IN(".(!empty($cartString)?$cartString:"''").")",ARRAY_A);
						$job_hash = $wpdb->get_row("SELECT Job_Talents_Hash FROM ".table_agency_casting_job." WHERE Job_ID = '".$_GET["Job_ID"]."' ");
						foreach($results as $mobile){

							$user_hash_record = $wpdb->get_row("SELECT * FROM ".table_agency_castingcart_profile_hash." WHERE CastingProfileHashProfileID = '".$mobile["ProfileID"]."' AND CastingProfileHashJobID = '".$job_hash->Job_Talents_Hash."'");
							$has_hash = $wpdb->num_rows;
							if($has_hash <= 0){
								$hash_profile_id = RBAgency_Common::generate_random_string(20,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
								$sql = "INSERT INTO ".table_agency_castingcart_profile_hash." VALUES(
								'',
								'".$job_hash->Job_Talents_Hash."',
								'".$mobile["ProfileID"]."',
								'".$hash_profile_id."')";
								$wpdb->query($sql);

								RBAgency_Casting::sendText(array($mobile["ProfileContactPhoneCell"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$job_hash->Job_Talents_Hash."/".$hash_profile_id,$_POST["message"]);
								RBAgency_Casting::sendEmail(array($mobile["ProfileContactEmail"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$job_hash->Job_Talents_Hash."/".$hash_profile_id,$_POST["message"]);
							} else {
								RBAgency_Casting::sendText(array($mobile["ProfileContactPhoneCell"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$job_hash->Job_Talents_Hash."/".$user_hash_record->CastingProfileHash,$_POST["message"]);
								RBAgency_Casting::sendEmail(array($mobile["ProfileContactEmail"]),get_bloginfo("wpurl")."/profile-casting/jobs/".$job_hash->Job_Talents_Hash."/".$user_hash_record->CastingProfileHash,$_POST["message"]);
							}
						}
							echo "<p id=\"emailSent\">Invitation Sent Succesfully!</p>";
					}
					if($rb_agency_option_allowsendemail == 1){
						?>
						<div id="inviteprofilesForm" style="display:none;">
						<strong>Invite Profiles</strong>
						<form method="post" action="">
						<div>
							Message:<br/>
								<p>(Note: The "[casting-job-url]" will be the link to your shorlisted profile for the job) </p>

								<textarea name="message" style="width:100%;height:200px;">Add your message here...
								Click this link to view Job details: [casting-job-url]
								</textarea>
							<br/>
							<input type="submit" name="inviteprofiles" value="Send" />
							<input type="hidden" name="shortlistprofiles" value=""/>

							</div>
						</form>
						</div>
						<?php 
					} elseif($rb_agency_option_allowsendemail == 2){

					if(isset($_POST["checkavailability"])){
						// Prepre Message
						$message = $_POST["message"];

						// Define Link to Casting
						$link = admin_url("admin.php?page=rb_agency_castingjobs&action=informTalent&Job_ID=".$_GET["Job_ID"]);
						// Get the Job ID
						$data_job = $wpdb->get_row("SELECT  casting.*, job.* FROM ".table_agency_casting_job." as job INNER JOIN ".table_agency_casting." as casting ON casting.CastingUserLinked = job.Job_UserLinked WHERE job.Job_ID ='".$_GET["Job_ID"]."' ");
						// Send Email
						//RBAgency_Casting::sendEmailAdminCheckAvailability($data_job->CastingContactDisplay, $data_job->CastingContactEmail, $message, $link);

						if ($_POST["email_bcc"]) {
							$Message	= str_replace("[shortlisted-link-placeholder]", $link, $message);
							$headers[] = 'MIME-Version: 1.0';
							$headers[] = 'Content-type: text/html; charset=iso-8859-1';
							$headers[] = 'From: "'. $castingname .'" <'. trim($castingemail) .'>';
							$isSent = wp_mail($_POST["email_bcc"], $rb_agency_value_agencyname.": Check availability", $Message, $headers);
							echo "<p id=\"emailSent\">BCC Email Sent to ". $_POST["email_bcc"] .".</p>";
						}

						RBAgency_Casting::sendEmailAdminCheckAvailability($data_job->CastingContactDisplay, $data_job->CastingContactEmail, $message, $link);
						echo "<p id=\"emailSent\">Email Sent Succesfully to ". $data_job->CastingContactEmail ."!</p>";
					}




					?>


					<div id="checkavailabilityForm" class="rbform block" style="display:none;">
					<strong>Check Availability</strong>
					<form method="post" action="">
						<div class="rbfield rbtext rbsingle">
							<label>Send to:</label>
							<div><input type="text" disabled="disabled" value="<?php echo $rb_agency_option_agencyname; ?>"/><input type="hidden" name="adminemail" disabled="disabled" value="<?php echo !empty($rb_agency_option_agencyname)?$rb_agency_option_agencyname:get_bloginfo("admin_email");?>" /></div>
						</div>
						<div class="rbfield rbtext rbsingle">
							<label>BCC:</label>
							<div><input type="text" name="email_bcc" /></div>
						</div>
						<div class="rbfield rbtext rbsingle">
							<label>Message:</label>
							<div>
								<small>(Note: The "[shortlisted-link-placeholder]" will be the link to your shorlisted profile for the job) </small><br />
								<textarea name="message" style="width:100%;height:200px;">Add your message here...<br />[shortlisted-link-placeholder]</textarea>
							</div>
						</div>
						<div class="rbfield rbsubmit">
							<input type="submit" name="checkavailability" value="Send" />
						</div>
					</form>
					</div>



					<?php }// endif $rb_agency_option_allowsendemail == 2 ?>

					<!-- Send Profiles Form -->
					<?php
					//Send Profile
					if(isset($_POST["sendProfileBtn"])){
						// Prepre varialbes

						//START
						$SearchMuxHash			= RBAgency_Common::generate_random_string(8);
						$fromName 				= $_POST["fromName"];
						$fromEmail 				= $_POST["fromEmail"];
						$SearchMuxToName		= $_POST['sendToName'];
						$SearchMuxToEmail		= $_POST["sendToEmail"];
						$SearchMuxEmailToBcc	= $_POST['emailBcc'];
						$SearchMuxSubject		= get_bloginfo('name')." : ".$_POST["subject"];
						$SearchMuxMessage		= $_POST['message'];

						// Get Casting Cart
						$query = "SELECT  profile.*, profile.ProfileGallery, profile.ProfileContactDisplay, profile.ProfileDateBirth, profile.ProfileLocationState, profile.ProfileID as pID , cart.CastingCartTalentID, cart.CastingCartTalentID, (SELECT media.ProfileMediaURL FROM ". table_agency_profile_media ." media WHERE profile.ProfileID = media.ProfileID AND media.ProfileMediaType = \"Image\" AND media.ProfileMediaPrimary = 1) AS ProfileMediaURL FROM ". table_agency_profile ." profile INNER JOIN  ".table_agency_castingcart."  cart WHERE  cart.CastingCartTalentID = profile.ProfileID   AND cart.CastingCartProfileID = '".rb_agency_get_current_userid()."' AND ProfileIsActive = 1 ORDER BY profile.ProfileContactNameFirst";
						$result = $wpdb->get_results($query,ARRAY_A);
						$profileid_arr = array();

						foreach($result as $fetch){
							$profileid_arr[] = $fetch["pID"];
						}

						$casting = implode(",",$profileid_arr);
						$wpdb->query("INSERT INTO " . table_agency_searchsaved." (SearchProfileID) VALUES('".$casting."')");

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

						$SearchMuxMessage = str_replace("[link-place-holder]",network_site_url()."/client-view/".$SearchMuxHash,$SearchMuxMessage);


						$Message   = $SearchMuxMessage;
						$headers[] = 'MIME-Version: 1.0';
						$headers[] = "Content-Type: text/html; charset=\"". get_option('blog_charset') . "\"\n";
						$headers[] = 'From: "'. $fromName .'" <'. trim($fromEmail) .'>';

						$bccArray = explode(";",$SearchMuxEmailToBcc);

						foreach($bccArray as $bcc){
							$headers[] = 'Bcc: '.$bcc;
						}

						add_filter('wp_mail_from','yoursite_wp_mail_from');
						add_filter('wp_mail_from_name','yoursite_wp_mail_from_name');

						$Message = str_replace("\n","<br>",$Message);
						$isSent = wp_mail($SearchMuxToEmail, get_bloginfo('name')." : ".$_POST["subject"] , stripcslashes(make_clickable($Message)), $headers);
						if($isSent){
							echo "<p id=\"emailSent\">Email Sent Succesfully to ". $SearchMuxToName ."!</p>";
						} else {
							echo "<p id=\"emailSent\">Error sending the email!</p>";
						}


					}
					global $current_user;
  					get_currentuserinfo();
					?>
					<div id="sendProfilesForm" class="rbform block" style="display:none;">
						<h2>Send Profiles</h2>
						<form method="post" action="">
							<div>
								<input type="hidden" name="fromName" id="fromName" value="<?php echo $current_user->user_firstname." ".$current_user->user_lastname; ?>" disabled="disabled"/>
							</div>
							<div>
								<input type="hidden" name="fromEmail" id="fromEmail" value="<?php echo $current_user->user_email; ?>" disabled="disabled"/>
							</div>
							<div class="rbfield rbtext rbsingle">
								<label>Send to Name:</label>
								<div><input type="text" name="sendToName" id="sendToName" /></div>
							</div>
							<div class="rbfield rbtext rbsingle">
								<label>Send to Email:</label>
								<div><input type="text" name="sendToEmail" id="sendToEmail" /></div>
							</div>
							<div class="rbfield rbtext rbsingle">
								<label>BCC:</label>
								<div><input type="text" name="emailBcc" id="emailBcc"/></div>
							</div>
							<div class="rbfield rbtext rbsingle">
								<label>Subject:</label>
								<div><input type="text" name="subject" id="subject"/></div>
							</div>
							<div class="rbfield rbtextarea rbsingle">
								<label>Message:</label>
								<div><textarea id="message" name="message" style="width:100%;height:200px;">Click the following link (or copy and paste it into your browser): [link-place-holder]</textarea></div>
							</div>
							<div class="rbfield rbsubmit">
								<input type="submit" id="sendProfileBtn" name="sendProfileBtn" value="Send" />
							</div>
						</form>
					</div>
					<!-- end send profile form -->
					<?php
					if (class_exists('RBAgency_Profile')) {
						echo "<div id=\"profile-casting-list\">";
						$atts = array("type" => isset($DataTypeID)?$DataTypeID:"", "profilecasting" => true);
						$search_sql_query = RBAgency_Profile::search_generate_sqlwhere($atts);
						$view_type = 2; // casting
						if($rb_agency_option_allowsendemail == 1){
							$castingcart =  true;
							echo $search_results = RBAgency_Profile::search_results($search_sql_query, $view_type, $castingcart);
						} else {
							echo $search_results = RBAgency_Profile::search_results($search_sql_query, $view_type);
						}
						echo "</div>";
					} elseif(!is_user_logged_in()) {
						echo "<div id=\"profile-casting-list\">";
						echo "Please <a href=\"". get_bloginfo("url")."/casting-login/?lastviewed=".get_bloginfo("url")."/profile-casting/?Job_ID=".$wpdb->prepare("%d",$_GET["Job_ID"])."\" style=\"color:##3E85D1 !important;\">login</a> to view the profile(s).";
						echo "</div>";
					}

					if(isset($_GET["emailSent"])) {
						echo "<p id=\"emailSent\">Email Sent Succesfully! Go Back to <a href=\"". get_bloginfo("url")."/search/\">Search</a>.</p>";
					}

echo "			<div class=\"cb\"></div>\n";

if(is_user_logged_in()){
	echo "<p><a href='".get_bloginfo('wpurl')."/view-applicants'>Go back to Applicants.</a> | \n";
	echo "<a href='".get_bloginfo('wpurl')."/casting-dashboard'>Go back to dashboard.</a></p>\n";
}

echo "			<input type=\"hidden\" name=\"castingcart\" value=\"1\"/>";
echo "  	</div>\n";
echo "  </div>\n";
?>
<script type="text/javascript">
	jQuery(document).ready(function(){
		var arr = [];
		var arr_casting = [];

		jQuery("input[name=selectallprofiles]").change(function(){
				var ischecked = jQuery(this).is(':checked');
				jQuery("#profile-casting-list #profile-list  input[name^=profileid]").each(function(){
					if(ischecked){
					jQuery(this).removeAttr("checked");
					jQuery(this).prop("checked",true);
					arr.push(jQuery(this).val());
					} else {
					jQuery(this).prop("checked",true);
					jQuery(this).removeAttr("checked");
					arr = [];
					}
				});
				jQuery("input[name=shortlistprofiles]").val(arr.toString());
		});
		jQuery("select[name=Job_ID]").change(function(){
				jQuery("input[name=job_id][type=hidden]").val(jQuery(this).val());
		});
		jQuery("#profile-casting-list #profile-list input[name^=profileid]").click(function(){
				Array.prototype.remove = function(value) {
				var idx = this.indexOf(value);
				if (idx != -1) {
					return this.splice(idx, 1); 
				}
				return false;
				}
				if(jQuery(this).is(':checked')){
					arr.push(jQuery(this).val());
				} else {
					arr.remove(jQuery(this).val());
				}
			jQuery("input[name=shortlistprofiles]").val(arr.toString());
		});
	});
</script>

<?php
echo $test = RBAgency_Common::rb_footer(); 
?>


<?php

function yoursite_wp_mail_from($original_email_address ) {
  global $current_user;
  get_currentuserinfo();
  return $current_user->user_email;
}

function yoursite_wp_mail_from_name($original_email_from ) {
  global $current_user;
  get_currentuserinfo();
  return $current_user->user_firstname." ".$current_user->user_lastname;
}

add_filter ("wp_mail_content_type", "my_awesome_mail_content_type");
function my_awesome_mail_content_type($content_type) {
	return "text/html";
}
?>