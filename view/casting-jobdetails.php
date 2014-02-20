<?php

	session_start();

	header("Cache-control: private"); //IE 6 Fix

	// Profile Class
	include(rb_agency_BASEREL ."app/profile.class.php");
	
	// include casting class
	include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

	global $wpdb;
	
	global $current_user, $wp_roles;
	
	get_currentuserinfo();
	
	$job_id = get_query_var('value');

	// add scripts
	wp_deregister_script('jquery'); 
	wp_register_script('jquery', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
	wp_enqueue_script('jquery');

	echo $rb_header = RBAgency_Common::rb_header();

	if (is_user_logged_in()) { 	
	
		echo "<style>
				.jobdesc{margin-left:20px; width:250px; padding:20px 0px 20px 50px;}
			 </style>
			 <script type='text/javascript'>
			 	jQuery(document).ready(function(){
					jQuery('#apply_job').click(function(){";
						if(RBAgency_Casting::rb_casting_ismodel($current_user->ID) == false){
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
						</tr>	
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
							<td><b>Job Criteria:</b></td>
							<td class='jobdesc'>".RBAgency_Casting::rb_get_job_criteria($r->Job_Criteria)."</td>
						</tr>	
						<tr>	
							<td></td>";
							if(RBAgency_Casting::rb_casting_ismodel($current_user->ID) == false){
								if(strpos($_SERVER['HTTP_REFERER'], "view-applicants") > -1){ 
									echo "<td class='jobdesc'><input id='apply_job' type='button' class='button-primary' value='Back to Applicants'></td>";
								} else {
									echo "<td class='jobdesc'><input id='apply_job' type='button' class='button-primary' value='Browse More Jobs'></td>";
								}
							} else {
								echo "<td class='jobdesc'><input id='apply_job' type='button' class='button-primary' value='Apply to this Job'></td>";
							}
						echo "</tr>																																				
					  <table>";
			}
		}
		
	} else {
		include ("include-login.php");
	}
	
	//get_sidebar(); 
	echo $rb_footer = RBAgency_Common::rb_footer(); 

?>