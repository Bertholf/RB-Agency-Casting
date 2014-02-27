<?php

global $wpdb;
global $current_user;

// Profile Class
include(rb_agency_BASEREL ."app/profile.class.php");

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

echo $rb_header = RBAgency_Common::rb_header();

if (is_user_logged_in()) { 
	
	// casting agents and admin only
	if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) || current_user_can( 'manage_options' )){
		
		echo "	<style>
					table td{border:1px solid #CCC;padding:12px;}
					table th{border:1px solid #CCC;padding:12px;}
				</style>";
		if ( current_user_can( 'manage_options' ) ) {
			echo "<p><h3>All Applicants to All Job Postings from Casting Agents</h3></p><br>";
		} else {
			echo "<p><h3>Applicants to your Job Postings</h3></p><br>";
		}
		
		echo "<table style='margin-bottom:20px'>\n";
		echo "<tbody>";
		echo "    <tr class=\"thead\">\n";
		echo "        <td>Job Title<br>
						 <select name='filter_jobtitle'>
						 	<option value=''>-- Select Job Title --</option>
						 	<option value=''></option>
						 </select>		
					  </td>\n";
		echo "        <td>Applicant<br>
						<input type='text' name='filter_applicant' value=''>
					  </td>\n";
		echo "        <td>Criteria Matched<br>
						 <select name='filter_jobtitle'>
						 	<option value=''>-- Select Matched % --</option>
						 	<option value='75-100'>75% - 100% Matched</option>
						 	<option value='50-75'>50% - 75% Matched</option>
						 	<option value='25-50'>25% - 50% Matched</option>
						 	<option value='0-50'>0% - 25% Matched</option>
						 </select>		
					  </td>\n";
		echo "        <td><input type='button' class='button-primary' value='filter'></td>\n";
		echo "    </tr>\n";
		echo "</tbody>";
		echo "</table>";		
		
		echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
		echo " <thead>\n";
		echo "    <tr class=\"thead\">\n";
		echo "        <th class=\"column-JobID\" id=\"JobID\" scope=\"col\" style=\"width:50px;\">ID</th>\n";
		echo "        <th class=\"column-JobTitle\" id=\"JobTitle\" scope=\"col\" style=\"width:150px;\">Job Title</th>\n";
		echo "        <th class=\"column-JobDate\" id=\"JobDate\" scope=\"col\">Applicant</th>\n";
		echo "        <th class=\"column-JobLocation\" id=\"ProfilesProfileDate\" scope=\"col\">Criteria Passed</th>\n";
		echo "        <th class=\"column-JobRegion\" id=\"ProfileLocationCity\" scope=\"col\">Action</th>\n";
		echo "    </tr>\n";
		echo " </thead>\n";
		
		//pagination setup
		$start = get_query_var('target');
		$record_per_page = 2;
		$link = get_bloginfo('wpurl') . "/view-applicants/";
		$table_name = table_agency_casting_job_application;
		
		//for admin view
		if ( current_user_can( 'manage_options' ) ) {
			$where = " applicants LEFT JOIN " . table_agency_casting_job . 
					 " jobs ON jobs.Job_ID = applicants.Job_ID";
		} else {
			$where = " applicants LEFT JOIN " . table_agency_casting_job . 
					 " jobs ON jobs.Job_ID = applicants.Job_ID 
					   WHERE jobs.Job_UserLinked = " . $current_user->ID;
		}
		
		$selected_page = get_query_var('target');
		
		if($start != ""){
			$limit1 = ($start * $record_per_page) - $record_per_page;
		} else {
			$limit1 = 0;
		}
		
		// load all job postings
		//for admin view
		if ( current_user_can( 'manage_options' ) ) {
			$load_data = $wpdb->get_results("SELECT *, applicants.Job_UserLinked as app_id  FROM " . table_agency_casting_job_application . " applicants LEFT JOIN
											 " . table_agency_casting_job 
											 . " jobs ON jobs.Job_ID = applicants.Job_ID"
											 . " GROUP By applicants.Job_ID ORDER By applicants.Job_Criteria_Passed DESC 
											 LIMIT " . $limit1 . "," . $record_per_page );
		} else{
			$load_data = $wpdb->get_results("SELECT *, applicants.Job_UserLinked as app_id  FROM " . table_agency_casting_job_application . " applicants LEFT JOIN
											 " . table_agency_casting_job 
											 . " jobs ON jobs.Job_ID = applicants.Job_ID WHERE jobs.Job_UserLinked = " . $current_user->ID
											 . " GROUP By applicants.Job_ID ORDER By applicants.Job_Criteria_Passed DESC 
											 LIMIT " . $limit1 . "," . $record_per_page );
		}
		
		if(count($load_data) > 0){
			foreach($load_data as $load){
				$details = RBAgency_Casting::rb_casting_get_model_details($load->app_id);
				if($details->ProfileGallery != ""){
					$display = '<a href="'.get_bloginfo('wpurl').'/profile/'.$details->ProfileGallery.'">'.$details->ProfileContactNameFirst.'</a>';
				} else {
					$display = $details->ProfileContactNameFirst;
				}
				echo "    <tr>\n";
				echo "        <td class=\"column-JobID\" scope=\"col\" style=\"width:50px;\">".$load->Job_ID."</td>\n";
				echo "        <td class=\"column-JobTitle\" scope=\"col\" style=\"width:150px;\">".$load->Job_Title."</td>\n";
				echo "        <td class=\"column-JobDate\" scope=\"col\">";
				
				// applicant image
				$image = RBAgency_Casting::rb_get_model_image($load->app_id);
				if($image!= ""){			
					echo "<div style='float:left; display:block; width:120px; height:120px; text-align:center; line-height:120px; margin:5px; vertical-align:middle'>";
					echo "<span style = 'height: 120px; line-height:120px; width: 120px; display: table-cell; vertical-align: middle; text-align: center; soverflow: hidden;'>";
					echo "<img src='".$image."' style='max-width:120px; max-height:120px; vertical-align:middle'>";
					echo "</span>";
					echo "</div>";
				} else {
					echo "<div style='float:left; color:white; background:gray; width:120px; height:120px; text-align:center; line-height:120px; margin:5px; vertical-align:middle'>";
					echo "No Image";
					echo "</div>";
				}
				
				echo "<br><span style ='margin-left:5px; float:left; clear:both'>" . $display. "</span></td>\n";
				
				echo "        <td class=\"column-JobLocation\" scope=\"col\">".$load->Job_Criteria_Passed . RBAgency_Casting::rb_casting_get_percentage_passed($load->Job_ID, $load->Job_Criteria_Passed) . "</td>\n";
				echo "        <td class=\"column-JobType\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/job-detail/".$load->Job_ID."'>View Details</a></td>\n";
				echo "    </tr>\n";
			}
			echo "</table>";

		} else {
			
			echo "</table>";			
			echo "<p style=\"width:100%;\">You have no Applicants.<br>if you don't have any job postings, create a new job posting <a href='".get_bloginfo('wpurl')."/casting-postjob'>Here.</a></p>\n";
			
		}
		
		// actual pagination
		RBAgency_Casting::rb_casting_paginate($link, $table_name, $where, $record_per_page, $selected_page);
		
		echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/casting-dashboard'>Go Back to Casting Dashboard.</a></p>\n";		
		
	} else {

		echo "<p><h3>Only Casting Agents are permitted on this page.<br>You need to be registered <a href='".get_bloginfo('wpurl')."/casting-register'>here.</a></h3></p><br>";	
	
	}

} else {
	include ("include-login.php");
}

//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 

?>