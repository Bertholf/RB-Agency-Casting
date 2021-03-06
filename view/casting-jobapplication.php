<?php

	// include casting class
	include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

	global $wpdb;
	global $current_user, $wp_roles;
	get_currentuserinfo();

	// job id
	$job_id = get_query_var('target');

	$Job = $wpdb->get_row($wpdb->prepare("SELECT job.*,client.* FROM ".table_agency_casting_job." as job INNER JOIN ".table_agency_casting." as client ON client.CastingID = job.Job_UserLinked WHERE job.Job_ID = %d",$job_id));
  
	// check if already applied
	$check_applied = "SELECT Job_UserLinked FROM " . table_agency_casting_job_application . " WHERE Job_ID = " . $job_id."  AND Job_UserLinked='".$current_user->ID."'"; 


	$get_checkapplied = $wpdb->get_results($check_applied,ARRAY_A);
	$count = $wpdb->num_rows;



	//check if invited already
	$qp = "SELECT ProfileID FROM ".$wpdb->prefix."agency_profile WHERE ProfileUserLinked = ".$current_user->ID;
	$qp_result = $wpdb->get_results($qp);
	$Profile_ID = "";
	foreach($qp_result as $res){
		$Profile_ID = $res->ProfileID;
	}
	$q = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."agency_casting_job WHERE Job_ID = ".$job_id." AND Job_Talents IN (".$Profile_ID.")");
	$invited_already = $wpdb->num_rows;

	if($invited_already > 0){
		echo $rb_header = RBAgency_Common::rb_header();

		echo __("<p>You are already invited to this job. Please check your email for the invite link to accept or decline.</p>",RBAGENCY_casting_TEXTDOMAIN); 
		echo "<p><a href='".get_bloginfo('wpurl')."/browse-jobs/'>".__("Apply to more jobs here.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>"; 
		echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/profile-member'>".__("Go Back to Profile Dashboard.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>\n";

		echo $rb_footer = RBAgency_Common::rb_footer();
		exit();
	}
	if($count <= 0){

		// message
		$remarks = "";

		//-----------------------------------------------
		// process submission here
		//-----------------------------------------------
		if(isset($_GET['apply_job'])){

			if(RBAgency_Casting::rb_get_job_visibility($job_id) == 2){

				//get data
				$job_criterias = RBAgency_Casting::rb_get_job_criteria_passed($current_user->ID, $_GET['Job_Criteria']);
				$Job_Criteria_Passed = count($job_criterias);
				$Job_Criteria_Details = serialize($job_criterias);
				$job_pitch = htmlspecialchars($_GET['job_pitch']);

				// get precentage
				if(preg_match("/\|/", $_GET['Job_Criteria'])){
					$count = count(explode("|", $_GET['Job_Criteria']));
				} else {
					$count = 1;
				}
				$res = ( $Job_Criteria_Passed / $count ) * 100;
				$percentage = round($res); 

			} elseif(RBAgency_Casting::rb_get_job_visibility($job_id) == 1){

				//get data
				$Job_Criteria_Passed = 10;
				$Job_Criteria_Details = '';
				$job_pitch = htmlspecialchars($_GET['job_pitch']);

				// get precentage
				$percentage = 100; 

			} elseif(RBAgency_Casting::rb_get_job_visibility($job_id) == 0){

				//get data
				$Job_Criteria_Passed = 0;
				$Job_Criteria_Details = '';
				$job_pitch = htmlspecialchars($_GET['job_pitch']);

				// get precentage
				$percentage = 0; 

			}

			$wpdb->get_results("SELECT * FROM " . table_agency_casting_job_application . "  WHERE Job_ID='".$job_id."' AND Job_UserLinked='".$current_user->ID."'");
			$has_applied = $wpdb->num_rows;

			echo $rb_header = RBAgency_Common::rb_header();

				if($has_applied <=0 ){
					// insert
					$insert = "INSERT INTO " . table_agency_casting_job_application . " 
								(Job_ID, Job_UserLinked, Job_Criteria_Passed,Job_Criteria_Details,Job_Criteria_Percentage, Job_Pitch) VALUES
								(".$job_id.",". $current_user->ID .",".$Job_Criteria_Passed.",'".$Job_Criteria_Details."',".$percentage.",'".$job_pitch."')";

					$id = $wpdb->query($insert);
					if($id > 0){
						$Message  = __("Hi ",RBAGENCY_casting_TEXTDOMAIN).$Job->CastingContactDisplay.",\n\n";
						$Message .=  __("You have a new applicant for the job ",RBAGENCY_casting_TEXTDOMAIN).$Job->Job_Title.".\n\n";
						$Message .= __("To review, please click the link below:",RBAGENCY_casting_TEXTDOMAIN)."\n\n";
						$Message .= get_bloginfo("wpurl")."/view-applicants/?filter_jobtitle=".$Job->Job_ID."\n\n\n";
						$Message .= get_bloginfo("name");

						RBAgency_Casting::sendClientNewJobNotification($Job->CastingContactEmail,$Job->Job_Title,$Message);
						echo "<p>".__("Successfully Submitted Your Application",RBAGENCY_casting_TEXTDOMAIN)."</p>"; 
						echo "<p><a href='".get_bloginfo('wpurl')."/browse-jobs/'>".__("Apply to more jobs here.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>"; 
						echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/profile-member'>".__("Go Back to Profile Dashboard.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>\n";
					}
				} else {
					echo __("You've already applied to this Job.",RBAGENCY_casting_TEXTDOMAIN);
				}

				echo $rb_footer = RBAgency_Common::rb_footer();

		} else {

			// add scripts
			wp_deregister_script('jquery'); 
			wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
			wp_enqueue_script('jquery_latest');

			echo $rb_header = RBAgency_Common::rb_header();

			echo "<div id=\"content\">";

			if (is_user_logged_in()) {

				echo "<style>
						table tr:last-child td { text-align: right }
					</style>";

				echo "<form method='get' action='".get_bloginfo('wpurl')."/job-application/".$job_id."/' >";

				//fetch data from database
				$data_r = $wpdb->get_results("SELECT * FROM ". table_agency_casting_job . " WHERE Job_ID = " . $job_id);
				if(count($data_r) > 0){
					foreach($data_r as $r){
						echo "<table>
								<tr>
									<td><h2>".__("Application for",RBAGENCY_casting_TEXTDOMAIN)." ".$r->Job_Title."</h2></td>
									<td class='jobdesc'><input type='hidden' name='Job_ID' value='".$job_id."'></td>
								</tr>
								<tr>
									<td>".__("Location:",RBAGENCY_casting_TEXTDOMAIN)." </td>
									<td class='jobdesc'> ".$r->Job_Location."</td>
								</tr>
								<tr>
									<td>".__("Type:",RBAGENCY_casting_TEXTDOMAIN)." </td>
									<td class='jobdesc'>".RBAgency_Casting::rb_get_job_type_name($r->Job_Type)."<br>".RBAgency_Casting::rb_get_job_criteria($r->Job_Criteria)."
									<input type='hidden' value='".$r->Job_Criteria."' name='Job_Criteria'></td>
								</tr>
								<tr>
									<td>".__("Make Your Pitch!:",RBAGENCY_casting_TEXTDOMAIN)." </td>
									<td class='jobdesc'><textarea name='job_pitch'></textarea></td>
								</tr>
								<tr>
									<td></td>
									<td class='jobdesc'><input type='submit' class='button-primary' name='apply_job' value='".__("Submit my Application",RBAGENCY_casting_TEXTDOMAIN)."'></td>
								</tr>																							
								<table>";
					}
				}

				echo "</form>";

			} else {

				include ("include-login.php");

			}
			echo "</div><!-- #content -->";

		}

		echo $rb_footer = RBAgency_Common::rb_footer(); 

	} else {

		echo $rb_header = RBAgency_Common::rb_header();

		echo "<p>".__("You already applied for this job.",RBAGENCY_casting_TEXTDOMAIN)."</p>"; 
		echo "<p><a href='".get_bloginfo('wpurl')."/browse-jobs/'>".__("Apply to more jobs here.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>"; 
		echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/profile-member'>".__("Go Back to Profile Dashboard.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>\n";

		echo $rb_footer = RBAgency_Common::rb_footer(); 

	}
?>
