<?php

	// include casting class
	include(dirname(dirname(__FILE__)) ."/app/casting.class.php");


	global $current_user, $wp_roles;

	get_currentuserinfo();

	$job_id = get_query_var('value');

	// add scripts
	wp_deregister_script('jquery'); 
	wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
	wp_enqueue_script('jquery_latest');

	echo $rb_header = RBAgency_Common::rb_header();

	echo "<div id=\"content\">";
   
	if (is_user_logged_in()) {

		echo "<style>
				.jobdesc{margin-left:20px; width:250px; padding:20px 0px 20px 50px;}
			</style>
			<script type='text/javascript'>
				jQuery(document).ready(function(){
					jQuery('#apply_job').click(function(){";
						if(RBAgency_Casting::rb_casting_ismodel($current_user->ID,'ProfileID')){
							if(strpos($_SERVER['HTTP_REFERER'], "view-applicants") > -1){
								echo "window.location = '".get_bloginfo('wpurl')."/view-applicants'; ";
							} elseif(strpos($_SERVER['HTTP_REFERER'], "browse-jobs") > -1){
								echo "window.location = '".get_bloginfo('wpurl')."/browse-jobs'; ";
							} else {
								echo "window.location = '".get_bloginfo('wpurl')."/browse-jobs'; ";
							}
						} else {
							echo "window.location = '".get_bloginfo('wpurl')."/job-application/".$job_id."'; ";
						}
					echo "});
				});
			</script>";

		echo "<p><h2>Job Details</h2><p><br>";

		global $wpdb;

		//fetch data from database
		$data_r = $wpdb->get_results("SELECT * FROM ". table_agency_casting_job . " WHERE Job_ID = " . $job_id);
		if(count($data_r) > 0){
			foreach($data_r as $r){
				echo "<table>
						<tr>
							<td><b>Title:</b></td>
							<td class='jobdesc'>".$r->Job_Title."</td>
						</tr>
						<tr>
							<td><b>Description:</b></td>
							<td class='jobdesc'>".$r->Job_Text."</td>
						</tr>
						<tr>
							<td><b>Duration:</b></td>
							<td class='jobdesc'>".date('F j, Y', strtotime($r->Job_Date_Start))." - ".date('F j, Y', strtotime($r->Job_Date_End))."</td>
						</tr>";
						
						if(!empty($r->Job_Time_Start)){
						echo "<tr>
								<td><b>Time Start:</b></td>
								<td class='jobdesc'>".$r->Job_Time_Start."</td>
							</tr>	";
						}
						if(!empty($r->Job_Time_End)){
						echo "<tr>
								<td><b>Time End:</b></td>
								<td class='jobdesc'>".$r->Job_Time_End."</td>
							</tr>	";
						}
						
					echo "
						<tr>
							<td><b>Location:</b></td>
							<td class='jobdesc'>".$r->Job_Location."</td>
						</tr>
						<tr>
							<td><b>Region:</b></td>
							<td class='jobdesc'>".$r->Job_Region."</td>
						</tr>
						<tr>
							<td><b>Job Type:</b></td>
							<td class='jobdesc'>".RBAgency_Casting::rb_get_job_type_name($r->Job_Type)."</td>
						</tr>
						<tr>
							<td><b>Job Criteria:</b></td>";

							if(RBAgency_Casting::rb_get_job_visibility($r->Job_ID) == 2){
								echo "<td class='jobdesc'>".RBAgency_Casting::rb_get_job_criteria($r->Job_Criteria)."</td>";
							} elseif(RBAgency_Casting::rb_get_job_visibility($r->Job_ID) == 1){
								echo "<td class='jobdesc'>Open to All</td>";
							} elseif(RBAgency_Casting::rb_get_job_visibility($r->Job_ID) == 0){
								echo "<td class='jobdesc'>Invite Only</td>";
							}


						echo "</tr>	";
						if(!empty($r->Job_Audition_Date)){
						echo "<tr>
								<td><b>Job Audition Date:</b></td>
								<td class='jobdesc'>".$r->Job_Audition_Date."</td>
							</tr>	";
						}
						if(!empty($r->Job_Audition_Time)){
						echo "<tr>
								<td><b>Job Audition Time Start:</b></td>
								<td class='jobdesc'>".$r->Job_Audition_Time."</td>
							</tr>	";
						}
						if(!empty($r->Job_Audition_Time)){
						echo "<tr>
								<td><b>Job Audition Time End:</b></td>
								<td class='jobdesc'>".$r->Job_Audition_Time_End."</td>
							</tr>	";
						}
						
						if(!empty($r->Job_Audition_Venue)){
						echo "<tr>
								<td><b>Job Audition Venue:</b></td>
								<td class='jobdesc'>".$r->Job_Audition_Venue."</td>
							</tr>	";
						}
						//Custom fields
						rb_agency_detail_castingjob();
						//End custom fields
						echo "
						<tr>
							<td></td>";
							
							
							if(RBAgency_Casting::rb_casting_ismodel($current_user->ID,'ProfileID') && !current_user_can( 'edit_posts' )){
								echo "<td class='jobdesc'>";
								echo "<input id='apply_job_btn' type='button' class='button-primary' value='Apply to this Job' onClick='window.location.href=\"".get_bloginfo("wpurl")."/job-application/".$r->Job_ID."\"'>";
								echo "<input id='browse_jobs' type='button' class='button-primary' onClick='window.location.href= \"".get_bloginfo('wpurl')."/browse-jobs\"' style='margin-left:12px;' value='Browse More Jobs'>";
								echo "</td>";
							}
							
							elseif(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID,'ProfileID') || current_user_can( 'edit_posts' )){
								if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "view-applicants") > -1){
									echo "<td class='jobdesc'><input id='apply_job' type='button' class='button-primary' value='Back to Applicants'></td>";
								} else {
									echo "<td class='jobdesc'><input id='apply_job' type='button' class='button-primary' value='Browse More Jobs'></td>";
								}
							} 
						

							if(current_user_can("edit_posts")){
								echo "<td class='jobdesc'>";
								echo "<input id=\"view_applicants\" type='button' class='button-primary'  onClick='window.location.href=\"".get_bloginfo('wpurl')."/view-applicants/?filter_jobtitle=".$r->Job_ID."&filter_applicant=&filter_jobpercentage=&filter_rating=&filter_perpage=10&filter=filter\"' value=\"View Applicants\"/>";
								echo "</td>";
							}
						echo "</tr>	";

					echo "<table>";
			}

			// for models
			if(RBAgency_Casting::rb_casting_ismodel($current_user->ID) && !current_user_can( 'edit_posts' )){
				echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/profile-member'>Go Back to Profile Dashboard.</a></p>\n";
			}

		} else {
			echo "<p>Job doesn't exist</p>";
		}

	} else {
		include ("include-login.php");
	}

	echo "</div><!-- #content -->";

	//get_sidebar(); 
	echo $rb_footer = RBAgency_Common::rb_footer(); 
?>
