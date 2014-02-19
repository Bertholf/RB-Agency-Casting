<?php

global $wpdb;
global $current_user;

// Profile Class
include(rb_agency_BASEREL ."app/profile.class.php");

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

echo $rb_header = RBAgency_Common::rb_header();

if (is_user_logged_in()) { 

	if(RBAgency_Casting::rb_casting_ismodel($current_user->ID) == false){
		
		echo "	<style>
					table td{border:1px solid #CCC;padding:12px;}
					table th{border:1px solid #CCC;padding:12px;}
				</style>";
		
		echo "<p><h3>Browse Applicants to your Job Postings</h3></p><br>";
		
		echo "<form method=\"post\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "\">\n";
		echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
		echo " <thead>\n";
		echo "    <tr class=\"thead\">\n";
		echo "        <th class=\"column-JobID\" id=\"JobID\" scope=\"col\" style=\"width:50px;\">ID</th>\n";
		echo "        <th class=\"column-JobTitle\" id=\"JobTitle\" scope=\"col\" style=\"width:150px;\">Job Title</th>\n";
		echo "        <th class=\"column-JobDate\" id=\"JobDate\" scope=\"col\">Applicant Name</th>\n";
		echo "        <th class=\"column-JobLocation\" id=\"ProfilesProfileDate\" scope=\"col\">Criteria Passed</th>\n";
		echo "        <th class=\"column-JobRegion\" id=\"ProfileLocationCity\" scope=\"col\">Action</th>\n";
		echo "    </tr>\n";
		echo " </thead>\n";
		
		// load all job postings
		$load_data = $wpdb->get_results("SELECT *, applicants.Job_UserLinked as app_id  FROM " . table_agency_casting_job_application . " applicants LEFT JOIN
										 " . table_agency_casting_job 
										 . " jobs ON jobs.Job_ID = applicants.Job_ID WHERE jobs.Job_UserLinked = " . $current_user->ID);
		
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
				echo "        <td class=\"column-JobDate\" scope=\"col\">".$display."</td>\n";
				echo "        <td class=\"column-JobLocation\" scope=\"col\">".$load->Job_Criteria_Passed."</td>\n";
				echo "        <td class=\"column-JobType\" scope=\"col\"><a href=''>View Details</a></td>\n";
				echo "    </tr>\n";
			}
		}
		
		echo "</table>";
	
	} else {

			echo "<p><h3>Only Casting Agents are permitted on this page.</h3></p><br>";	
	
	}

} else {
	include ("include-login.php");
}

//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 

?>