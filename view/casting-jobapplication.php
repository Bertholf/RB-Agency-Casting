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
	
	// job id
	$job_id = get_query_var('target');
	
	// message
	$remarks = "";
	
	//-----------------------------------------------
	// process submission here
	//-----------------------------------------------
	if(isset($_GET['apply_job'])){
		
		//get data
		$job_criterias = RBAgency_Casting::rb_get_job_criteria_passed($current_user->ID, $_GET['Job_Criteria']);
		$Job_Criteria_Passed = count($job_criterias);
		$Job_Criteria_Details = serialize($job_criterias);
		$job_pitch = htmlspecialchars($_GET['job_pitch']);
		
		// insert
		$insert = "INSERT INTO " . table_agency_casting_job_application . " 
				   (Job_ID,Job_Criteria_Passed,Job_Criteria_Details,job_Pitch) VALUES
				   (".$job_id.",".$Job_Criteria_Passed.",'".$Job_Criteria_Details."','".$job_pitch."')";
		
		$wpdb->query($insert) or die(mysql_error());		

		echo $rb_header = RBAgency_Common::rb_header();
		
		echo "<p>Successfully Submitted Your Application</p>";   
		echo "<p><a href='".get_bloginfo('wpurl')."/browse-jobs/'>Apply to more jobs here.</a></p>";   

		echo $rb_footer = RBAgency_Common::rb_footer(); 	
			
			
	} else {

		// add scripts
		wp_deregister_script('jquery'); 
		wp_register_script('jquery', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
		wp_enqueue_script('jquery');
	
		echo $rb_header = RBAgency_Common::rb_header();
	
		if (is_user_logged_in()) { 	
	
			echo "<style>
					.jobdesc{margin-left:20px; width:150px; padding:10px 0px 10px 30px;}
				 </style>";
			
			echo "<form method='get' action='".get_bloginfo('wpurl')."/job-application/".$job_id."/' >";
		
			//fetch data from database
			$data_r = $wpdb->get_results("SELECT * FROM ". table_agency_casting_job . " WHERE Job_ID = " . $job_id);
			if(count($data_r) > 0){
				foreach($data_r as $r){
					echo "<table>
							<tr>
								<td><h2>Application for ".$r->Job_Title."</h2></td>	
								<td class='jobdesc'><input type='hidden' name='Job_ID' value='".$job_id."'></td>
							</tr>	
							<tr>	
								<td>Location: </td>
								<td class='jobdesc'> ".$r->Job_Location."</td>
							</tr>
							<tr>	
								<td>Type: </td>
								<td class='jobdesc'>".RBAgency_Casting::rb_get_job_type_name($r->Job_Type)."<br>".RBAgency_Casting::rb_get_job_criteria($r->Job_Criteria)."
								<input type='hidden' value='".$r->Job_Criteria."' name='Job_Criteria'></td>
							</tr>
							<tr>	
								<td>Make Your Pitch!: </td>
								<td class='jobdesc'><textarea name='job_pitch'></textarea></td>
							</tr>						
							<tr>
								<td></td>	
								<td class='jobdesc'><input type='submit' class='button-primary' name='apply_job' value='Submit my Application'></td>
							</tr>																																				
						  <table>";
				}
			}
			
			echo "</form>";
			
		} else {
			
			include ("include-login.php");
	
		}

		echo $rb_footer = RBAgency_Common::rb_footer(); 	

	}
?>